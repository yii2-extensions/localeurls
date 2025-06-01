<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests;

use PHPUnit\Framework\Attributes\Group;
use Yii;
use yii\base\{Exception, InvalidConfigException};
use yii\web\NotFoundHttpException;
use yii2\extensions\localeurls\LanguageChangedEvent;

use function array_column;

/**
 * Test suite for {@see LanguageChangedEvent} event functionality and behavior.
 *
 * Verifies that language change events are triggered and handled within the LocaleUrls extension's URL language
 * management system.
 *
 * These tests ensure that the event system correctly detects language changes from various sources (cookies, sessions,
 * URLs) and fires the appropriate events with the correct data.
 *
 * The test scenarios validate that language persistence mechanisms work as expected and that events are only triggered
 * when actual language changes occur, preventing unnecessary event firing for identical language states.
 *
 * Test coverage.
 * - Cookie-based language change detection and event firing.
 * - Event data validation (new language, old language properties).
 * - Event handler execution and callback functionality.
 * - Language persistence behavior (cookies, sessions).
 * - Language persistence disabling functionality.
 * - No-change scenarios where events shouldn't fire.
 * - Session-based language change detection and event firing.
 * - URL-based language detection and initial language setting.
 *
 * @copyright Copyright (C) 2023 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
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

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testFiresIfNoLanguagePersisted(): void
    {
        Yii::getLogger()->flush(true);

        $this->mockUrlLanguageManager(
            [
                'languages' => ['fr', 'en', 'de'],
                'on languageChanged' => [$this, 'languageChangedHandler'],
            ],
        );

        $this->expectedLanguage = 'fr';

        $this->assertFalse(
            $this->eventFired,
            'Language changed event should not be fired before making the request when no language is persisted.',
        );

        $this->mockRequest('/fr/site/page');

        $this->assertTrue(
            $this->eventFired,
            'Language changed event should be fired when no language is persisted and a language is detected from URL.',
        );

        $loggerMessages = Yii::getLogger()->messages;
        $expectedMessages = array_column($loggerMessages, 0);

        $this->assertContains(
            'Triggering languageChanged event:  -> fr',
            $expectedMessages,
            'Language changed event should be logged with the new language.',
        );
        $this->assertContains(
            'Persisting language \'fr\' in session.',
            $expectedMessages,
            'Persisting language in session should be logged.',
        );
        $this->assertContains(
            'Persisting language \'fr\' in cookie.',
            $expectedMessages,
            'Persisting language in cookie should be logged.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testFiresNotIfNoCookieLanguageChange(): void
    {
        $_COOKIE['_language'] = 'fr';

        $this->mockUrlLanguageManager(
            [
                'languages' => ['fr', 'en', 'de'],
                'on languageChanged' => [$this, 'languageChangedHandler'],
            ],
        );

        $this->assertFalse(
            $this->eventFired,
            'Language changed event should not be fired before making the request when cookie language matches URL ' .
            'language.',
        );

        $this->mockRequest('/fr/site/page');

        $this->assertFalse(
            $this->eventFired,
            'Language changed event should not be fired when cookie language matches URL language.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
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

        $this->assertFalse(
            $this->eventFired,
            'Language changed event should not be fired before making the request when session language matches URL ' .
            'language.',
        );

        $this->mockRequest('/fr/site/page');

        $this->assertFalse(
            $this->eventFired,
            'Language changed event should not be fired when session language matches URL language.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
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

        $this->assertFalse(
            $this->eventFired,
            'Language changed event should not be fired before making the request when persistence is disabled.',
        );

        $this->mockRequest('/fr/site/page');

        $this->assertFalse(
            $this->eventFired,
            'Language changed event should not be fired when language persistence is disabled.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
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

        $this->assertFalse(
            $this->eventFired,
            'Language changed event should not be fired before making the request with different language.',
        );

        $this->mockRequest('/fr/site/page');

        $this->assertTrue(
            $this->eventFired,
            'Language changed event should be fired when URL language differs from cookie language.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
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

        $this->assertFalse(
            $this->eventFired,
            'Language changed event should not be fired before making the request with different language.',
        );

        $this->mockRequest('/fr/site/page');

        $this->assertTrue(
            $this->eventFired,
            'Language changed event should be fired when URL language differs from session language.',
        );
    }

    public function languageChangedHandler(LanguageChangedEvent $event): void
    {
        $this->assertTrue($this->eventExpected);
        $this->assertEquals($this->expectedLanguage, $event->language);
        $this->assertEquals($this->expectedOldLanguage, $event->oldLanguage);

        $this->eventFired = true;
    }
}
