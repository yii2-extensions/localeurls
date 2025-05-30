<?php

declare(strict_types=1);

namespace Yii2\Extensions\LocaleUrls\Test;

use PHPUnit\Framework\Attributes\Group;
use Yii2\Extensions\LocaleUrls\LanguageChangedEvent;

#[Group('locale-urls')]
final class EventTest extends TestCase
{
    protected bool $eventExpected = true;

    protected bool $eventFired = false;

    protected string|null $expectedLanguage = null;

    protected string|null $expectedOldLanguage = null;

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->eventExpected = true;
        $this->eventFired = false;
        $this->expectedLanguage = null;
        $this->expectedOldLanguage = null;
    }

    public function testFiresIfNoLanguagePersisted(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['fr', 'en', 'de'],
                'on languageChanged' => [$this, 'languageChangedHandler'],
            ],
        );

        $this->expectedLanguage = 'fr';

        $this->assertFalse($this->eventFired);
        $this->mockRequest('/fr/site/page');
        $this->assertTrue($this->eventFired);
    }

    public function testFiresOnCookieLanguageChange(): void
    {
        $_COOKIE['_language'] = 'de';

        $this->mockUrlLanguageManager(
            [
                'languages' => ['fr', 'en', 'de'],
                'on languageChanged' => [$this, 'languageChangedHandler'],
            ],
        );

        $this->expectedLanguage = 'fr';
        $this->expectedOldLanguage = 'de';

        $this->assertFalse($this->eventFired);
        $this->mockRequest('/fr/site/page');
        $this->assertTrue($this->eventFired);
    }

    public function testFiresOnSessionLanguageChange(): void
    {
        @session_start();
        $_SESSION['_language'] = 'de';

        $this->mockUrlLanguageManager(
            [
                'languages' => ['fr', 'en', 'de'],
                'on languageChanged' => [$this, 'languageChangedHandler'],
            ],
        );

        $this->expectedLanguage = 'fr';
        $this->expectedOldLanguage = 'de';

        $this->assertFalse($this->eventFired);
        $this->mockRequest('/fr/site/page');
        $this->assertTrue($this->eventFired);
    }

    public function testFiresNotIfNoCookieLanguageChange(): void
    {
        $_COOKIE['_language'] = 'fr';

        $this->mockUrlLanguageManager(
            [
                'languages' => ['fr', 'en', 'de'],
                'on languageChanged' => [$this, 'languageChangedHandler'],
            ],
        );

        $this->assertFalse($this->eventFired);
        $this->mockRequest('/fr/site/page');
        $this->assertFalse($this->eventFired);
    }

    public function testFiresNotIfNoSessionLanguageChange(): void
    {
        @session_start();
        $_SESSION['_language'] = 'fr';

        $this->mockUrlLanguageManager(
            [
                'languages' => ['fr', 'en', 'de'],
                'on languageChanged' => [$this, 'languageChangedHandler'],
            ],
        );

        $this->assertFalse($this->eventFired);
        $this->mockRequest('/fr/site/page');
        $this->assertFalse($this->eventFired);
    }

    public function testFiresNotIfPersistenceDisabled(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['fr', 'en', 'de'],
                'on languageChanged' => [$this, 'languageChangedHandler'],
                'enableLanguagePersistence' => false,
            ],
        );

        $this->expectedLanguage = 'fr';

        $this->assertFalse($this->eventFired);
        $this->mockRequest('/fr/site/page');
        $this->assertFalse($this->eventFired);
    }

    public function languageChangedHandler($event): void
    {
        $this->assertInstanceOf(LanguageChangedEvent::class, $event);
        $this->assertTrue($this->eventExpected);
        $this->assertEquals($this->expectedLanguage, $event->language);
        $this->assertEquals($this->expectedOldLanguage, $event->oldLanguage);

        $this->eventFired = true;
    }
}
