<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests;

use PHPUnit\Framework\Attributes\Group;
use yii\base\{Exception, InvalidConfigException};
use yii\web\NotFoundHttpException;
use yii2\extensions\localeurls\LanguageChangedEvent;
use yii2\extensions\localeurls\tests\support\TestCase;

/**
 * Unit tests for the {@see LanguageChangedEvent} raised when the persisted language changes.
 */
#[Group('locale-urls')]
final class EventTest extends TestCase
{
    /**
     * Whether the language change event has been fired.
     */
    private bool $eventFired = false;
    /**
     * Expected new language after the event is fired.
     */
    private string|null $expectedLanguage = null;
    /**
     * Expected old language before the event is fired.
     */
    private string|null $expectedOldLanguage = null;

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testFiresIfNoLanguagePersisted(): void
    {
        $this->expectedLanguage = 'fr';

        $this->requestFrenchPage();

        self::assertTrue($this->eventFired, 'Event must fire.');

        $messages = $this->loggedMessages();

        self::assertContains(
            'Triggering languageChanged event:  -> fr',
            $messages,
            'Event must be logged.',
        );
        self::assertContains(
            "Persisting language 'fr' in session.",
            $messages,
            'Session write must be logged.',
        );
        self::assertContains(
            "Persisting language 'fr' in cookie.",
            $messages,
            'Cookie write must be logged.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testFiresNotIfNoCookieLanguageChange(): void
    {
        $this->withCookies(['_language' => 'fr']);

        $this->requestFrenchPage();

        self::assertFalse(
            $this->eventFired,
            'Unchanged cookie language must not fire the event.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testFiresNotIfNoSessionLanguageChange(): void
    {
        $this->withSession(['_language' => 'fr']);

        $this->requestFrenchPage();

        self::assertFalse(
            $this->eventFired,
            'Unchanged session language must not fire the event.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testFiresNotIfPersistenceDisabled(): void
    {
        $this->expectedLanguage = 'fr';

        $this->requestFrenchPage(['enableLanguagePersistence' => false]);

        self::assertFalse(
            $this->eventFired,
            'Event must not fire without persistence.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testFiresOnCookieLanguageChange(): void
    {
        $this->withCookies(['_language' => 'de']);

        $this->expectedLanguage = 'fr';
        $this->expectedOldLanguage = 'de';

        $this->requestFrenchPage();

        self::assertTrue(
            $this->eventFired,
            'Event must fire.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testFiresOnSessionLanguageChange(): void
    {
        $this->withSession(['_language' => 'de']);

        $this->expectedLanguage = 'fr';
        $this->expectedOldLanguage = 'de';

        $this->requestFrenchPage();

        self::assertTrue(
            $this->eventFired,
            'Event must fire.',
        );
    }

    /**
     * Records the event and checks its languages against the expected ones.
     */
    private function languageChangedHandler(LanguageChangedEvent $event): void
    {
        self::assertSame(
            $this->expectedLanguage,
            $event->language,
            'New language must match.',
        );
        self::assertSame(
            $this->expectedOldLanguage,
            $event->oldLanguage,
            'Old language must match.',
        );

        $this->eventFired = true;
    }

    /**
     * Requests `/fr/site/page` with the `fr`, `en` and `de` languages and the event handler attached.
     *
     * @param array $config Additional URL manager configuration.
     *
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     *
     * @phpstan-param array<string, mixed> $config
     */
    private function requestFrenchPage(array $config = []): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['fr', 'en', 'de'],
                'on languageChanged' => $this->languageChangedHandler(...),
                ...$config,
            ],
        );
        $this->mockRequest('/fr/site/page');
    }
}
