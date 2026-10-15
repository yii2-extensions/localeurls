<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests\base;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use yii\base\{Exception, InvalidConfigException};
use yii\web\NotFoundHttpException;
use yii2\extensions\localeurls\tests\provider\{RequestFixtures, SlugRedirectProvider};
use yii2\extensions\localeurls\tests\support\TestCase;
use yii2\extensions\localeurls\UrlLanguageManager;

/**
 * Unit tests for {@see UrlLanguageManager} redirects of a slug route (`/foo/<term:.+>/bar`), replayed under the URL
 * layout of each concrete subclass.
 *
 * {@see SlugRedirectProvider} for test case data providers.
 *
 * @phpstan-import-type Fixtures from RequestFixtures
 */
abstract class AbstractSlugRedirect extends TestCase
{
    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testLogBrowserLanguageDetectionAndRedirectionMessages(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['de', 'at' => 'de-AT'],
            ],
        );
        $this->assertRedirect(
            '/de/foo/baz/bar',
            '/foo/baz/bar',
            [
                'acceptableLanguages' => ['en-US', 'en', 'de'],
            ],
        );

        $messages = $this->loggedMessages();

        self::assertContains(
            'Detected browser language \'de\'.',
            $messages,
            'Browser language detection must be logged.',
        );
        self::assertContains(
            'Redirecting to ' . $this->prepareUrl('/de/foo/baz/bar') . '.',
            $messages,
            'Redirect target must be logged.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testLogGeoIpLanguageDetectionWhenGeoIpCountryPresent(): void
    {
        $this->withServer(['HTTP_X_GEO_COUNTRY' => 'DEU']);

        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de'],
                'geoIpLanguageCountries' => [
                    'de' => ['DEU'],
                    'en-US' => ['USA'],
                ],
            ],
        );
        $this->assertRedirect('/de/foo/baz/bar', '/foo/baz/bar');

        self::assertContains(
            'Detected GeoIp language \'de\'.',
            $this->loggedMessages(),
            'GeoIP language detection must be logged.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     *
     * @phpstan-param array<string, mixed> $urlManager
     * @phpstan-param Fixtures $fixtures
     */
    #[DataProviderExternal(SlugRedirectProvider::class, 'redirects')]
    public function testRedirectToExpectedUrl(array $urlManager, string $from, string $to, array $fixtures): void
    {
        $this->applyFixtures($fixtures);
        $this->mockUrlLanguageManager($urlManager);
        $this->assertRedirect($to, $from, $fixtures['request']);
    }

    protected function mockUrlLanguageManager(array $config = []): void
    {
        $config['rules'] ??= ['/foo/<term:.+>/bar' => 'slug/action'];

        parent::mockUrlLanguageManager($config);
    }
}
