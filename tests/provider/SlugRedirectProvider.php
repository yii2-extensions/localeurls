<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests\provider;

use Generator;

/**
 * Data provider for {@see \yii2\extensions\localeurls\tests\base\AbstractSlugRedirect} test cases.
 *
 * Provides representative URL manager configuration, request URL and detection fixtures paired with the expected
 * redirect for the slug route (`/foo/<term:.+>/bar`).
 *
 * @phpstan-import-type Fixtures from RequestFixtures
 */
final class SlugRedirectProvider
{
    use RequestFixtures;

    /**
     * Provides requests that redirect with the `302` status code.
     *
     * Each row holds the URL manager configuration, the request URL, the expected redirect URL, and the request
     * fixtures.
     *
     * @phpstan-return Generator<string, array{array<string, mixed>, string, string, Fixtures}>
     */
    public static function redirects(): Generator
    {
        yield 'if default language in URL and default language uses no suffix' => [
            ['languages' => ['en-US', 'en', 'de']],
            '/en/foo/baz/bar',
            '/foo/baz/bar',
            self::fixtures(),
        ];
        yield 'if default language in URL and default language uses no suffix and trailing slash enabled' => [
            ['languages' => ['en-US', 'en', 'de'], 'suffix' => '/'],
            '/en/foo/baz/bar/',
            '/foo/baz/bar/',
            self::fixtures(),
        ];
        yield 'if language with upper case country in URL' => [
            ['languages' => ['en-US', 'deutsch' => 'de', 'es-BO']],
            '/es-BO/foo/baz/bar',
            '/es-bo/foo/baz/bar',
            self::fixtures(),
        ];
        yield 'if language with upper case wildcard country in URL' => [
            ['languages' => ['en-US', 'deutsch' => 'de', 'es-*']],
            '/es-BO/foo/baz/bar',
            '/es-bo/foo/baz/bar',
            self::fixtures(),
        ];
        yield 'if no language in URL and accepted language matches' => [
            ['languages' => ['en-US', 'en', 'de']],
            '/foo/baz/bar',
            '/de/foo/baz/bar',
            self::fixtures(accept: ['de']),
        ];
        yield 'if no language in URL and accepted language matches language and country alias' => [
            ['languages' => ['de', 'at' => 'de-AT']],
            '/foo/baz/bar',
            '/de/foo/baz/bar',
            self::fixtures(accept: ['en-US', 'en', 'de']),
        ];
        yield 'if no language in URL and accepted language matches wildcard' => [
            ['languages' => ['en-US', 'en', 'de-*']],
            '/foo/baz/bar',
            '/de/foo/baz/bar',
            self::fixtures(accept: ['de']),
        ];
        yield 'if no language in URL and accepted language with country matches' => [
            ['languages' => ['en-US', 'en', 'de', 'de-AT']],
            '/foo/baz/bar',
            '/de-at/foo/baz/bar',
            self::fixtures(accept: ['de-AT', 'de', 'en']),
        ];
        yield 'if no language in URL and accepted language with country matches country alias' => [
            ['languages' => ['de', 'at' => 'de-AT']],
            '/foo/baz/bar',
            '/at/foo/baz/bar',
            self::fixtures(accept: ['de-at', 'de']),
        ];
        yield 'if no language in URL and accepted language with country matches language' => [
            ['languages' => ['en-US', 'en', 'de']],
            '/foo/baz/bar',
            '/de/foo/baz/bar',
            self::fixtures(accept: ['de-at']),
        ];
        yield 'if no language in URL and accepted language with country matches wildcard' => [
            ['languages' => ['en-US', 'en', 'de-*']],
            '/foo/baz/bar',
            '/de-at/foo/baz/bar',
            self::fixtures(accept: ['de-AT', 'de', 'en']),
        ];
        yield 'if no language in URL and accepted language with lowercase country matches' => [
            ['languages' => ['en-US', 'en', 'de', 'de-AT']],
            '/foo/baz/bar',
            '/de-at/foo/baz/bar',
            self::fixtures(accept: ['de-at', 'de', 'en']),
        ];
        yield 'if no language in URL and default language uses suffix' => [
            ['languages' => ['en-US', 'en', 'de'], 'enableDefaultLanguageUrlCode' => true],
            '/foo/baz/bar',
            '/en/foo/baz/bar',
            self::fixtures(),
        ];
        yield 'if no language in URL and GeoIP matches' => [
            ['languages' => ['en-US', 'en', 'de'], 'geoIpLanguageCountries' => ['de' => ['DEU'], 'en-US' => ['USA']]],
            '/foo/baz/bar',
            '/de/foo/baz/bar',
            self::fixtures(geo: 'DEU'),
        ];
        yield 'if no language in URL and GeoIP matches wildcard' => [
            ['languages' => ['en-US', 'en', 'de-*'], 'geoIpLanguageCountries' => ['de' => ['DEU'], 'en-US' => ['USA']]],
            '/foo/baz/bar',
            '/de/foo/baz/bar',
            self::fixtures(geo: 'DEU'),
        ];
        yield 'if no language in URL and GeoIP with country matches' => [
            [
                'languages' => ['en-US', 'en', 'de', 'de-AT'],
                'geoIpLanguageCountries' => ['de-DE' => ['DEU'], 'de-AT' => ['AUT']],
            ],
            '/foo/baz/bar',
            '/de-at/foo/baz/bar',
            self::fixtures(geo: 'AUT'),
        ];
        yield 'if no language in URL and GeoIP with country matches country alias' => [
            ['languages' => ['de', 'at' => 'de-AT'], 'geoIpLanguageCountries' => ['de' => ['DEU'], 'de-AT' => ['AUT']]],
            '/foo/baz/bar',
            '/at/foo/baz/bar',
            self::fixtures(geo: 'AUT'),
        ];
        yield 'if no language in URL and GeoIP with country matches wildcard' => [
            [
                'languages' => ['en-US', 'en', 'de-*'],
                'geoIpLanguageCountries' => ['de-DE' => ['DEU'], 'de-AT' => ['AUT']],
            ],
            '/foo/baz/bar',
            '/de-at/foo/baz/bar',
            self::fixtures(geo: 'AUT'),
        ];
        yield 'if no language in URL and language in cookie' => [
            ['languages' => ['en-US', 'en', 'de']],
            '/foo/baz/bar',
            '/de/foo/baz/bar',
            self::fixtures(cookie: 'de'),
        ];
        yield 'if no language in URL and language in cookie matches wildcard' => [
            ['languages' => ['en-US', 'en', 'de-*']],
            '/foo/baz/bar',
            '/de/foo/baz/bar',
            self::fixtures(cookie: 'de'),
        ];
        yield 'if no language in URL and language in session' => [
            ['languages' => ['en-US', 'en', 'de']],
            '/foo/baz/bar',
            '/de/foo/baz/bar',
            self::fixtures(session: 'de'),
        ];
        yield 'if no language in URL and language in session matches wildcard' => [
            ['languages' => ['en-US', 'en', 'de-*']],
            '/foo/baz/bar',
            '/de/foo/baz/bar',
            self::fixtures(session: 'de'),
        ];
        yield 'if URL does not match ignores URLs' => [
            ['languages' => ['en-US', 'en', 'de'], 'ignoreLanguageUrlPatterns' => ['#not/used#' => '#^site/other#']],
            '/en/foo/baz/bar',
            '/foo/baz/bar',
            self::fixtures(),
        ];
        yield 'root to default language if default language uses suffix and trailing slash enabled' => [
            ['languages' => ['en-US', 'en', 'de'], 'enableDefaultLanguageUrlCode' => true, 'suffix' => '/'],
            '/',
            '/en/',
            self::fixtures(),
        ];
        yield 'to lower case from accept language header' => [
            ['languages' => ['de-AT'], 'keepUppercaseLanguageCode' => false],
            '/foo/baz/bar',
            '/de-at/foo/baz/bar',
            self::fixtures(accept: ['DE-AT']),
        ];
        yield 'to lower case from upper case cookie' => [
            ['languages' => ['de-AT'], 'keepUppercaseLanguageCode' => false],
            '/foo/baz/bar',
            '/de-at/foo/baz/bar',
            self::fixtures(cookie: 'DE-AT'),
        ];
        yield 'to lower case from upper case session' => [
            ['languages' => ['de-AT'], 'keepUppercaseLanguageCode' => false],
            '/foo/baz/bar',
            '/de-at/foo/baz/bar',
            self::fixtures(session: 'DE-AT'),
        ];
        yield 'to root if only default language in URL, no default suffix, trailing slash enabled' => [
            ['languages' => ['en-US', 'en', 'de'], 'suffix' => '/'],
            '/en',
            '/',
            self::fixtures(),
        ];
    }
}
