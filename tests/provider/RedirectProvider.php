<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests\provider;

use Generator;
use yii\web\UrlNormalizer;
use yii2\extensions\localeurls\tests\support\stub\UrlRule;

/**
 * Data provider for {@see \yii2\extensions\localeurls\tests\base\AbstractRedirect} test cases.
 *
 * @phpstan-import-type Fixtures from RequestFixtures
 */
final class RedirectProvider
{
    use RequestFixtures;

    /**
     * Languages of the configurations without a URL code for every language.
     */
    private const array BASE_LANGUAGES = ['en-US', 'en', 'de'];
    /**
     * GeoIP country codes per language.
     */
    private const array GEO_IP = ['de' => ['DEU'], 'de-AT' => ['AUT'], 'fr' => ['FRA'], 'en' => ['USA', 'GBR']];
    /**
     * Languages with aliases, a region and a wildcard.
     */
    private const array LANGUAGES = ['en-US', 'en', 'de', 'pt', 'at' => 'de-AT', 'alias' => 'fr', 'es-BO', 'wc-*'];
    /**
     * Rule with an empty suffix, for configurations with the `/` suffix.
     */
    private const array NO_SLASH_RULE = ['pattern' => '/noslash', 'route' => 'test/slash', 'suffix' => ''];
    /**
     * URL normalizer component configuration.
     */
    private const array NORMALIZER = ['class' => UrlNormalizer::class];
    /**
     * Custom and slug rules shared by every configuration with rules.
     */
    private const array ROUTE_RULES = ['/custom' => 'test/action', '/slug/<name>' => 'test/slug'];
    /**
     * Rule with the `/` suffix, for configurations without a suffix.
     */
    private const array SLASH_RULE = ['pattern' => '/slash', 'route' => 'test/slash', 'suffix' => '/'];

    /**
     * Provides requests that resolve without a redirect.
     *
     * Each row holds the URL manager configuration, the request URL, the expected application language, the expected
     * path info with the language code removed, the request fixtures, and the expected route and parameters.
     *
     * @phpstan-return Generator<
     *   string,
     *   array{array<string, mixed>, string, string, string, Fixtures, array{string, array<string, string>}},
     * >
     */
    public static function noRedirects(): Generator
    {
        $noCode = self::noCode();
        $defaultCode = self::defaultCode();
        $keepUppercase = self::keepUppercase();
        $ignorePatterns = self::ignorePatterns();
        $noPersistenceDetection = self::noPersistenceDetection();
        $suffix = self::suffix();
        $suffixDefaultCode = self::suffixDefaultCode();
        $normalizerSuffix = self::normalizerSuffix();
        $normalizer = self::normalizer();
        $normalizerSuffixDefaultCode = self::normalizerSuffixDefaultCode();
        $normalizerDefaultCode = self::normalizerDefaultCode();

        yield 'no-code: /site/page + geo USA' => [
            $noCode,
            '/site/page',
            'en',
            'site/page',
            self::fixtures(geo: 'USA'),
            ['site/page', []],
        ];
        yield 'no-code: /site/page + accept en' => [
            $noCode,
            '/site/page',
            'en',
            'site/page',
            self::fixtures(accept: ['en']),
            ['site/page', []],
        ];
        yield 'no-code: /site/page + session en' => [
            $noCode,
            '/site/page',
            'en',
            'site/page',
            self::fixtures(session: 'en'),
            ['site/page', []],
        ];
        yield 'no-code: /site/page + cookie en' => [
            $noCode,
            '/site/page',
            'en',
            'site/page',
            self::fixtures(cookie: 'en'),
            ['site/page', []],
        ];
        yield 'no-code: /de/site/page + session wc' => [
            $noCode,
            '/de/site/page',
            'de',
            'site/page',
            self::fixtures(session: 'wc'),
            ['site/page', []],
        ];
        yield 'no-code: /de/site/page + cookie wc' => [
            $noCode,
            '/de/site/page',
            'de',
            'site/page',
            self::fixtures(cookie: 'wc'),
            ['site/page', []],
        ];
        yield 'no-code: /de/site/page + accept en' => [
            $noCode,
            '/de/site/page',
            'de',
            'site/page',
            self::fixtures(accept: ['en']),
            ['site/page', []],
        ];
        yield 'no-code: /de/site/page + geo USA' => [
            $noCode,
            '/de/site/page',
            'de',
            'site/page',
            self::fixtures(geo: 'USA'),
            ['site/page', []],
        ];
        yield 'no-code: /custom' => [
            $noCode,
            '/custom',
            'en',
            'custom',
            self::fixtures(),
            ['test/action', []],
        ];
        yield 'no-code: /de/custom' => [
            $noCode,
            '/de/custom',
            'de',
            'custom',
            self::fixtures(),
            ['test/action', []],
        ];
        yield 'no-code: /slash/' => [
            $noCode,
            '/slash/',
            'en',
            'slash/',
            self::fixtures(),
            ['test/slash', []],
        ];
        yield 'no-code: /de/slash/' => [
            $noCode,
            '/de/slash/',
            'de',
            'slash/',
            self::fixtures(),
            ['test/slash', []],
        ];
        yield 'no-code: /slug/value' => [
            $noCode,
            '/slug/value',
            'en',
            'slug/value',
            self::fixtures(),
            ['test/slug', ['name' => 'value']],
        ];
        yield 'no-code: /ruleclass-test-url' => [
            $noCode,
            '/ruleclass-test-url',
            'en',
            'ruleclass-test-url',
            self::fixtures(),
            ['ruleclass-test-url', []],
        ];
        yield 'default-code: /en/site/page + session wc' => [
            $defaultCode,
            '/en/site/page',
            'en',
            'site/page',
            self::fixtures(session: 'wc'),
            ['site/page', []],
        ];
        yield 'default-code: /en/site/page + cookie wc' => [
            $defaultCode,
            '/en/site/page',
            'en',
            'site/page',
            self::fixtures(cookie: 'wc'),
            ['site/page', []],
        ];
        yield 'default-code: /en/site/page + accept en' => [
            $defaultCode,
            '/en/site/page',
            'en',
            'site/page',
            self::fixtures(accept: ['en']),
            ['site/page', []],
        ];
        yield 'default-code: /en/site/page + geo USA' => [
            $defaultCode,
            '/en/site/page',
            'en',
            'site/page',
            self::fixtures(geo: 'USA'),
            ['site/page', []],
        ];
        yield 'default-code: /de/site/page + session wc' => [
            $defaultCode,
            '/de/site/page',
            'de',
            'site/page',
            self::fixtures(session: 'wc'),
            ['site/page', []],
        ];
        yield 'default-code: /de/site/page + cookie wc' => [
            $defaultCode,
            '/de/site/page',
            'de',
            'site/page',
            self::fixtures(cookie: 'wc'),
            ['site/page', []],
        ];
        yield 'default-code: /de/site/page + accept en' => [
            $defaultCode,
            '/de/site/page',
            'de',
            'site/page',
            self::fixtures(accept: ['en']),
            ['site/page', []],
        ];
        yield 'default-code: /de/site/page + geo USA' => [
            $defaultCode,
            '/de/site/page',
            'de',
            'site/page',
            self::fixtures(geo: 'USA'),
            ['site/page', []],
        ];
        yield 'default-code: /en/custom' => [
            $defaultCode,
            '/en/custom',
            'en',
            'custom',
            self::fixtures(),
            ['test/action', []],
        ];
        yield 'default-code: /de/custom' => [
            $defaultCode,
            '/de/custom',
            'de',
            'custom',
            self::fixtures(),
            ['test/action', []],
        ];
        yield 'default-code: /en/slash/' => [
            $defaultCode,
            '/en/slash/',
            'en',
            'slash/',
            self::fixtures(),
            ['test/slash', []],
        ];
        yield 'default-code: /de/slash/' => [
            $defaultCode,
            '/de/slash/',
            'de',
            'slash/',
            self::fixtures(),
            ['test/slash', []],
        ];
        yield 'default-code: /en/slug/value' => [
            $defaultCode,
            '/en/slug/value',
            'en',
            'slug/value',
            self::fixtures(),
            ['test/slug', ['name' => 'value']],
        ];
        yield 'default-code: /en/ruleclass-english' => [
            $defaultCode,
            '/en/ruleclass-english',
            'en',
            'ruleclass-english',
            self::fixtures(),
            ['ruleclass/test', []],
        ];
        yield 'keep-uppercase: /es-BO/site/page' => [
            $keepUppercase,
            '/es-BO/site/page',
            'es-BO',
            'site/page',
            self::fixtures(),
            ['site/page', []],
        ];
        yield 'ignore-patterns: /site/other' => [
            $ignorePatterns,
            '/site/other',
            'en',
            'site/other',
            self::fixtures(),
            ['site/other', []],
        ];
        yield 'no-persistence-detection: /' => [
            $noPersistenceDetection,
            '/',
            'en',
            '',
            self::fixtures(),
            ['', []],
        ];
        yield 'no-persistence-detection: / + session de, cookie fr, accept pt, geo DEU' => [
            $noPersistenceDetection,
            '/',
            'en',
            '',
            self::fixtures(session: 'de', cookie: 'fr', accept: ['pt'], geo: 'DEU'),
            ['', []],
        ];
        yield 'no-persistence-detection: /site/page' => [
            $noPersistenceDetection,
            '/site/page',
            'en',
            'site/page',
            self::fixtures(),
            ['site/page', []],
        ];
        yield 'no-persistence-detection: /site/page + session de, cookie fr, accept pt, geo DEU' => [
            $noPersistenceDetection,
            '/site/page',
            'en',
            'site/page',
            self::fixtures(session: 'de', cookie: 'fr', accept: ['pt'], geo: 'DEU'),
            ['site/page', []],
        ];
        yield 'no-persistence-detection: /de' => [
            $noPersistenceDetection,
            '/de',
            'de',
            '',
            self::fixtures(),
            ['', []],
        ];
        yield 'no-persistence-detection: /de + session en, cookie fr, accept pt, geo FRA' => [
            $noPersistenceDetection,
            '/de',
            'de',
            '',
            self::fixtures(session: 'en', cookie: 'fr', accept: ['pt'], geo: 'FRA'),
            ['', []],
        ];
        yield 'no-persistence-detection: /de/site/page' => [
            $noPersistenceDetection,
            '/de/site/page',
            'de',
            'site/page',
            self::fixtures(),
            ['site/page', []],
        ];
        yield 'no-persistence-detection: /de/site/page + session en, cookie fr, accept pt, geo FRA' => [
            $noPersistenceDetection,
            '/de/site/page',
            'de',
            'site/page',
            self::fixtures(session: 'en', cookie: 'fr', accept: ['pt'], geo: 'FRA'),
            ['site/page', []],
        ];
        yield 'suffix: /site/page/' => [
            $suffix,
            '/site/page/',
            'en',
            'site/page/',
            self::fixtures(),
            ['site/page', []],
        ];
        yield 'suffix: /de/site/page/' => [
            $suffix,
            '/de/site/page/',
            'de',
            'site/page/',
            self::fixtures(),
            ['site/page', []],
        ];
        yield 'suffix: /custom/' => [
            $suffix,
            '/custom/',
            'en',
            'custom/',
            self::fixtures(),
            ['test/action', []],
        ];
        yield 'suffix: /de/custom/' => [
            $suffix,
            '/de/custom/',
            'de',
            'custom/',
            self::fixtures(),
            ['test/action', []],
        ];
        yield 'suffix: /noslash' => [
            $suffix,
            '/noslash',
            'en',
            'noslash',
            self::fixtures(),
            ['test/slash', []],
        ];
        yield 'suffix: /de/noslash' => [
            $suffix,
            '/de/noslash',
            'de',
            'noslash',
            self::fixtures(),
            ['test/slash', []],
        ];
        yield 'suffix: /slug/value/' => [
            $suffix,
            '/slug/value/',
            'en',
            'slug/value/',
            self::fixtures(),
            ['test/slug', ['name' => 'value']],
        ];
        yield 'suffix-default-code: /en/site/page/' => [
            $suffixDefaultCode,
            '/en/site/page/',
            'en',
            'site/page/',
            self::fixtures(),
            ['site/page', []],
        ];
        yield 'suffix-default-code: /en/site/page/ + session de' => [
            $suffixDefaultCode,
            '/en/site/page/',
            'en',
            'site/page/',
            self::fixtures(session: 'de'),
            ['site/page', []],
        ];
        yield 'suffix-default-code: /en/site/page/ + cookie fr' => [
            $suffixDefaultCode,
            '/en/site/page/',
            'en',
            'site/page/',
            self::fixtures(cookie: 'fr'),
            ['site/page', []],
        ];
        yield 'suffix-default-code: /en/site/page/ + geo FRA' => [
            $suffixDefaultCode,
            '/en/site/page/',
            'en',
            'site/page/',
            self::fixtures(geo: 'FRA'),
            ['site/page', []],
        ];
        yield 'suffix-default-code: /pt/site/page/' => [
            $suffixDefaultCode,
            '/pt/site/page/',
            'pt',
            'site/page/',
            self::fixtures(),
            ['site/page', []],
        ];
        yield 'suffix-default-code: /pt/site/page/ + session de' => [
            $suffixDefaultCode,
            '/pt/site/page/',
            'pt',
            'site/page/',
            self::fixtures(session: 'de'),
            ['site/page', []],
        ];
        yield 'suffix-default-code: /pt/site/page/ + cookie fr' => [
            $suffixDefaultCode,
            '/pt/site/page/',
            'pt',
            'site/page/',
            self::fixtures(cookie: 'fr'),
            ['site/page', []],
        ];
        yield 'suffix-default-code: /pt/site/page/ + geo FRA' => [
            $suffixDefaultCode,
            '/pt/site/page/',
            'pt',
            'site/page/',
            self::fixtures(geo: 'FRA'),
            ['site/page', []],
        ];
        yield 'suffix-default-code: /en/custom/' => [
            $suffixDefaultCode,
            '/en/custom/',
            'en',
            'custom/',
            self::fixtures(),
            ['test/action', []],
        ];
        yield 'suffix-default-code: /de/custom/' => [
            $suffixDefaultCode,
            '/de/custom/',
            'de',
            'custom/',
            self::fixtures(),
            ['test/action', []],
        ];
        yield 'suffix-default-code: /en/noslash' => [
            $suffixDefaultCode,
            '/en/noslash',
            'en',
            'noslash',
            self::fixtures(),
            ['test/slash', []],
        ];
        yield 'suffix-default-code: /de/noslash' => [
            $suffixDefaultCode,
            '/de/noslash',
            'de',
            'noslash',
            self::fixtures(),
            ['test/slash', []],
        ];
        yield 'suffix-default-code: /en/slug/value/' => [
            $suffixDefaultCode,
            '/en/slug/value/',
            'en',
            'slug/value/',
            self::fixtures(),
            ['test/slug', ['name' => 'value']],
        ];
        yield 'normalizer-suffix: \'\'' => [
            $normalizerSuffix,
            '',
            'en',
            '',
            self::fixtures(),
            ['', []],
        ];
        yield 'normalizer-suffix: /site/page/' => [
            $normalizerSuffix,
            '/site/page/',
            'en',
            'site/page/',
            self::fixtures(),
            ['site/page', []],
        ];
        yield 'normalizer-suffix: /de/' => [
            $normalizerSuffix,
            '/de/',
            'de',
            '',
            self::fixtures(),
            ['', []],
        ];
        yield 'normalizer-suffix: /de/site/login/' => [
            $normalizerSuffix,
            '/de/site/login/',
            'de',
            'site/login/',
            self::fixtures(),
            ['site/login', []],
        ];
        yield 'normalizer-suffix: /custom/' => [
            $normalizerSuffix,
            '/custom/',
            'en',
            'custom/',
            self::fixtures(),
            ['test/action', []],
        ];
        yield 'normalizer-suffix: /de/custom/' => [
            $normalizerSuffix,
            '/de/custom/',
            'de',
            'custom/',
            self::fixtures(),
            ['test/action', []],
        ];
        yield 'normalizer-suffix: /noslash' => [
            $normalizerSuffix,
            '/noslash',
            'en',
            'noslash',
            self::fixtures(),
            ['test/slash', []],
        ];
        yield 'normalizer-suffix: /de/noslash' => [
            $normalizerSuffix,
            '/de/noslash',
            'de',
            'noslash',
            self::fixtures(),
            ['test/slash', []],
        ];
        yield 'normalizer: \'\'' => [
            $normalizer,
            '',
            'en',
            '',
            self::fixtures(),
            ['', []],
        ];
        yield 'normalizer: /site/page' => [
            $normalizer,
            '/site/page',
            'en',
            'site/page',
            self::fixtures(),
            ['site/page', []],
        ];
        yield 'normalizer: /de/' => [
            $normalizer,
            '/de/',
            'de',
            '',
            self::fixtures(),
            ['', []],
        ];
        yield 'normalizer: /de' => [
            $normalizer,
            '/de',
            'de',
            '',
            self::fixtures(),
            ['', []],
        ];
        yield 'normalizer: /de/site/login' => [
            $normalizer,
            '/de/site/login',
            'de',
            'site/login',
            self::fixtures(),
            ['site/login', []],
        ];
        yield 'normalizer: /custom' => [
            $normalizer,
            '/custom',
            'en',
            'custom',
            self::fixtures(),
            ['test/action', []],
        ];
        yield 'normalizer: /de/custom' => [
            $normalizer,
            '/de/custom',
            'de',
            'custom',
            self::fixtures(),
            ['test/action', []],
        ];
        yield 'normalizer: /slash/' => [
            $normalizer,
            '/slash/',
            'en',
            'slash/',
            self::fixtures(),
            ['test/slash', []],
        ];
        yield 'normalizer: /de/slash/' => [
            $normalizer,
            '/de/slash/',
            'de',
            'slash/',
            self::fixtures(),
            ['test/slash', []],
        ];
        yield 'normalizer-suffix-default-code: /en/custom/' => [
            $normalizerSuffixDefaultCode,
            '/en/custom/',
            'en',
            'custom/',
            self::fixtures(),
            ['test/action', []],
        ];
        yield 'normalizer-suffix-default-code: /de/custom/' => [
            $normalizerSuffixDefaultCode,
            '/de/custom/',
            'de',
            'custom/',
            self::fixtures(),
            ['test/action', []],
        ];
        yield 'normalizer-suffix-default-code: /en/noslash' => [
            $normalizerSuffixDefaultCode,
            '/en/noslash',
            'en',
            'noslash',
            self::fixtures(),
            ['test/slash', []],
        ];
        yield 'normalizer-suffix-default-code: /de/noslash' => [
            $normalizerSuffixDefaultCode,
            '/de/noslash',
            'de',
            'noslash',
            self::fixtures(),
            ['test/slash', []],
        ];
        yield 'normalizer-default-code: /en/custom' => [
            $normalizerDefaultCode,
            '/en/custom',
            'en',
            'custom',
            self::fixtures(),
            ['test/action', []],
        ];
        yield 'normalizer-default-code: /de/custom' => [
            $normalizerDefaultCode,
            '/de/custom',
            'de',
            'custom',
            self::fixtures(),
            ['test/action', []],
        ];
        yield 'normalizer-default-code: /en/slash/' => [
            $normalizerDefaultCode,
            '/en/slash/',
            'en',
            'slash/',
            self::fixtures(),
            ['test/slash', []],
        ];
        yield 'normalizer-default-code: /de/slash/' => [
            $normalizerDefaultCode,
            '/de/slash/',
            'de',
            'slash/',
            self::fixtures(),
            ['test/slash', []],
        ];
        yield 'no-code: /en-us/site/page' => [
            $noCode,
            '/en-us/site/page',
            'en-US',
            'site/page',
            self::fixtures(),
            ['site/page', []],
        ];
        yield 'no-code: /at/site/page' => [
            $noCode,
            '/at/site/page',
            'de-AT',
            'site/page',
            self::fixtures(),
            ['site/page', []],
        ];
        yield 'no-code: /wc-bb/site/page' => [
            $noCode,
            '/wc-bb/site/page',
            'wc-BB',
            'site/page',
            self::fixtures(),
            ['site/page', []],
        ];
        yield 'keep-uppercase: /es-bo/site/page' => [
            $keepUppercase,
            '/es-bo/site/page',
            'es-BO',
            'site/page',
            self::fixtures(),
            ['site/page', []],
        ];
        yield 'keep-uppercase: /DEUTSCH/site/page' => [
            $keepUppercase,
            '/DEUTSCH/site/page',
            'de',
            'site/page',
            self::fixtures(),
            ['site/page', []],
        ];
        yield 'alias-and-code: /de/site/page' => [
            self::aliasAndCode(),
            '/de/site/page',
            'de',
            'site/page',
            self::fixtures(),
            ['site/page', []],
        ];
        yield 'alias-and-code: /deutsch/site/page' => [
            self::aliasAndCode(),
            '/deutsch/site/page',
            'de',
            'site/page',
            self::fixtures(),
            ['site/page', []],
        ];
        yield 'region-code: /de-at/site/page' => [
            self::regionCode(),
            '/de-at/site/page',
            'de-at',
            'site/page',
            self::fixtures(),
            ['site/page', []],
        ];
        yield 'uppercase-alias: /at/site/page' => [
            self::uppercaseAlias(),
            '/at/site/page',
            'de-AT',
            'site/page',
            self::fixtures(),
            ['site/page', []],
        ];
        yield 'uppercase-alias-and-code: /deutsch/site/page' => [
            self::uppercaseAliasAndCode(),
            '/deutsch/site/page',
            'de',
            'site/page',
            self::fixtures(),
            ['site/page', []],
        ];
        yield 'wildcard: /es-mx/site/page' => [
            self::wildcard(),
            '/es-mx/site/page',
            'es-MX',
            'site/page',
            self::fixtures(),
            ['site/page', []],
        ];
        yield 'wildcard: /site/page + cookie xx' => [
            self::wildcard(),
            '/site/page',
            'en',
            'site/page',
            self::fixtures(cookie: 'xx'),
            ['site/page', []],
        ];
        yield 'wildcard-first: /de-at/site/page' => [
            self::wildcardFirst(),
            '/de-at/site/page',
            'de-AT',
            'site/page',
            self::fixtures(),
            ['site/page', []],
        ];
        yield 'wildcard-keep-uppercase: /ES-MX/site/page' => [
            self::wildcardKeepUppercase(),
            '/ES-MX/site/page',
            'es-MX',
            'site/page',
            self::fixtures(),
            ['site/page', []],
        ];
    }

    /**
     * Provides requests that redirect.
     *
     * Each row holds the URL manager configuration, the request URL, the expected redirect URL, the expected status
     * code, and the request fixtures. URLs are relative to the base URL and script name of the layout under test.
     *
     * @phpstan-return Generator<string, array{array<string, mixed>, string, string, int, Fixtures}>
     */
    public static function redirects(): Generator
    {
        $noCode = self::noCode();
        $defaultCode = self::defaultCode();
        $keepUppercase = self::keepUppercase();
        $ignorePatterns = self::ignorePatterns();
        $noPersistenceDetection = self::noPersistenceDetection();
        $suffix = self::suffix();
        $suffixDefaultCode = self::suffixDefaultCode();
        $normalizerSuffix = self::normalizerSuffix();
        $normalizer = self::normalizer();
        $normalizerSuffixDefaultCode = self::normalizerSuffixDefaultCode();
        $normalizerDefaultCode = self::normalizerDefaultCode();

        yield 'no-code: /en/site/page' => [
            $noCode,
            '/en/site/page',
            '/site/page',
            302,
            self::fixtures(),
        ];
        yield 'no-code: /EN/site/page' => [
            $noCode,
            '/EN/site/page',
            '/site/page',
            302,
            self::fixtures(),
        ];
        yield 'default-code: /EN/site/page' => [
            $defaultCode,
            '/EN/site/page',
            '/en/site/page',
            302,
            self::fixtures(),
        ];
        yield 'no-code: /en' => [
            $noCode,
            '/en',
            '/',
            302,
            self::fixtures(),
        ];
        yield 'no-code: /site/page + geo DEU' => [
            $noCode,
            '/site/page',
            '/de/site/page',
            302,
            self::fixtures(geo: 'DEU'),
        ];
        yield 'no-code: /site/page + geo AUT' => [
            $noCode,
            '/site/page',
            '/at/site/page',
            302,
            self::fixtures(geo: 'AUT'),
        ];
        yield 'no-code: /site/page + geo FRA' => [
            $noCode,
            '/site/page',
            '/alias/site/page',
            302,
            self::fixtures(geo: 'FRA'),
        ];
        yield 'no-code: /site/page + accept de' => [
            $noCode,
            '/site/page',
            '/de/site/page',
            302,
            self::fixtures(accept: ['de']),
        ];
        yield 'no-code: /site/page + accept de-at,de' => [
            $noCode,
            '/site/page',
            '/at/site/page',
            302,
            self::fixtures(accept: ['de-at', 'de']),
        ];
        yield 'no-code: /site/page + accept wc' => [
            $noCode,
            '/site/page',
            '/wc/site/page',
            302,
            self::fixtures(accept: ['wc']),
        ];
        yield 'no-code: /site/page + accept es-BO,es,en' => [
            $noCode,
            '/site/page',
            '/es-bo/site/page',
            302,
            self::fixtures(accept: ['es-BO', 'es', 'en']),
        ];
        yield 'no-code: /site/page + accept es-bo,es,en' => [
            $noCode,
            '/site/page',
            '/es-bo/site/page',
            302,
            self::fixtures(accept: ['es-bo', 'es', 'en']),
        ];
        yield 'no-code: /site/page + accept wc-AT,de,en' => [
            $noCode,
            '/site/page',
            '/wc-at/site/page',
            302,
            self::fixtures(accept: ['wc-AT', 'de', 'en']),
        ];
        yield 'no-code: /site/page + accept pt-br' => [
            $noCode,
            '/site/page',
            '/pt/site/page',
            302,
            self::fixtures(accept: ['pt-br']),
        ];
        yield 'no-code: /site/page + accept fr' => [
            $noCode,
            '/site/page',
            '/alias/site/page',
            302,
            self::fixtures(accept: ['fr']),
        ];
        yield 'no-code: /site/page + session de' => [
            $noCode,
            '/site/page',
            '/de/site/page',
            302,
            self::fixtures(session: 'de'),
        ];
        yield 'no-code: /site/page + session de-AT' => [
            $noCode,
            '/site/page',
            '/at/site/page',
            302,
            self::fixtures(session: 'de-AT'),
        ];
        yield 'no-code: /site/page + session wc' => [
            $noCode,
            '/site/page',
            '/wc/site/page',
            302,
            self::fixtures(session: 'wc'),
        ];
        yield 'no-code: /site/page + session es-BO' => [
            $noCode,
            '/site/page',
            '/es-bo/site/page',
            302,
            self::fixtures(session: 'es-BO'),
        ];
        yield 'no-code: /site/page + session wc-AT' => [
            $noCode,
            '/site/page',
            '/wc-at/site/page',
            302,
            self::fixtures(session: 'wc-AT'),
        ];
        yield 'no-code: /site/page + session pt' => [
            $noCode,
            '/site/page',
            '/pt/site/page',
            302,
            self::fixtures(session: 'pt'),
        ];
        yield 'no-code: /site/page + session fr' => [
            $noCode,
            '/site/page',
            '/alias/site/page',
            302,
            self::fixtures(session: 'fr'),
        ];
        yield 'no-code: /site/page + cookie de' => [
            $noCode,
            '/site/page',
            '/de/site/page',
            302,
            self::fixtures(cookie: 'de'),
        ];
        yield 'no-code: /site/page + cookie de-AT' => [
            $noCode,
            '/site/page',
            '/at/site/page',
            302,
            self::fixtures(cookie: 'de-AT'),
        ];
        yield 'no-code: /site/page + cookie wc' => [
            $noCode,
            '/site/page',
            '/wc/site/page',
            302,
            self::fixtures(cookie: 'wc'),
        ];
        yield 'no-code: /site/page + cookie es-BO' => [
            $noCode,
            '/site/page',
            '/es-bo/site/page',
            302,
            self::fixtures(cookie: 'es-BO'),
        ];
        yield 'no-code: /site/page + cookie wc-AT' => [
            $noCode,
            '/site/page',
            '/wc-at/site/page',
            302,
            self::fixtures(cookie: 'wc-AT'),
        ];
        yield 'no-code: /site/page + cookie pt' => [
            $noCode,
            '/site/page',
            '/pt/site/page',
            302,
            self::fixtures(cookie: 'pt'),
        ];
        yield 'no-code: /site/page + cookie fr' => [
            $noCode,
            '/site/page',
            '/alias/site/page',
            302,
            self::fixtures(cookie: 'fr'),
        ];
        yield 'no-code: /site/page + session de, cookie fr, accept pt, geo USA' => [
            $noCode,
            '/site/page',
            '/de/site/page',
            302,
            self::fixtures(session: 'de', cookie: 'fr', accept: ['pt'], geo: 'USA'),
        ];
        yield 'no-code: /site/page + cookie fr, accept pt, geo USA' => [
            $noCode,
            '/site/page',
            '/alias/site/page',
            302,
            self::fixtures(cookie: 'fr', accept: ['pt'], geo: 'USA'),
        ];
        yield 'no-code: /site/page + accept pt, geo USA' => [
            $noCode,
            '/site/page',
            '/pt/site/page',
            302,
            self::fixtures(accept: ['pt'], geo: 'USA'),
        ];
        yield 'no-code: /es-BO/site/page' => [
            $noCode,
            '/es-BO/site/page',
            '/es-bo/site/page',
            302,
            self::fixtures(),
        ];
        yield 'no-code: /es-BO/site/page + session de' => [
            $noCode,
            '/es-BO/site/page',
            '/es-bo/site/page',
            302,
            self::fixtures(session: 'de'),
        ];
        yield 'no-code: /es-BO/site/page + cookie de' => [
            $noCode,
            '/es-BO/site/page',
            '/es-bo/site/page',
            302,
            self::fixtures(cookie: 'de'),
        ];
        yield 'no-code: /wc-BB/site/page' => [
            $noCode,
            '/wc-BB/site/page',
            '/wc-bb/site/page',
            302,
            self::fixtures(),
        ];
        yield 'no-code: /wc-BB/site/page + session de' => [
            $noCode,
            '/wc-BB/site/page',
            '/wc-bb/site/page',
            302,
            self::fixtures(session: 'de'),
        ];
        yield 'no-code: /wc-BB/site/page + cookie de' => [
            $noCode,
            '/wc-BB/site/page',
            '/wc-bb/site/page',
            302,
            self::fixtures(cookie: 'de'),
        ];
        yield 'no-code: /en/custom' => [
            $noCode,
            '/en/custom',
            '/custom',
            302,
            self::fixtures(),
        ];
        yield 'no-code: /en/slash/' => [
            $noCode,
            '/en/slash/',
            '/slash/',
            302,
            self::fixtures(),
        ];
        yield 'no-code: /en?a=b' => [
            $noCode,
            '/en?a=b',
            '/?a=b',
            302,
            self::fixtures(),
        ];
        yield 'no-code: /en/site/page?a=b' => [
            $noCode,
            '/en/site/page?a=b',
            '/site/page?a=b',
            302,
            self::fixtures(),
        ];
        yield 'no-code: /en/custom?a=b' => [
            $noCode,
            '/en/custom?a=b',
            '/custom?a=b',
            302,
            self::fixtures(),
        ];
        yield 'no-code: /en/slash/?a=b' => [
            $noCode,
            '/en/slash/?a=b',
            '/slash/?a=b',
            302,
            self::fixtures(),
        ];
        yield 'no-code: /site/page?a=b + accept de' => [
            $noCode,
            '/site/page?a=b',
            '/de/site/page?a=b',
            302,
            self::fixtures(accept: ['de']),
        ];
        yield 'no-code: /en/slug/value' => [
            $noCode,
            '/en/slug/value',
            '/slug/value',
            302,
            self::fixtures(),
        ];
        yield 'no-code: /en/ruleclass-test-url' => [
            $noCode,
            '/en/ruleclass-test-url',
            '/ruleclass-test-url',
            302,
            self::fixtures(),
        ];
        yield 'default-code: /' => [
            $defaultCode,
            '/',
            '/en',
            302,
            self::fixtures(),
        ];
        yield 'default-code: / + session de' => [
            $defaultCode,
            '/',
            '/de',
            302,
            self::fixtures(session: 'de'),
        ];
        yield 'default-code: / + cookie fr' => [
            $defaultCode,
            '/',
            '/alias',
            302,
            self::fixtures(cookie: 'fr'),
        ];
        yield 'default-code: / + geo DEU' => [
            $defaultCode,
            '/',
            '/de',
            302,
            self::fixtures(geo: 'DEU'),
        ];
        yield 'default-code: /site/page' => [
            $defaultCode,
            '/site/page',
            '/en/site/page',
            302,
            self::fixtures(),
        ];
        yield 'default-code: /site/page + session de' => [
            $defaultCode,
            '/site/page',
            '/de/site/page',
            302,
            self::fixtures(session: 'de'),
        ];
        yield 'default-code: /site/page + cookie fr' => [
            $defaultCode,
            '/site/page',
            '/alias/site/page',
            302,
            self::fixtures(cookie: 'fr'),
        ];
        yield 'default-code: /site/page + geo DEU' => [
            $defaultCode,
            '/site/page',
            '/de/site/page',
            302,
            self::fixtures(geo: 'DEU'),
        ];
        yield 'default-code: /custom' => [
            $defaultCode,
            '/custom',
            '/en/custom',
            302,
            self::fixtures(),
        ];
        yield 'default-code: /slash/' => [
            $defaultCode,
            '/slash/',
            '/en/slash/',
            302,
            self::fixtures(),
        ];
        yield 'default-code: /?a=b' => [
            $defaultCode,
            '/?a=b',
            '/en?a=b',
            302,
            self::fixtures(),
        ];
        yield 'default-code: /site/page1?a=b' => [
            $defaultCode,
            '/site/page1?a=b',
            '/en/site/page1?a=b',
            302,
            self::fixtures(),
        ];
        yield 'default-code: /custom?a=b' => [
            $defaultCode,
            '/custom?a=b',
            '/en/custom?a=b',
            302,
            self::fixtures(),
        ];
        yield 'default-code: /slash/?a=b' => [
            $defaultCode,
            '/slash/?a=b',
            '/en/slash/?a=b',
            302,
            self::fixtures(),
        ];
        yield 'default-code: /site/page2?a=b + accept de' => [
            $defaultCode,
            '/site/page2?a=b',
            '/de/site/page2?a=b',
            302,
            self::fixtures(accept: ['de']),
        ];
        yield 'default-code: /slug/value' => [
            $defaultCode,
            '/slug/value',
            '/en/slug/value',
            302,
            self::fixtures(),
        ];
        yield 'default-code: /ruleclass-english' => [
            $defaultCode,
            '/ruleclass-english',
            '/en/ruleclass-english',
            302,
            self::fixtures(),
        ];
        yield 'default-code: /en/ruleclass-deutsch' => [
            $defaultCode,
            '/en/ruleclass-deutsch',
            '/en/ruleclass-english',
            302,
            self::fixtures(),
        ];
        yield 'keep-uppercase: /site/page + session en-US' => [
            $keepUppercase,
            '/site/page',
            '/en-US/site/page',
            302,
            self::fixtures(session: 'en-US'),
        ];
        yield 'keep-uppercase: /site/page + cookie en-US' => [
            $keepUppercase,
            '/site/page',
            '/en-US/site/page',
            302,
            self::fixtures(cookie: 'en-US'),
        ];
        yield 'ignore-patterns: /site/page' => [
            $ignorePatterns,
            '/site/page',
            '/en/site/page',
            302,
            self::fixtures(),
        ];
        yield 'no-persistence-detection: /en' => [
            $noPersistenceDetection,
            '/en',
            '/',
            302,
            self::fixtures(),
        ];
        yield 'no-persistence-detection: /en/site/page' => [
            $noPersistenceDetection,
            '/en/site/page',
            '/site/page',
            302,
            self::fixtures(),
        ];
        yield 'suffix: /en' => [
            $suffix,
            '/en',
            '/',
            302,
            self::fixtures(),
        ];
        yield 'suffix: /en/site/page/' => [
            $suffix,
            '/en/site/page/',
            '/site/page/',
            302,
            self::fixtures(),
        ];
        yield 'suffix: /en/custom/' => [
            $suffix,
            '/en/custom/',
            '/custom/',
            302,
            self::fixtures(),
        ];
        yield 'suffix: /en/noslash' => [
            $suffix,
            '/en/noslash',
            '/noslash',
            302,
            self::fixtures(),
        ];
        yield 'suffix: /en?a=b' => [
            $suffix,
            '/en?a=b',
            '/?a=b',
            302,
            self::fixtures(),
        ];
        yield 'suffix: /en/site/page/?a=b' => [
            $suffix,
            '/en/site/page/?a=b',
            '/site/page/?a=b',
            302,
            self::fixtures(),
        ];
        yield 'suffix: /en/custom/?a=b' => [
            $suffix,
            '/en/custom/?a=b',
            '/custom/?a=b',
            302,
            self::fixtures(),
        ];
        yield 'suffix: /en/noslash?a=b' => [
            $suffix,
            '/en/noslash?a=b',
            '/noslash?a=b',
            302,
            self::fixtures(),
        ];
        yield 'suffix: /site/page/?a=b + accept de' => [
            $suffix,
            '/site/page/?a=b',
            '/de/site/page/?a=b',
            302,
            self::fixtures(accept: ['de']),
        ];
        yield 'suffix: /en/slug/value/' => [
            $suffix,
            '/en/slug/value/',
            '/slug/value/',
            302,
            self::fixtures(),
        ];
        yield 'suffix-default-code: /' => [
            $suffixDefaultCode,
            '/',
            '/en/',
            302,
            self::fixtures(),
        ];
        yield 'suffix-default-code: / + session de' => [
            $suffixDefaultCode,
            '/',
            '/de/',
            302,
            self::fixtures(session: 'de'),
        ];
        yield 'suffix-default-code: / + cookie fr' => [
            $suffixDefaultCode,
            '/',
            '/alias/',
            302,
            self::fixtures(cookie: 'fr'),
        ];
        yield 'suffix-default-code: / + geo DEU' => [
            $suffixDefaultCode,
            '/',
            '/de/',
            302,
            self::fixtures(geo: 'DEU'),
        ];
        yield 'suffix-default-code: /site/page/' => [
            $suffixDefaultCode,
            '/site/page/',
            '/en/site/page/',
            302,
            self::fixtures(),
        ];
        yield 'suffix-default-code: /site/page/ + session de' => [
            $suffixDefaultCode,
            '/site/page/',
            '/de/site/page/',
            302,
            self::fixtures(session: 'de'),
        ];
        yield 'suffix-default-code: /site/page/ + cookie fr' => [
            $suffixDefaultCode,
            '/site/page/',
            '/alias/site/page/',
            302,
            self::fixtures(cookie: 'fr'),
        ];
        yield 'suffix-default-code: /site/page/ + geo DEU' => [
            $suffixDefaultCode,
            '/site/page/',
            '/de/site/page/',
            302,
            self::fixtures(geo: 'DEU'),
        ];
        yield 'suffix-default-code: /custom/' => [
            $suffixDefaultCode,
            '/custom/',
            '/en/custom/',
            302,
            self::fixtures(),
        ];
        yield 'suffix-default-code: /noslash' => [
            $suffixDefaultCode,
            '/noslash',
            '/en/noslash',
            302,
            self::fixtures(),
        ];
        yield 'suffix-default-code: /?a=b' => [
            $suffixDefaultCode,
            '/?a=b',
            '/en/?a=b',
            302,
            self::fixtures(),
        ];
        yield 'suffix-default-code: /site/page1/?a=b' => [
            $suffixDefaultCode,
            '/site/page1/?a=b',
            '/en/site/page1/?a=b',
            302,
            self::fixtures(),
        ];
        yield 'suffix-default-code: /custom/?a=b' => [
            $suffixDefaultCode,
            '/custom/?a=b',
            '/en/custom/?a=b',
            302,
            self::fixtures(),
        ];
        yield 'suffix-default-code: /noslash?a=b' => [
            $suffixDefaultCode,
            '/noslash?a=b',
            '/en/noslash?a=b',
            302,
            self::fixtures(),
        ];
        yield 'suffix-default-code: /site/page2/?a=b + accept de' => [
            $suffixDefaultCode,
            '/site/page2/?a=b',
            '/de/site/page2/?a=b',
            302,
            self::fixtures(accept: ['de']),
        ];
        yield 'suffix-default-code: /slug/value/' => [
            $suffixDefaultCode,
            '/slug/value/',
            '/en/slug/value/',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /site/page' => [
            $normalizerSuffix,
            '/site/page',
            '/site/page/',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /de' => [
            $normalizerSuffix,
            '/de',
            '/de/',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /de/site/login' => [
            $normalizerSuffix,
            '/de/site/login',
            '/de/site/login/',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /en/site/login' => [
            $normalizerSuffix,
            '/en/site/login',
            '/site/login/',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /en/site/login/' => [
            $normalizerSuffix,
            '/en/site/login/',
            '/site/login/',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /custom' => [
            $normalizerSuffix,
            '/custom',
            '/custom/',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /en/custom' => [
            $normalizerSuffix,
            '/en/custom',
            '/custom/',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /en/custom/' => [
            $normalizerSuffix,
            '/en/custom/',
            '/custom/',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /de/custom' => [
            $normalizerSuffix,
            '/de/custom',
            '/de/custom/',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /noslash/' => [
            $normalizerSuffix,
            '/noslash/',
            '/noslash',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /en/noslash' => [
            $normalizerSuffix,
            '/en/noslash',
            '/noslash',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /en/noslash/' => [
            $normalizerSuffix,
            '/en/noslash/',
            '/noslash',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /de/noslash/' => [
            $normalizerSuffix,
            '/de/noslash/',
            '/de/noslash',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /site/page?a=b' => [
            $normalizerSuffix,
            '/site/page?a=b',
            '/site/page/?a=b',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /de?a=b' => [
            $normalizerSuffix,
            '/de?a=b',
            '/de/?a=b',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /de/site/login?a=b' => [
            $normalizerSuffix,
            '/de/site/login?a=b',
            '/de/site/login/?a=b',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /en/site/login?a=b' => [
            $normalizerSuffix,
            '/en/site/login?a=b',
            '/site/login/?a=b',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /en/site/login/?a=b' => [
            $normalizerSuffix,
            '/en/site/login/?a=b',
            '/site/login/?a=b',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /custom?a=b' => [
            $normalizerSuffix,
            '/custom?a=b',
            '/custom/?a=b',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /en/custom?a=b' => [
            $normalizerSuffix,
            '/en/custom?a=b',
            '/custom/?a=b',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /en/custom/?a=b' => [
            $normalizerSuffix,
            '/en/custom/?a=b',
            '/custom/?a=b',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /de/custom?a=b' => [
            $normalizerSuffix,
            '/de/custom?a=b',
            '/de/custom/?a=b',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /noslash/?a=b' => [
            $normalizerSuffix,
            '/noslash/?a=b',
            '/noslash?a=b',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /en/noslash?a=b' => [
            $normalizerSuffix,
            '/en/noslash?a=b',
            '/noslash?a=b',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /en/noslash/?a=b' => [
            $normalizerSuffix,
            '/en/noslash/?a=b',
            '/noslash?a=b',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /de/noslash/?a=b' => [
            $normalizerSuffix,
            '/de/noslash/?a=b',
            '/de/noslash?a=b',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /slug/value' => [
            $normalizerSuffix,
            '/slug/value',
            '/slug/value/',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /en/slug/value' => [
            $normalizerSuffix,
            '/en/slug/value',
            '/slug/value/',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-suffix: /de/slug/value' => [
            $normalizerSuffix,
            '/de/slug/value',
            '/de/slug/value/',
            301,
            self::fixtures(),
        ];
        yield 'normalizer: /site/page/' => [
            $normalizer,
            '/site/page/',
            '/site/page',
            301,
            self::fixtures(),
        ];
        yield 'normalizer: /de/site/login/' => [
            $normalizer,
            '/de/site/login/',
            '/de/site/login',
            301,
            self::fixtures(),
        ];
        yield 'normalizer: /en/site/login/' => [
            $normalizer,
            '/en/site/login/',
            '/site/login',
            302,
            self::fixtures(),
        ];
        yield 'normalizer: /en/site/login' => [
            $normalizer,
            '/en/site/login',
            '/site/login',
            302,
            self::fixtures(),
        ];
        yield 'normalizer: /custom/' => [
            $normalizer,
            '/custom/',
            '/custom',
            301,
            self::fixtures(),
        ];
        yield 'normalizer: /en/custom' => [
            $normalizer,
            '/en/custom',
            '/custom',
            302,
            self::fixtures(),
        ];
        yield 'normalizer: /en/custom/' => [
            $normalizer,
            '/en/custom/',
            '/custom',
            302,
            self::fixtures(),
        ];
        yield 'normalizer: /de/custom/' => [
            $normalizer,
            '/de/custom/',
            '/de/custom',
            301,
            self::fixtures(),
        ];
        yield 'normalizer: /slash' => [
            $normalizer,
            '/slash',
            '/slash/',
            301,
            self::fixtures(),
        ];
        yield 'normalizer: /en/slash' => [
            $normalizer,
            '/en/slash',
            '/slash/',
            302,
            self::fixtures(),
        ];
        yield 'normalizer: /en/slash/' => [
            $normalizer,
            '/en/slash/',
            '/slash/',
            302,
            self::fixtures(),
        ];
        yield 'normalizer: /de/slash' => [
            $normalizer,
            '/de/slash',
            '/de/slash/',
            301,
            self::fixtures(),
        ];
        yield 'normalizer: /site/page/?a=b' => [
            $normalizer,
            '/site/page/?a=b',
            '/site/page?a=b',
            301,
            self::fixtures(),
        ];
        yield 'normalizer: /de/?a=b' => [
            $normalizer,
            '/de/?a=b',
            '/de?a=b',
            301,
            self::fixtures(),
        ];
        yield 'normalizer: /de/site/login/?a=b' => [
            $normalizer,
            '/de/site/login/?a=b',
            '/de/site/login?a=b',
            301,
            self::fixtures(),
        ];
        yield 'normalizer: /en/site/login/?a=b' => [
            $normalizer,
            '/en/site/login/?a=b',
            '/site/login?a=b',
            302,
            self::fixtures(),
        ];
        yield 'normalizer: /en/site/login?a=b' => [
            $normalizer,
            '/en/site/login?a=b',
            '/site/login?a=b',
            302,
            self::fixtures(),
        ];
        yield 'normalizer: /custom/?a=b' => [
            $normalizer,
            '/custom/?a=b',
            '/custom?a=b',
            301,
            self::fixtures(),
        ];
        yield 'normalizer: /en/custom?a=b' => [
            $normalizer,
            '/en/custom?a=b',
            '/custom?a=b',
            302,
            self::fixtures(),
        ];
        yield 'normalizer: /en/custom/?a=b' => [
            $normalizer,
            '/en/custom/?a=b',
            '/custom?a=b',
            302,
            self::fixtures(),
        ];
        yield 'normalizer: /de/custom/?a=b' => [
            $normalizer,
            '/de/custom/?a=b',
            '/de/custom?a=b',
            301,
            self::fixtures(),
        ];
        yield 'normalizer: /slash?a=b' => [
            $normalizer,
            '/slash?a=b',
            '/slash/?a=b',
            301,
            self::fixtures(),
        ];
        yield 'normalizer: /en/slash?a=b' => [
            $normalizer,
            '/en/slash?a=b',
            '/slash/?a=b',
            302,
            self::fixtures(),
        ];
        yield 'normalizer: /en/slash/?a=b' => [
            $normalizer,
            '/en/slash/?a=b',
            '/slash/?a=b',
            302,
            self::fixtures(),
        ];
        yield 'normalizer: /de/slash?a=b' => [
            $normalizer,
            '/de/slash?a=b',
            '/de/slash/?a=b',
            301,
            self::fixtures(),
        ];
        yield 'normalizer: /slug/value/' => [
            $normalizer,
            '/slug/value/',
            '/slug/value',
            301,
            self::fixtures(),
        ];
        yield 'normalizer: /en/slug/value/' => [
            $normalizer,
            '/en/slug/value/',
            '/slug/value',
            302,
            self::fixtures(),
        ];
        yield 'normalizer: /de/slug/value/' => [
            $normalizer,
            '/de/slug/value/',
            '/de/slug/value',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-suffix-default-code: \'\'' => [
            $normalizerSuffixDefaultCode,
            '',
            '/en/',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-suffix-default-code: \'\' + session de' => [
            $normalizerSuffixDefaultCode,
            '',
            '/de/',
            302,
            self::fixtures(session: 'de'),
        ];
        yield 'normalizer-suffix-default-code: \'\' + cookie fr' => [
            $normalizerSuffixDefaultCode,
            '',
            '/alias/',
            302,
            self::fixtures(cookie: 'fr'),
        ];
        yield 'normalizer-suffix-default-code: \'\' + geo DEU' => [
            $normalizerSuffixDefaultCode,
            '',
            '/de/',
            302,
            self::fixtures(geo: 'DEU'),
        ];
        yield 'normalizer-suffix-default-code: /site/page' => [
            $normalizerSuffixDefaultCode,
            '/site/page',
            '/en/site/page/',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-suffix-default-code: /site/page + session de' => [
            $normalizerSuffixDefaultCode,
            '/site/page',
            '/de/site/page/',
            302,
            self::fixtures(session: 'de'),
        ];
        yield 'normalizer-suffix-default-code: /site/page + cookie fr' => [
            $normalizerSuffixDefaultCode,
            '/site/page',
            '/alias/site/page/',
            302,
            self::fixtures(cookie: 'fr'),
        ];
        yield 'normalizer-suffix-default-code: /site/page + geo DEU' => [
            $normalizerSuffixDefaultCode,
            '/site/page',
            '/de/site/page/',
            302,
            self::fixtures(geo: 'DEU'),
        ];
        yield 'normalizer-suffix-default-code: /en/site/page' => [
            $normalizerSuffixDefaultCode,
            '/en/site/page',
            '/en/site/page/',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-suffix-default-code: /custom' => [
            $normalizerSuffixDefaultCode,
            '/custom',
            '/en/custom/',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-suffix-default-code: /custom/' => [
            $normalizerSuffixDefaultCode,
            '/custom/',
            '/en/custom/',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-suffix-default-code: /en/custom' => [
            $normalizerSuffixDefaultCode,
            '/en/custom',
            '/en/custom/',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-suffix-default-code: /de/custom' => [
            $normalizerSuffixDefaultCode,
            '/de/custom',
            '/de/custom/',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-suffix-default-code: /noslash' => [
            $normalizerSuffixDefaultCode,
            '/noslash',
            '/en/noslash',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-suffix-default-code: /noslash/' => [
            $normalizerSuffixDefaultCode,
            '/noslash/',
            '/en/noslash',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-suffix-default-code: /en/noslash/' => [
            $normalizerSuffixDefaultCode,
            '/en/noslash/',
            '/en/noslash',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-suffix-default-code: /de/noslash/' => [
            $normalizerSuffixDefaultCode,
            '/de/noslash/',
            '/de/noslash',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-suffix-default-code: ?a=b' => [
            $normalizerSuffixDefaultCode,
            '?a=b',
            '/en/?a=b',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-suffix-default-code: /site/page?a=b' => [
            $normalizerSuffixDefaultCode,
            '/site/page?a=b',
            '/en/site/page/?a=b',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-suffix-default-code: /custom?a=b' => [
            $normalizerSuffixDefaultCode,
            '/custom?a=b',
            '/en/custom/?a=b',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-suffix-default-code: /custom/?a=b' => [
            $normalizerSuffixDefaultCode,
            '/custom/?a=b',
            '/en/custom/?a=b',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-suffix-default-code: /en/custom?a=b' => [
            $normalizerSuffixDefaultCode,
            '/en/custom?a=b',
            '/en/custom/?a=b',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-suffix-default-code: /de/custom?a=b' => [
            $normalizerSuffixDefaultCode,
            '/de/custom?a=b',
            '/de/custom/?a=b',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-suffix-default-code: /noslash?a=b' => [
            $normalizerSuffixDefaultCode,
            '/noslash?a=b',
            '/en/noslash?a=b',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-suffix-default-code: /noslash/?a=b' => [
            $normalizerSuffixDefaultCode,
            '/noslash/?a=b',
            '/en/noslash?a=b',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-suffix-default-code: /en/noslash/?a=b' => [
            $normalizerSuffixDefaultCode,
            '/en/noslash/?a=b',
            '/en/noslash?a=b',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-suffix-default-code: /de/noslash/?a=b' => [
            $normalizerSuffixDefaultCode,
            '/de/noslash/?a=b',
            '/de/noslash?a=b',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-suffix-default-code: /slug/value' => [
            $normalizerSuffixDefaultCode,
            '/slug/value',
            '/en/slug/value/',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-suffix-default-code: /en/slug/value' => [
            $normalizerSuffixDefaultCode,
            '/en/slug/value',
            '/en/slug/value/',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-suffix-default-code: /de/slug/value' => [
            $normalizerSuffixDefaultCode,
            '/de/slug/value',
            '/de/slug/value/',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-default-code: /' => [
            $normalizerDefaultCode,
            '/',
            '/en',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-default-code: / + session de' => [
            $normalizerDefaultCode,
            '/',
            '/de',
            302,
            self::fixtures(session: 'de'),
        ];
        yield 'normalizer-default-code: / + cookie fr' => [
            $normalizerDefaultCode,
            '/',
            '/alias',
            302,
            self::fixtures(cookie: 'fr'),
        ];
        yield 'normalizer-default-code: / + geo DEU' => [
            $normalizerDefaultCode,
            '/',
            '/de',
            302,
            self::fixtures(geo: 'DEU'),
        ];
        yield 'normalizer-default-code: /site/page/' => [
            $normalizerDefaultCode,
            '/site/page/',
            '/en/site/page',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-default-code: /site/page/ + session de' => [
            $normalizerDefaultCode,
            '/site/page/',
            '/de/site/page',
            302,
            self::fixtures(session: 'de'),
        ];
        yield 'normalizer-default-code: /site/page/ + cookie fr' => [
            $normalizerDefaultCode,
            '/site/page/',
            '/alias/site/page',
            302,
            self::fixtures(cookie: 'fr'),
        ];
        yield 'normalizer-default-code: /site/page/ + geo DEU' => [
            $normalizerDefaultCode,
            '/site/page/',
            '/de/site/page',
            302,
            self::fixtures(geo: 'DEU'),
        ];
        yield 'normalizer-default-code: /en/site/page/' => [
            $normalizerDefaultCode,
            '/en/site/page/',
            '/en/site/page',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-default-code: /custom' => [
            $normalizerDefaultCode,
            '/custom',
            '/en/custom',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-default-code: /custom/' => [
            $normalizerDefaultCode,
            '/custom/',
            '/en/custom',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-default-code: /en/custom/' => [
            $normalizerDefaultCode,
            '/en/custom/',
            '/en/custom',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-default-code: /de/custom/' => [
            $normalizerDefaultCode,
            '/de/custom/',
            '/de/custom',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-default-code: /slash' => [
            $normalizerDefaultCode,
            '/slash',
            '/en/slash/',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-default-code: /slash/' => [
            $normalizerDefaultCode,
            '/slash/',
            '/en/slash/',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-default-code: /en/slash' => [
            $normalizerDefaultCode,
            '/en/slash',
            '/en/slash/',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-default-code: /de/slash' => [
            $normalizerDefaultCode,
            '/de/slash',
            '/de/slash/',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-default-code: /?a=b' => [
            $normalizerDefaultCode,
            '/?a=b',
            '/en?a=b',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-default-code: /site/page/?a=b' => [
            $normalizerDefaultCode,
            '/site/page/?a=b',
            '/en/site/page?a=b',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-default-code: /en/site/page/?a=b' => [
            $normalizerDefaultCode,
            '/en/site/page/?a=b',
            '/en/site/page?a=b',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-default-code: /custom?a=b' => [
            $normalizerDefaultCode,
            '/custom?a=b',
            '/en/custom?a=b',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-default-code: /custom/?a=b' => [
            $normalizerDefaultCode,
            '/custom/?a=b',
            '/en/custom?a=b',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-default-code: /de/custom/?a=b' => [
            $normalizerDefaultCode,
            '/de/custom/?a=b',
            '/de/custom?a=b',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-default-code: /slash?a=b' => [
            $normalizerDefaultCode,
            '/slash?a=b',
            '/en/slash/?a=b',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-default-code: /slash/?a=b' => [
            $normalizerDefaultCode,
            '/slash/?a=b',
            '/en/slash/?a=b',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-default-code: /en/slash?a=b' => [
            $normalizerDefaultCode,
            '/en/slash?a=b',
            '/en/slash/?a=b',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-default-code: /de/slash?a=b' => [
            $normalizerDefaultCode,
            '/de/slash?a=b',
            '/de/slash/?a=b',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-default-code: /slug/value/' => [
            $normalizerDefaultCode,
            '/slug/value/',
            '/en/slug/value',
            302,
            self::fixtures(),
        ];
        yield 'normalizer-default-code: /en/slug/value/' => [
            $normalizerDefaultCode,
            '/en/slug/value/',
            '/en/slug/value',
            301,
            self::fixtures(),
        ];
        yield 'normalizer-default-code: /de/slug/value/' => [
            $normalizerDefaultCode,
            '/de/slug/value/',
            '/de/slug/value',
            301,
            self::fixtures(),
        ];
        yield 'alias-before-code: /de-AT/site/page' => [
            self::aliasBeforeCode(),
            '/de-AT/site/page',
            '/de-at/site/page',
            302,
            self::fixtures(),
        ];
        yield 'alias-and-code: /DE/site/page' => [
            self::aliasAndCode(),
            '/DE/site/page',
            '/de/site/page',
            302,
            self::fixtures(),
        ];
        yield 'alias-and-code: /Deutsch/site/page' => [
            self::aliasAndCode(),
            '/Deutsch/site/page',
            '/deutsch/site/page',
            302,
            self::fixtures(),
        ];
        yield 'no-code: /DE/site/page' => [
            $noCode,
            '/DE/site/page',
            '/de/site/page',
            302,
            self::fixtures(),
        ];
        yield 'no-code: /EN-us/site/page' => [
            $noCode,
            '/EN-us/site/page',
            '/en-us/site/page',
            302,
            self::fixtures(),
        ];
        yield 'no-code: /WC-bb/site/page' => [
            $noCode,
            '/WC-bb/site/page',
            '/wc-bb/site/page',
            302,
            self::fixtures(),
        ];
        yield 'no-code: /site/page + session pt-br' => [
            $noCode,
            '/site/page',
            '/pt/site/page',
            302,
            self::fixtures(session: 'pt-br'),
        ];
        yield 'no-code: /site/page + cookie pt-br' => [
            $noCode,
            '/site/page',
            '/pt/site/page',
            302,
            self::fixtures(cookie: 'pt-br'),
        ];
        yield 'no-code: /en/site/page?105=1&5=1' => [
            $noCode,
            '/en/site/page?105=1&5=1',
            '/site/page?105=1&5=1',
            302,
            self::fixtures(),
        ];
        yield 'no-code: /en/site/page?0=x' => [
            $noCode,
            '/en/site/page?0=x',
            '/site/page?0=x',
            302,
            self::fixtures(),
        ];
        yield 'no-code: /en/site/page?0=x&5=y' => [
            $noCode,
            '/en/site/page?0=x&5=y',
            '/site/page?5=y&0=x',
            302,
            self::fixtures(),
        ];
        yield 'no-code: /en/site/page?language=//evil.com' => [
            $noCode,
            '/en/site/page?language=//evil.com',
            '/site/page',
            302,
            self::fixtures(),
        ];
        yield 'no-code: /site/page?language=pt + session de' => [
            $noCode,
            '/site/page?language=pt',
            '/de/site/page',
            302,
            self::fixtures(session: 'de'),
        ];
        yield 'no-code: /site/page?a=b&language=pt + accept de' => [
            $noCode,
            '/site/page?a=b&language=pt',
            '/de/site/page?a=b',
            302,
            self::fixtures(accept: ['de']),
        ];
        yield 'keep-uppercase: /site/page + cookie en-us' => [
            $keepUppercase,
            '/site/page',
            '/en-US/site/page',
            302,
            self::fixtures(cookie: 'en-us'),
        ];
        yield 'normalizer: /site/page/?105=1&5=1' => [
            $normalizer,
            '/site/page/?105=1&5=1',
            '/site/page?105=1&5=1',
            301,
            self::fixtures(),
        ];
        yield 'normalizer: /site/page/?language=//evil.com' => [
            $normalizer,
            '/site/page/?language=//evil.com',
            '/site/page',
            301,
            self::fixtures(),
        ];
        yield 'no-wildcard: /site/page + cookie es-mx' => [
            self::noWildcard(),
            '/site/page',
            '/es/site/page',
            302,
            self::fixtures(cookie: 'es-mx'),
        ];
        yield 'region-code: /de-AT/site/page' => [
            self::regionCodeUppercase(),
            '/de-AT/site/page',
            '/de-at/site/page',
            302,
            self::fixtures(),
        ];
        yield 'region-code: /DE-AT/site/page' => [
            self::regionCodeUppercase(),
            '/DE-AT/site/page',
            '/de-at/site/page',
            302,
            self::fixtures(),
        ];
        yield 'uppercase-alias: /AT/site/page' => [
            self::uppercaseAlias(),
            '/AT/site/page',
            '/at/site/page',
            302,
            self::fixtures(),
        ];
        yield 'uppercase-alias-and-code: /DEUTSCH/site/page' => [
            self::uppercaseAliasAndCode(),
            '/DEUTSCH/site/page',
            '/deutsch/site/page',
            302,
            self::fixtures(),
        ];
        yield 'wildcard: /ES-MX/site/page' => [
            self::wildcard(),
            '/ES-MX/site/page',
            '/es-mx/site/page',
            302,
            self::fixtures(),
        ];
        yield 'wildcard: /site/page + accept ES-mx,es' => [
            self::wildcard(),
            '/site/page',
            '/es-mx/site/page',
            302,
            self::fixtures(accept: ['ES-mx', 'es']),
        ];
        yield 'wildcard: /site/page + cookie es-419' => [
            self::wildcard(),
            '/site/page',
            '/es/site/page',
            302,
            self::fixtures(cookie: 'es-419'),
        ];
        yield 'wildcard: /site/page + cookie es-*' => [
            self::wildcard(),
            '/site/page',
            '/es/site/page',
            302,
            self::fixtures(cookie: 'es-*'),
        ];
        yield 'wildcard: /site/page + session es-*' => [
            self::wildcard(),
            '/site/page',
            '/es/site/page',
            302,
            self::fixtures(session: 'es-*'),
        ];
        yield 'wildcard: /site/page + cookie es-////evil.com' => [
            self::wildcard(),
            '/site/page',
            '/es/site/page',
            302,
            self::fixtures(cookie: 'es-////evil.com'),
        ];
    }

    /**
     * Returns the URL manager configuration with an alias of a language that is also configured as a plain code.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function aliasAndCode(): array
    {
        return ['languages' => ['en', 'de', 'deutsch' => 'de']];
    }

    /**
     * Returns the URL manager configuration with an alias listed before the code it maps to.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function aliasBeforeCode(): array
    {
        return ['languages' => ['en', 'at' => 'de-AT', 'de-AT']];
    }

    /**
     * Returns the URL manager configuration with a URL code for the default language.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function defaultCode(): array
    {
        return self::withDefaultCode(self::noCode());
    }

    /**
     * Returns the URL manager configuration with ignore patterns.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function ignorePatterns(): array
    {
        return [
            'languages' => self::BASE_LANGUAGES,
            'enableDefaultLanguageUrlCode' => true,
            'ignoreLanguageUrlPatterns' => ['#not/used#' => '#^site/other#'],
        ];
    }

    /**
     * Returns the URL manager configuration with uppercase language codes kept in the URL.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function keepUppercase(): array
    {
        return [
            'languages' => ['en-US', 'deutsch' => 'de', 'es-BO'],
            'keepUppercaseLanguageCode' => true,
        ];
    }

    /**
     * Returns the URL manager configuration with no URL code for the default language.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function noCode(): array
    {
        return [
            'languages' => self::LANGUAGES,
            'geoIpLanguageCountries' => self::GEO_IP,
            'rules' => [...self::ROUTE_RULES, ['class' => UrlRule::class], self::SLASH_RULE],
        ];
    }

    /**
     * Returns the URL manager configuration with persistence and detection disabled.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function noPersistenceDetection(): array
    {
        return [
            'languages' => self::LANGUAGES,
            'enableLanguageDetection' => false,
            'enableLanguagePersistence' => false,
        ];
    }

    /**
     * Returns the URL manager configuration with the URL normalizer and no URL code for the default language.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function normalizer(): array
    {
        return [
            'languages' => self::BASE_LANGUAGES,
            'normalizer' => self::NORMALIZER,
            'rules' => [...self::ROUTE_RULES, self::SLASH_RULE],
        ];
    }

    /**
     * Returns the URL manager configuration with the URL normalizer and a URL code for the default language.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function normalizerDefaultCode(): array
    {
        return self::withDefaultCode(self::normalizer());
    }

    /**
     * Returns the URL manager configuration with the URL normalizer, the `/` suffix and no default language code.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function normalizerSuffix(): array
    {
        return [...self::suffix(), 'normalizer' => self::NORMALIZER];
    }

    /**
     * Returns the URL manager configuration with the URL normalizer, the `/` suffix and a default language code.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function normalizerSuffixDefaultCode(): array
    {
        return self::withDefaultCode(self::normalizerSuffix());
    }

    /**
     * Returns the URL manager configuration with a language that has no wildcard.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function noWildcard(): array
    {
        return ['languages' => ['en', 'es']];
    }

    /**
     * Returns the URL manager configuration with a region code configured in lowercase.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function regionCode(): array
    {
        return ['languages' => ['en', 'de-at']];
    }

    /**
     * Returns the URL manager configuration with a region code configured with an uppercase region.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function regionCodeUppercase(): array
    {
        return ['languages' => ['en', 'de-AT']];
    }

    /**
     * Returns the URL manager configuration with the `/` suffix and no URL code for the default language.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function suffix(): array
    {
        return [
            'languages' => self::BASE_LANGUAGES,
            'suffix' => '/',
            'rules' => [...self::ROUTE_RULES, self::NO_SLASH_RULE],
        ];
    }

    /**
     * Returns the URL manager configuration with the `/` suffix and a URL code for the default language.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function suffixDefaultCode(): array
    {
        return self::withDefaultCode(self::suffix());
    }

    /**
     * Returns the URL manager configuration with an alias configured in uppercase.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function uppercaseAlias(): array
    {
        return ['languages' => ['en', 'AT' => 'de-AT']];
    }

    /**
     * Returns the URL manager configuration with an alias configured in uppercase for a language also configured as a
     * plain code.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function uppercaseAliasAndCode(): array
    {
        return ['languages' => ['en', 'de', 'Deutsch' => 'de']];
    }

    /**
     * Returns the URL manager configuration with a wildcard language.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function wildcard(): array
    {
        return ['languages' => ['en', 'es-*']];
    }

    /**
     * Returns the URL manager configuration with a wildcard listed before a region code.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function wildcardFirst(): array
    {
        return ['languages' => ['es-*', 'en', 'de-AT']];
    }

    /**
     * Returns the URL manager configuration with a wildcard language and uppercase language codes kept in the URL.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function wildcardKeepUppercase(): array
    {
        return [...self::wildcard(), 'keepUppercaseLanguageCode' => true];
    }

    /**
     * Adds the full language list, the GeoIP countries and a URL code for the default language to a configuration.
     *
     * @param array $config URL manager configuration to extend.
     *
     * @phpstan-param array<string, mixed> $config
     * @phpstan-return array<string, mixed>
     */
    private static function withDefaultCode(array $config): array
    {
        return [
            ...$config,
            'languages' => self::LANGUAGES,
            'geoIpLanguageCountries' => self::GEO_IP,
            'enableDefaultLanguageUrlCode' => true,
        ];
    }
}
