<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests\base;

use PHPUnit\Framework\Attributes\Group;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;
use yii2\extensions\localeurls\tests\TestCase;

/**
 * Base class for slug redirect tests in the Yii2 LocaleUrls extension.
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
#[Group('locale-urls')]
abstract class AbstractSlugRedirect extends TestCase
{
    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
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
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
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
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
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
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
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
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
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
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
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
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
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
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
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
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
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
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
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
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
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
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
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
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
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
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
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
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
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
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
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
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
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
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
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
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
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
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
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
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
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
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
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
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
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
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
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
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
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

    protected function mockUrlLanguageManager(array $config = []): void
    {
        if (!isset($config['rules'])) {
            $config['rules'] = [];
        }

        $config['rules']['/foo/<term:.+>/bar'] = 'slug/action';

        parent::mockUrlLanguageManager($config);
    }
}
