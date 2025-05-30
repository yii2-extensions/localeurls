<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests;

use PHPUnit\Framework\Attributes\Group;

/**
 * Test suite for URL redirection functionality with slug-based routing and language detection.
 *
 * Verifies that URL redirection logic correctly handles slug-based routes with language detection from multiple sources
 * including HTTP headers, cookies, sessions, and GeoIP data, ensuring proper language code normalization and URL
 * formation.
 *
 * These tests validate redirection behavior in scenarios where URLs contain slug segments and require language
 * processing, including default language handling, case normalization, wildcard pattern matching, and parameter
 * preservation during redirects.
 *
 * This test class focuses specifically on slug redirection scenarios where language codes need to be detected,
 * normalized, and applied to URLs containing path segments that represent content slugs or identifiers.
 *
 * Test coverage.
 * - Accept-Language header processing with slug URLs and language priority handling.
 * - Cookie-based language detection with slug path preservation.
 * - Country alias resolution in slug-based URLs (for example, 'at' to 'de-AT').
 * - Default language handling with slug URLs (with and without language codes).
 * - GeoIP-based language detection with slug path redirection.
 * - Language code case normalization (uppercase to lowercase conversion).
 * - Language persistence and detection hierarchy with slug-based routing.
 * - Parameter and query string preservation during slug URL redirection.
 * - Session-based language detection with slug path handling.
 * - Suffix management in slug URLs (trailing slash handling).
 * - URL ignore pattern matching with slug-based routes.
 * - Wildcard language pattern matching with slug URLs (for example, 'es-*' patterns).
 *
 * @copyright Copyright (C) 2023 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
#[Group('locale-urls')]
class SlugRedirectTest extends TestCase
{
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
