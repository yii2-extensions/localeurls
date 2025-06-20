<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests\base;

use Yii;
use yii\base\{Exception, InvalidConfigException};
use yii\web\NotFoundHttpException;
use yii2\extensions\localeurls\tests\TestCase;

use function array_column;
use function is_array;
use function session_start;

/**
 * Base class for slug redirect tests in the Yii LocaleUrls extension.
 *
 * Provides comprehensive tests for URL redirection involving slug-based routes, ensuring correct language detection,
 * normalization, and redirect behavior across multiple sources and configuration scenarios.
 *
 * This class validates the LocaleUrls redirection system by simulating requests with slug segments and different
 * language sources, such as cookies, sessions, headers, and GeoIP data.
 *
 * It covers normalization, language code handling, custom URL rules, suffix management, and parameter preservation
 * during redirects for URLs containing slugs or identifiers.
 *
 * Test coverage.
 * - Accept-Language header processing with slug URLs and language priority handling.
 * - Cookie-based language detection and redirection with slug path preservation.
 * - Country alias and wildcard pattern resolution in slug-based URLs (for example, 'at' to 'de-AT', 'es-*').
 * - Default language handling in slug URLs (with and without language codes).
 * - GeoIP-based language detection and automatic redirection with slug segments.
 * - Language code case conversion (uppercase to lowercase normalization).
 * - Language persistence configuration effects on slug redirection.
 * - Parameter and query string preservation during slug URL redirection.
 * - Session-based language detection and redirection with slug path handling.
 * - Suffix handling in slug URL normalization and redirection (trailing slash management).
 * - URL ignore pattern matching with slug-based routes.
 * - Wildcard language pattern matching and redirection for slug URLs.
 *
 * @copyright Copyright (C) 2023 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
abstract class AbstractSlugRedirect extends TestCase
{
    public function testLogBrowserLanguageDetectionAndRedirectionMessages(): void
    {
        Yii::getLogger()->flush(true);

        $this->mockUrlLanguageManager(
            [
                'languages' => ['de', 'at' => 'de-AT'],
            ],
        );

        try {
            $this->mockRequest(
                '/foo/baz/bar',
                [
                    'acceptableLanguages' => ['en-US', 'en', 'de'],
                ],
            );
        } catch (Exception) {
        }

        $loggerMessages = Yii::getLogger()->messages;
        $expectedMessages = array_column($loggerMessages, 0);

        self::assertContains(
            'Detected browser language \'de\'.',
            $expectedMessages,
            'Logger should record browser language detection message \'Detected browser language \'de\'.\' at index ' .
            '\'3\'.',
        );

        if ($this->baseUrl === '/base' && $this->showScriptName) {
            self::assertSame(
                'http://localhost/base/index.php/de/foo/baz/bar',
                Yii::$app->response->getHeaders()->get('Location'),
                'Response should redirect to \'http://localhost/base/index.php/de/foo/baz/bar\'.',
            );
            self::assertContains(
                'Redirecting to /base/index.php/de/foo/baz/bar.',
                $expectedMessages,
                'Logger should record redirection message \'Redirecting to /base/index.php/de/foo/baz/bar.\' at ' .
                'index \'6\'.',
            );
        }

        if ($this->baseUrl === '/base' && $this->showScriptName === false) {
            self::assertSame(
                'http://localhost/base/de/foo/baz/bar',
                Yii::$app->response->getHeaders()->get('Location'),
                'Response should redirect to \'http://localhost/base/de/foo/baz/bar\'.',
            );
            self::assertContains(
                'Redirecting to /base/de/foo/baz/bar.',
                $expectedMessages,
                'Logger should record redirection message \'Redirecting to /base/de/foo/baz/bar.\' at index \'6\'.',
            );
        }

        if ($this->baseUrl === '' && $this->showScriptName) {
            self::assertSame(
                'http://localhost/index.php/de/foo/baz/bar',
                Yii::$app->response->getHeaders()->get('Location'),
                'Response should redirect to \'http://localhost/index.php/de/foo/baz/bar\'.',
            );
            self::assertContains(
                'Redirecting to /index.php/de/foo/baz/bar.',
                $expectedMessages,
                'Logger should record redirection message \'Redirecting to /index.php/de/foo/baz/bar.\' at index ' .
                '\'6\'.',
            );
        }

        if ($this->baseUrl === '' && $this->showScriptName === false) {
            self::assertSame(
                'http://localhost/de/foo/baz/bar',
                Yii::$app->response->getHeaders()->get('Location'),
                'Response should redirect to \'http://localhost/de/foo/baz/bar\'.',
            );
            self::assertContains(
                'Redirecting to /de/foo/baz/bar.',
                $expectedMessages,
                'Logger should record redirection message \'Redirecting to /de/foo/baz/bar.\' at index \'6\'.',
            );
        }
    }

    public function testLogGeoIpLanguageDetectionWhenGeoIpCountryPresent(): void
    {
        Yii::getLogger()->flush(true);

        $_SERVER['HTTP_X_GEO_COUNTRY'] = 'DEU';

        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de'],
                'geoIpLanguageCountries' => [
                    'de' => ['DEU'],
                    'en-US' => ['USA'],
                ],
            ],
        );

        try {
            $this->mockRequest('/foo/baz/bar');
        } catch (Exception) {
        }

        $loggerMessages = Yii::getLogger()->messages;
        $expectedMessages = array_column($loggerMessages, 0);

        self::assertContains(
            'Detected GeoIp language \'de\'.',
            $expectedMessages,
            'Logger should record GeoIP language detection message \'Detected GeoIp language \'de\'.\' at index \'3\'.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsIfDefaultLanguageInUrlAndDefaultLanguageUsesNoSuffix(): void
    {
        $this->expectRedirect('/foo/baz/bar');
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de'],
            ],
        );
        $this->mockRequest('/en/foo/baz/bar');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsIfDefaultLanguageInUrlAndDefaultLanguageUsesNoSuffixAndTrailingSlashEnabled(): void
    {
        $this->expectRedirect('/foo/baz/bar/');
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de'],
                'suffix' => '/',
            ],
        );
        $this->mockRequest('/en/foo/baz/bar/');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsIfLanguageWithUpperCaseCountryInUrl(): void
    {
        $this->expectRedirect('/es-bo/foo/baz/bar');
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'deutsch' => 'de', 'es-BO'],
            ],
        );
        $this->mockRequest('/es-BO/foo/baz/bar');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsIfLanguageWithUpperCaseWildcardCountryInUrl(): void
    {
        $this->expectRedirect('/es-bo/foo/baz/bar');
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'deutsch' => 'de', 'es-*'],
            ],
        );
        $this->mockRequest('/es-BO/foo/baz/bar');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageMatches(): void
    {
        $this->expectRedirect('/de/foo/baz/bar');
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de'],
            ],
        );
        $this->mockRequest(
            '/foo/baz/bar',
            [
                'acceptableLanguages' => ['de'],
            ],
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageMatchesLanguageAndCountryAlias(): void
    {
        $this->expectRedirect('/de/foo/baz/bar');
        $this->mockUrlLanguageManager(
            [
                'languages' => ['de', 'at' => 'de-AT'],
            ],
        );
        $this->mockRequest(
            '/foo/baz/bar',
            [
                'acceptableLanguages' => ['en-US', 'en', 'de'],
            ],
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageMatchesWildcard(): void
    {
        $this->expectRedirect('/de/foo/baz/bar');
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de-*'],
            ],
        );
        $this->mockRequest(
            '/foo/baz/bar',
            [
                'acceptableLanguages' => ['de'],
            ],
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageWithCountryMatches(): void
    {
        $this->expectRedirect('/de-at/foo/baz/bar');
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de', 'de-AT'],
            ],
        );
        $this->mockRequest(
            '/foo/baz/bar',
            [
                'acceptableLanguages' => ['de-AT', 'de', 'en'],
            ],
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageWithCountryMatchesCountryAlias(): void
    {
        $this->expectRedirect('/at/foo/baz/bar');
        $this->mockUrlLanguageManager(
            [
                'languages' => ['de', 'at' => 'de-AT'],
            ],
        );
        $this->mockRequest(
            '/foo/baz/bar',
            [
                'acceptableLanguages' => ['de-at', 'de'],
            ],
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageWithCountryMatchesLanguage(): void
    {
        $this->expectRedirect('/de/foo/baz/bar');
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de'],
            ],
        );
        $this->mockRequest(
            '/foo/baz/bar',
            [
                'acceptableLanguages' => ['de-at'],
            ],
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageWithCountryMatchesWildcard(): void
    {
        $this->expectRedirect('/de-at/foo/baz/bar');
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de-*'],
            ],
        );
        $this->mockRequest(
            '/foo/baz/bar',
            [
                'acceptableLanguages' => ['de-AT', 'de', 'en'],
            ],
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageWithLowercaseCountryMatches(): void
    {
        $this->expectRedirect('/de-at/foo/baz/bar');
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de', 'de-AT'],
            ],
        );
        $this->mockRequest(
            '/foo/baz/bar',
            [
                'acceptableLanguages' => ['de-at', 'de', 'en'],
            ],
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsIfNoLanguageInUrlAndDefaultLanguageUsesSuffix(): void
    {
        $this->expectRedirect('/en/foo/baz/bar');

        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de'],
                'enableDefaultLanguageUrlCode' => true,
            ],
        );
        $this->mockRequest('/foo/baz/bar');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsIfNoLanguageInUrlAndGeoIpMatches(): void
    {
        $this->expectRedirect('/de/foo/baz/bar');

        $_SERVER['HTTP_X_GEO_COUNTRY'] = 'DEU';

        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de'],
                'geoIpLanguageCountries' => [
                    'de' => ['DEU'],
                    'en-US' => ['USA'],
                ],
            ],
        );
        $this->mockRequest('/foo/baz/bar');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsIfNoLanguageInUrlAndGeoIpMatchesWildcard(): void
    {
        $this->expectRedirect('/de/foo/baz/bar');

        $_SERVER['HTTP_X_GEO_COUNTRY'] = 'DEU';

        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de-*'],
                'geoIpLanguageCountries' => [
                    'de' => ['DEU'],
                    'en-US' => ['USA'],
                ],
            ],
        );
        $this->mockRequest('/foo/baz/bar');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsIfNoLanguageInUrlAndGeoIpWithCountryMatches(): void
    {
        $this->expectRedirect('/de-at/foo/baz/bar');

        $_SERVER['HTTP_X_GEO_COUNTRY'] = 'AUT';

        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de', 'de-AT'],
                'geoIpLanguageCountries' => [
                    'de-DE' => ['DEU'],
                    'de-AT' => ['AUT'],
                ],
            ],
        );
        $this->mockRequest('/foo/baz/bar');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsIfNoLanguageInUrlAndGeoIpWithCountryMatchesCountryAlias(): void
    {
        $this->expectRedirect('/at/foo/baz/bar');

        $_SERVER['HTTP_X_GEO_COUNTRY'] = 'AUT';

        $this->mockUrlLanguageManager(
            [
                'languages' => ['de', 'at' => 'de-AT'],
                'geoIpLanguageCountries' => [
                    'de' => ['DEU'],
                    'de-AT' => ['AUT'],
                ],
            ],
        );
        $this->mockRequest('/foo/baz/bar');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsIfNoLanguageInUrlAndGeoIpWithCountryMatchesWildcard(): void
    {
        $this->expectRedirect('/de-at/foo/baz/bar');

        $_SERVER['HTTP_X_GEO_COUNTRY'] = 'AUT';

        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de-*'],
                'geoIpLanguageCountries' => [
                    'de-DE' => ['DEU'],
                    'de-AT' => ['AUT'],
                ],
            ],
        );
        $this->mockRequest('/foo/baz/bar');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsIfNoLanguageInUrlAndLanguageInCookie(): void
    {
        $this->expectRedirect('/de/foo/baz/bar');

        $_COOKIE['_language'] = 'de';

        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de'],
            ],
        );
        $this->mockRequest('/foo/baz/bar');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsIfNoLanguageInUrlAndLanguageInCookieMatchesWildcard(): void
    {
        $this->expectRedirect('/de/foo/baz/bar');

        $_COOKIE['_language'] = 'de';

        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de-*'],
            ],
        );
        $this->mockRequest('/foo/baz/bar');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsIfNoLanguageInUrlAndLanguageInSession(): void
    {
        $this->expectRedirect('/de/foo/baz/bar');

        @session_start();

        $_SESSION['_language'] = 'de';

        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de'],
            ],
        );
        $this->mockRequest('/foo/baz/bar');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsIfNoLanguageInUrlAndLanguageInSessionMatchesWildcard(): void
    {
        $this->expectRedirect('/de/foo/baz/bar');

        @session_start();

        $_SESSION['_language'] = 'de';

        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de-*'],
            ],
        );
        $this->mockRequest('/foo/baz/bar');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsIfUrlDoesNotMatchIgnoresUrls(): void
    {
        $this->expectRedirect('/foo/baz/bar');
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de'],
                'ignoreLanguageUrlPatterns' => [
                    '#not/used#' => '#^site/other#',
                ],
            ],
        );
        $this->mockRequest('/en/foo/baz/bar');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsRootToDefaultLanguageIfDefaultLanguageUsesSuffixAndTrailingSlashEnabled(): void
    {
        $this->expectRedirect('/en/');
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de'],
                'enableDefaultLanguageUrlCode' => true,
                'suffix' => '/',
            ],
        );
        $this->mockRequest('/');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsToLowerCaseFromUpperCaseCookie(): void
    {
        $this->expectRedirect('/de-at/foo/baz/bar');

        $_COOKIE['_language'] = 'DE-AT';

        $this->mockUrlLanguageManager(
            [
                'languages' => ['de-AT'],
                'keepUppercaseLanguageCode' => false,
            ],
        );
        $this->mockRequest('/foo/baz/bar');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsToLowerCaseFromAcceptLanguageHeader(): void
    {
        $this->expectRedirect('/de-at/foo/baz/bar');
        $this->mockUrlLanguageManager(
            [
                'languages' => ['de-AT'],
                'keepUppercaseLanguageCode' => false,
            ],
        );
        $this->mockRequest(
            '/foo/baz/bar',
            [
                'acceptableLanguages' => ['DE-AT'],
            ],
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsToLowerCaseFromUpperCaseSession(): void
    {
        $this->expectRedirect('/de-at/foo/baz/bar');

        @session_start();

        $_SESSION['_language'] = 'DE-AT';

        $this->mockUrlLanguageManager(
            [
                'languages' => ['de-AT'],
                'keepUppercaseLanguageCode' => false,
            ],
        );
        $this->mockRequest('/foo/baz/bar');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsToRootIfOnlyDefaultLanguageInUrlAndDefaultLanguageUsesNoSuffixAndTrailingSlashEnabled(): void
    {
        $this->expectRedirect('/');
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de'],
                'suffix' => '/',
            ],
        );
        $this->mockRequest('/en');
    }

    protected function mockUrlLanguageManager(array|false|string $config = []): void
    {
        if (is_array($config)) {
            $config['rules'] ??= ['/foo/<term:.+>/bar' => 'slug/action'];
        }

        parent::mockUrlLanguageManager($config);
    }
}
