<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests\provider;

use Generator;
use yii2\extensions\localeurls\tests\support\stub\UrlRule;

/**
 * Data provider for {@see \yii2\extensions\localeurls\tests\base\AbstractUrlCreation} test cases.
 *
 * Provides representative request URL and route pairs with the URL expected from `Url::to()` for each URL manager
 * configuration.
 *
 * @phpstan-type UrlRow array{
 *   array<string, mixed>,
 *   string,
 *   string,
 *   array<array-key, string>,
 *   'absolute'|'forcedRelative'|'relative',
 * }
 */
final class UrlCreationProvider
{
    /**
     * Languages with the `french` alias.
     */
    private const array ALIAS_LANGUAGES = ['en-US', 'en', 'french' => 'fr', 'de'];
    /**
     * Ignore patterns that match routes ending in `ignored` or `custom`.
     */
    private const array IGNORE_PATTERNS = ['#^.*/ignored$#' => '#not/used#', '#^.*/custom$#' => '#not/used#'];
    /**
     * Languages of the configurations without aliases.
     */
    private const array LANGUAGES = ['en-US', 'en', 'de'];
    /**
     * Home, slug, custom and absolute slug rules shared by the `rules` and `suffix` configurations.
     */
    private const array SHARED_RULES = [
        '' => 'site/index',
        '/foo/<term:.+>/bar' => 'demo/slug',
        '/custom/<term:.+>/bar' => 'demo/custom',
        'http://www.example.com/foo/<term:.+>/bar' => 'demo/absolute-slug',
    ];

    /**
     * Provides routes and the URLs created for them.
     *
     * Each row holds the URL manager configuration, the request URL, the expected URL, the route, and the creation
     * mode: `relative`, `absolute` (scheme taken from the expected URL) or `forcedRelative` (absolute URL expected from
     * a relative creation, because a rule binds the route to a host).
     *
     * @phpstan-return Generator<string, UrlRow>
     */
    public static function urls(): Generator
    {
        $rules = self::rules();
        $suffix = self::suffix();
        $keepUppercase = self::keepUppercase();
        $hostnameRule = self::hostnameRule();
        $noPersistence = self::noPersistence();
        $noDetection = self::noDetection();
        $noPersistenceDetection = self::noPersistenceDetection();
        $localeUrlsDisabled = self::localeUrlsDisabled();
        $noLanguages = self::noLanguages();
        $aliasAndCode = self::aliasAndCode();

        yield 'rules: /site/page -> /' => [
            $rules,
            '/site/page',
            '/',
            ['/site/index'],
            'relative',
        ];
        yield 'rules: /site/page -> /?x=y' => [
            $rules,
            '/site/page',
            '/?x=y',
            ['/site/index', 'x' => 'y'],
            'relative',
        ];
        yield 'rules: /site/page -> /demo/action' => [
            $rules,
            '/site/page',
            '/demo/action',
            ['/demo/action'],
            'relative',
        ];
        yield 'rules: /site/page -> /demo/action?x=y' => [
            $rules,
            '/site/page',
            '/demo/action?x=y',
            ['/demo/action', 'x' => 'y'],
            'relative',
        ];
        yield 'rules: /site/page -> /foo/baz/bar' => [
            $rules,
            '/site/page',
            '/foo/baz/bar',
            ['/demo/slug', 'term' => 'baz'],
            'relative',
        ];
        yield 'rules: /site/page -> /foo/baz/bar?x=y' => [
            $rules,
            '/site/page',
            '/foo/baz/bar?x=y',
            ['/demo/slug', 'term' => 'baz', 'x' => 'y'],
            'relative',
        ];
        yield 'rules: /site/page -> /ruleclass-english' => [
            $rules,
            '/site/page',
            '/ruleclass-english',
            ['/ruleclass/test'],
            'relative',
        ];
        yield 'rules: /site/page -> /slash/' => [
            $rules,
            '/site/page',
            '/slash/',
            ['/demo/slash'],
            'relative',
        ];
        yield 'rules: /site/page -> /slash/?x=y' => [
            $rules,
            '/site/page',
            '/slash/?x=y',
            ['/demo/slash', 'x' => 'y'],
            'relative',
        ];
        yield 'rules: /site/page -> /slugslash/baz/bar/' => [
            $rules,
            '/site/page',
            '/slugslash/baz/bar/',
            ['/demo/slug-slash', 'term' => 'baz'],
            'relative',
        ];
        yield 'rules: /site/page -> /slugslash/baz/bar/?x=y' => [
            $rules,
            '/site/page',
            '/slugslash/baz/bar/?x=y',
            ['/demo/slug-slash', 'term' => 'baz', 'x' => 'y'],
            'relative',
        ];
        yield 'rules: /site/page -> http://localhost/' => [
            $rules,
            '/site/page',
            'http://localhost/',
            ['/site/index'],
            'absolute',
        ];
        yield 'rules: /site/page -> http://localhost/?x=y' => [
            $rules,
            '/site/page',
            'http://localhost/?x=y',
            ['/site/index', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /site/page -> http://localhost/demo/action' => [
            $rules,
            '/site/page',
            'http://localhost/demo/action',
            ['/demo/action'],
            'absolute',
        ];
        yield 'rules: /site/page -> http://localhost/demo/action?x=y' => [
            $rules,
            '/site/page',
            'http://localhost/demo/action?x=y',
            ['/demo/action', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /site/page -> http://localhost/foo/baz/bar' => [
            $rules,
            '/site/page',
            'http://localhost/foo/baz/bar',
            ['/demo/slug', 'term' => 'baz'],
            'absolute',
        ];
        yield 'rules: /site/page -> http://localhost/foo/baz/bar?x=y' => [
            $rules,
            '/site/page',
            'http://localhost/foo/baz/bar?x=y',
            ['/demo/slug', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /site/page -> http://localhost/ruleclass-english' => [
            $rules,
            '/site/page',
            'http://localhost/ruleclass-english',
            ['/ruleclass/test'],
            'absolute',
        ];
        yield 'rules: /site/page -> http://localhost/slash/' => [
            $rules,
            '/site/page',
            'http://localhost/slash/',
            ['/demo/slash'],
            'absolute',
        ];
        yield 'rules: /site/page -> http://localhost/slash/?x=y' => [
            $rules,
            '/site/page',
            'http://localhost/slash/?x=y',
            ['/demo/slash', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /site/page -> http://localhost/slugslash/baz/bar/' => [
            $rules,
            '/site/page',
            'http://localhost/slugslash/baz/bar/',
            ['/demo/slug-slash', 'term' => 'baz'],
            'absolute',
        ];
        yield 'rules: /site/page -> http://localhost/slugslash/baz/bar/?x=y' => [
            $rules,
            '/site/page',
            'http://localhost/slugslash/baz/bar/?x=y',
            ['/demo/slug-slash', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /site/page -> http://www.example.com/foo/baz/bar' => [
            $rules,
            '/site/page',
            'http://www.example.com/foo/baz/bar',
            ['/demo/absolute-slug', 'term' => 'baz'],
            'absolute',
        ];
        yield 'rules: /site/page -> http://www.example.com/foo/baz/bar?x=y' => [
            $rules,
            '/site/page',
            'http://www.example.com/foo/baz/bar?x=y',
            ['/demo/absolute-slug', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> /de' => [
            $rules,
            '/de/site/page',
            '/de',
            ['/site/index'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /de?x=y' => [
            $rules,
            '/de/site/page',
            '/de?x=y',
            ['/site/index', 'x' => 'y'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /de/demo/action' => [
            $rules,
            '/de/site/page',
            '/de/demo/action',
            ['/demo/action'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /de/demo/action?x=y' => [
            $rules,
            '/de/site/page',
            '/de/demo/action?x=y',
            ['/demo/action', 'x' => 'y'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /de/foo/baz/bar' => [
            $rules,
            '/de/site/page',
            '/de/foo/baz/bar',
            ['/demo/slug', 'term' => 'baz'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /de/foo/baz/bar?x=y' => [
            $rules,
            '/de/site/page',
            '/de/foo/baz/bar?x=y',
            ['/demo/slug', 'term' => 'baz', 'x' => 'y'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /de/ruleclass-deutsch' => [
            $rules,
            '/de/site/page',
            '/de/ruleclass-deutsch',
            ['/ruleclass/test', 'slugLanguage' => 'de'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /de/slash/' => [
            $rules,
            '/de/site/page',
            '/de/slash/',
            ['/demo/slash'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /de/slash/?x=y' => [
            $rules,
            '/de/site/page',
            '/de/slash/?x=y',
            ['/demo/slash', 'x' => 'y'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /de/slugslash/baz/bar/' => [
            $rules,
            '/de/site/page',
            '/de/slugslash/baz/bar/',
            ['/demo/slug-slash', 'term' => 'baz'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /de/slugslash/baz/bar/?x=y' => [
            $rules,
            '/de/site/page',
            '/de/slugslash/baz/bar/?x=y',
            ['/demo/slug-slash', 'term' => 'baz', 'x' => 'y'],
            'relative',
        ];
        yield 'rules: /de/site/page -> http://localhost/de' => [
            $rules,
            '/de/site/page',
            'http://localhost/de',
            ['/site/index'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/de?x=y' => [
            $rules,
            '/de/site/page',
            'http://localhost/de?x=y',
            ['/site/index', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/de/demo/action' => [
            $rules,
            '/de/site/page',
            'http://localhost/de/demo/action',
            ['/demo/action'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/de/demo/action?x=y' => [
            $rules,
            '/de/site/page',
            'http://localhost/de/demo/action?x=y',
            ['/demo/action', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/de/foo/baz/bar' => [
            $rules,
            '/de/site/page',
            'http://localhost/de/foo/baz/bar',
            ['/demo/slug', 'term' => 'baz'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/de/foo/baz/bar?x=y' => [
            $rules,
            '/de/site/page',
            'http://localhost/de/foo/baz/bar?x=y',
            ['/demo/slug', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/de/ruleclass-deutsch' => [
            $rules,
            '/de/site/page',
            'http://localhost/de/ruleclass-deutsch',
            ['/ruleclass/test', 'slugLanguage' => 'de'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/de/slash/' => [
            $rules,
            '/de/site/page',
            'http://localhost/de/slash/',
            ['/demo/slash'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/de/slash/?x=y' => [
            $rules,
            '/de/site/page',
            'http://localhost/de/slash/?x=y',
            ['/demo/slash', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/de/slugslash/baz/bar/' => [
            $rules,
            '/de/site/page',
            'http://localhost/de/slugslash/baz/bar/',
            ['/demo/slug-slash', 'term' => 'baz'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/de/slugslash/baz/bar/?x=y' => [
            $rules,
            '/de/site/page',
            'http://localhost/de/slugslash/baz/bar/?x=y',
            ['/demo/slug-slash', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://www.example.com/de/foo/baz/bar' => [
            $rules,
            '/de/site/page',
            'http://www.example.com/de/foo/baz/bar',
            ['/demo/absolute-slug', 'term' => 'baz'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://www.example.com/de/foo/baz/bar?x=y' => [
            $rules,
            '/de/site/page',
            'http://www.example.com/de/foo/baz/bar?x=y',
            ['/demo/absolute-slug', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> /en-us' => [
            $rules,
            '/de/site/page',
            '/en-us',
            ['/', 'language' => 'en-US'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /en-us?x=y' => [
            $rules,
            '/de/site/page',
            '/en-us?x=y',
            ['/site/index', 'language' => 'en-US', 'x' => 'y'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /en-us/demo/action' => [
            $rules,
            '/de/site/page',
            '/en-us/demo/action',
            ['/demo/action', 'language' => 'en-US'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /en-us/demo/action?x=y' => [
            $rules,
            '/de/site/page',
            '/en-us/demo/action?x=y',
            ['/demo/action', 'language' => 'en-US', 'x' => 'y'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /en-us/foo/baz/bar' => [
            $rules,
            '/de/site/page',
            '/en-us/foo/baz/bar',
            ['/demo/slug', 'language' => 'en-US', 'term' => 'baz'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /en-us/foo/baz/bar?x=y' => [
            $rules,
            '/de/site/page',
            '/en-us/foo/baz/bar?x=y',
            ['/demo/slug', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /en-us/ruleclass-english' => [
            $rules,
            '/de/site/page',
            '/en-us/ruleclass-english',
            ['/ruleclass/test', 'language' => 'en-US', 'slugLanguage' => 'en-US'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /en-us/slash/' => [
            $rules,
            '/de/site/page',
            '/en-us/slash/',
            ['/demo/slash', 'language' => 'en-US'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /en-us/slash/?x=y' => [
            $rules,
            '/de/site/page',
            '/en-us/slash/?x=y',
            ['/demo/slash', 'language' => 'en-US', 'x' => 'y'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /en-us/slugslash/baz/bar/' => [
            $rules,
            '/de/site/page',
            '/en-us/slugslash/baz/bar/',
            ['/demo/slug-slash', 'language' => 'en-US', 'term' => 'baz'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /en-us/slugslash/baz/bar/?x=y' => [
            $rules,
            '/de/site/page',
            '/en-us/slugslash/baz/bar/?x=y',
            ['/demo/slug-slash', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],
            'relative',
        ];
        yield 'rules: /de/site/page -> http://localhost/en-us' => [
            $rules,
            '/de/site/page',
            'http://localhost/en-us',
            ['/', 'language' => 'en-US'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/en-us?x=y' => [
            $rules,
            '/de/site/page',
            'http://localhost/en-us?x=y',
            ['/', 'language' => 'en-US', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/en-us/demo/action' => [
            $rules,
            '/de/site/page',
            'http://localhost/en-us/demo/action',
            ['/demo/action', 'language' => 'en-US'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/en-us/demo/action?x=y' => [
            $rules,
            '/de/site/page',
            'http://localhost/en-us/demo/action?x=y',
            ['/demo/action', 'language' => 'en-US', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/en-us/foo/baz/bar' => [
            $rules,
            '/de/site/page',
            'http://localhost/en-us/foo/baz/bar',
            ['/demo/slug', 'language' => 'en-US', 'term' => 'baz'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/en-us/foo/baz/bar?x=y' => [
            $rules,
            '/de/site/page',
            'http://localhost/en-us/foo/baz/bar?x=y',
            ['/demo/slug', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/en-us/ruleclass-english' => [
            $rules,
            '/de/site/page',
            'http://localhost/en-us/ruleclass-english',
            ['/ruleclass/test', 'language' => 'en-US', 'slugLanguage' => 'en-US'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/en-us/slash/' => [
            $rules,
            '/de/site/page',
            'http://localhost/en-us/slash/',
            ['/demo/slash', 'language' => 'en-US'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/en-us/slash/?x=y' => [
            $rules,
            '/de/site/page',
            'http://localhost/en-us/slash/?x=y',
            ['/demo/slash', 'language' => 'en-US', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/en-us/slugslash/baz/bar/' => [
            $rules,
            '/de/site/page',
            'http://localhost/en-us/slugslash/baz/bar/',
            ['/demo/slug-slash', 'language' => 'en-US', 'term' => 'baz'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/en-us/slugslash/baz/bar/?x=y' => [
            $rules,
            '/de/site/page',
            'http://localhost/en-us/slugslash/baz/bar/?x=y',
            ['/demo/slug-slash', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://www.example.com/en-us/foo/baz/bar' => [
            $rules,
            '/de/site/page',
            'http://www.example.com/en-us/foo/baz/bar',
            ['/demo/absolute-slug', 'language' => 'en-US', 'term' => 'baz'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://www.example.com/en-us/foo/baz/bar?x=y' => [
            $rules,
            '/de/site/page',
            'http://www.example.com/en-us/foo/baz/bar?x=y',
            ['/demo/absolute-slug', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> /french'
            => [$rules, '/de/site/page', '/french', ['/', 'language' => 'fr'], 'relative'];
        yield 'rules: /de/site/page -> /french?x=y' => [
            $rules,
            '/de/site/page',
            '/french?x=y',
            ['/site/index', 'language' => 'fr', 'x' => 'y'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /french/demo/action' => [
            $rules,
            '/de/site/page',
            '/french/demo/action',
            ['/demo/action', 'language' => 'fr'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /french/demo/action?x=y' => [
            $rules,
            '/de/site/page',
            '/french/demo/action?x=y',
            ['/demo/action', 'language' => 'fr', 'x' => 'y'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /french/foo/baz/bar' => [
            $rules,
            '/de/site/page',
            '/french/foo/baz/bar',
            ['/demo/slug', 'language' => 'fr', 'term' => 'baz'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /french/foo/baz/bar?x=y' => [
            $rules,
            '/de/site/page',
            '/french/foo/baz/bar?x=y',
            ['/demo/slug', 'language' => 'fr', 'term' => 'baz', 'x' => 'y'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /french/ruleclass-francais' => [
            $rules,
            '/de/site/page',
            '/french/ruleclass-francais',
            ['/ruleclass/test', 'language' => 'fr', 'slugLanguage' => 'fr'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /french/slash/' => [
            $rules,
            '/de/site/page',
            '/french/slash/',
            ['/demo/slash', 'language' => 'fr'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /french/slash/?x=y' => [
            $rules,
            '/de/site/page',
            '/french/slash/?x=y',
            ['/demo/slash', 'language' => 'fr', 'x' => 'y'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /french/slugslash/baz/bar/' => [
            $rules,
            '/de/site/page',
            '/french/slugslash/baz/bar/',
            ['/demo/slug-slash', 'language' => 'fr', 'term' => 'baz'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /french/slugslash/baz/bar/?x=y' => [
            $rules,
            '/de/site/page',
            '/french/slugslash/baz/bar/?x=y',
            ['/demo/slug-slash', 'language' => 'fr', 'term' => 'baz', 'x' => 'y'],
            'relative',
        ];
        yield 'rules: /de/site/page -> http://localhost/french' => [
            $rules,
            '/de/site/page',
            'http://localhost/french',
            ['/', 'language' => 'fr'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/french?x=y' => [
            $rules,
            '/de/site/page',
            'http://localhost/french?x=y',
            ['/', 'language' => 'fr', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/french/demo/action' => [
            $rules,
            '/de/site/page',
            'http://localhost/french/demo/action',
            ['/demo/action', 'language' => 'fr'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/french/demo/action?x=y' => [
            $rules,
            '/de/site/page',
            'http://localhost/french/demo/action?x=y',
            ['/demo/action', 'language' => 'fr', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/french/foo/baz/bar' => [
            $rules,
            '/de/site/page',
            'http://localhost/french/foo/baz/bar',
            ['/demo/slug', 'language' => 'fr', 'term' => 'baz'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/french/foo/baz/bar?x=y' => [
            $rules,
            '/de/site/page',
            'http://localhost/french/foo/baz/bar?x=y',
            ['/demo/slug', 'language' => 'fr', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/french/ruleclass-francais' => [
            $rules,
            '/de/site/page',
            'http://localhost/french/ruleclass-francais',
            ['/ruleclass/test', 'language' => 'fr', 'slugLanguage' => 'fr'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/french/slash/' => [
            $rules,
            '/de/site/page',
            'http://localhost/french/slash/',
            ['/demo/slash', 'language' => 'fr'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/french/slash/?x=y' => [
            $rules,
            '/de/site/page',
            'http://localhost/french/slash/?x=y',
            ['/demo/slash', 'language' => 'fr', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/french/slugslash/baz/bar/' => [
            $rules,
            '/de/site/page',
            'http://localhost/french/slugslash/baz/bar/',
            ['/demo/slug-slash', 'language' => 'fr', 'term' => 'baz'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/french/slugslash/baz/bar/?x=y' => [
            $rules,
            '/de/site/page',
            'http://localhost/french/slugslash/baz/bar/?x=y',
            ['/demo/slug-slash', 'language' => 'fr', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://www.example.com/french/foo/baz/bar' => [
            $rules,
            '/de/site/page',
            'http://www.example.com/french/foo/baz/bar',
            ['/demo/absolute-slug', 'language' => 'fr', 'term' => 'baz'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://www.example.com/french/foo/baz/bar?x=y' => [
            $rules,
            '/de/site/page',
            'http://www.example.com/french/foo/baz/bar?x=y',
            ['/demo/absolute-slug', 'language' => 'fr', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> /demo/ignored' => [
            $rules,
            '/de/site/page',
            '/demo/ignored',
            ['/demo/ignored'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /demo/ignored?x=y' => [
            $rules,
            '/de/site/page',
            '/demo/ignored?x=y',
            ['/demo/ignored', 'x' => 'y'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /custom/baz/bar' => [
            $rules,
            '/de/site/page',
            '/custom/baz/bar',
            ['/demo/custom', 'term' => 'baz'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /custom/baz/bar?x=y' => [
            $rules,
            '/de/site/page',
            '/custom/baz/bar?x=y',
            ['/demo/custom', 'term' => 'baz', 'x' => 'y'],
            'relative',
        ];
        yield 'rules: /de/site/page -> http://localhost/demo/ignored' => [
            $rules,
            '/de/site/page',
            'http://localhost/demo/ignored',
            ['/demo/ignored'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/demo/ignored?x=y' => [
            $rules,
            '/de/site/page',
            'http://localhost/demo/ignored?x=y',
            ['/demo/ignored', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/custom/baz/bar' => [
            $rules,
            '/de/site/page',
            'http://localhost/custom/baz/bar',
            ['/demo/custom', 'term' => 'baz'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/custom/baz/bar?x=y' => [
            $rules,
            '/de/site/page',
            'http://localhost/custom/baz/bar?x=y',
            ['/demo/custom', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> /' => [
            $rules,
            '/de/site/page',
            '/',
            ['/site/index', 'language' => ''],
            'relative',
        ];
        yield 'rules: /de/site/page -> /?x=y' => [
            $rules,
            '/de/site/page',
            '/?x=y',
            ['/site/index', 'language' => '', 'x' => 'y'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /demo/action' => [
            $rules,
            '/de/site/page',
            '/demo/action',
            ['/demo/action', 'language' => ''],
            'relative',
        ];
        yield 'rules: /de/site/page -> /demo/action?x=y' => [
            $rules,
            '/de/site/page',
            '/demo/action?x=y',
            ['/demo/action', 'language' => '', 'x' => 'y'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /foo/baz/bar' => [
            $rules,
            '/de/site/page',
            '/foo/baz/bar',
            ['/demo/slug', 'language' => '', 'term' => 'baz'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /foo/baz/bar?x=y' => [
            $rules,
            '/de/site/page',
            '/foo/baz/bar?x=y',
            ['/demo/slug', 'language' => '', 'term' => 'baz', 'x' => 'y'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /ruleclass-english' => [
            $rules,
            '/de/site/page',
            '/ruleclass-english',
            ['/ruleclass/test', 'language' => '', 'slugLanguage' => 'en'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /slash/' => [
            $rules,
            '/de/site/page',
            '/slash/',
            ['/demo/slash', 'language' => ''],
            'relative',
        ];
        yield 'rules: /de/site/page -> /slash/?x=y' => [
            $rules,
            '/de/site/page',
            '/slash/?x=y',
            ['/demo/slash', 'language' => '', 'x' => 'y'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /slugslash/baz/bar/' => [
            $rules,
            '/de/site/page',
            '/slugslash/baz/bar/',
            ['/demo/slug-slash', 'language' => '', 'term' => 'baz'],
            'relative',
        ];
        yield 'rules: /de/site/page -> /slugslash/baz/bar/?x=y' => [
            $rules,
            '/de/site/page',
            '/slugslash/baz/bar/?x=y',
            ['/demo/slug-slash', 'language' => '', 'term' => 'baz', 'x' => 'y'],
            'relative',
        ];
        yield 'rules: /de/site/page -> http://localhost/' => [
            $rules,
            '/de/site/page',
            'http://localhost/',
            ['/site/index', 'language' => ''],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/?x=y' => [
            $rules,
            '/de/site/page',
            'http://localhost/?x=y',
            ['/site/index', 'language' => '', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/demo/action' => [
            $rules,
            '/de/site/page',
            'http://localhost/demo/action',
            ['/demo/action', 'language' => ''],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/demo/action?x=y' => [
            $rules,
            '/de/site/page',
            'http://localhost/demo/action?x=y',
            ['/demo/action', 'language' => '', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/foo/baz/bar' => [
            $rules,
            '/de/site/page',
            'http://localhost/foo/baz/bar',
            ['/demo/slug', 'language' => '', 'term' => 'baz'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/foo/baz/bar?x=y' => [
            $rules,
            '/de/site/page',
            'http://localhost/foo/baz/bar?x=y',
            ['/demo/slug', 'language' => '', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/ruleclass-english' => [
            $rules,
            '/de/site/page',
            'http://localhost/ruleclass-english',
            ['/ruleclass/test', 'language' => '', 'slugLanguage' => 'en'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/slash/' => [
            $rules,
            '/de/site/page',
            'http://localhost/slash/',
            ['/demo/slash', 'language' => ''],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/slash/?x=y' => [
            $rules,
            '/de/site/page',
            'http://localhost/slash/?x=y',
            ['/demo/slash', 'language' => '', 'x' => 'y'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/slugslash/baz/bar/' => [
            $rules,
            '/de/site/page',
            'http://localhost/slugslash/baz/bar/',
            ['/demo/slug-slash', 'language' => '', 'term' => 'baz'],
            'absolute',
        ];
        yield 'rules: /de/site/page -> http://localhost/slugslash/baz/bar/?x=y' => [
            $rules,
            '/de/site/page',
            'http://localhost/slugslash/baz/bar/?x=y',
            ['/demo/slug-slash', 'language' => '', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /site/page/ -> /' => [
            $suffix,
            '/site/page/',
            '/',
            ['/site/index'],
            'relative',
        ];
        yield 'suffix: /site/page/ -> /?x=y' => [
            $suffix,
            '/site/page/',
            '/?x=y',
            ['/site/index', 'x' => 'y'],
            'relative',
        ];
        yield 'suffix: /site/page/ -> /demo/action/' => [
            $suffix,
            '/site/page/',
            '/demo/action/',
            ['/demo/action'],
            'relative',
        ];
        yield 'suffix: /site/page/ -> /demo/action/?x=y' => [
            $suffix,
            '/site/page/',
            '/demo/action/?x=y',
            ['/demo/action', 'x' => 'y'],
            'relative',
        ];
        yield 'suffix: /site/page/ -> /foo/baz/bar/' => [
            $suffix,
            '/site/page/',
            '/foo/baz/bar/',
            ['/demo/slug', 'term' => 'baz'],
            'relative',
        ];
        yield 'suffix: /site/page/ -> /foo/baz/bar/?x=y' => [
            $suffix,
            '/site/page/',
            '/foo/baz/bar/?x=y',
            ['/demo/slug', 'term' => 'baz', 'x' => 'y'],
            'relative',
        ];
        yield 'suffix: /site/page/ -> /noslash' => [
            $suffix,
            '/site/page/',
            '/noslash',
            ['/demo/noslash'],
            'relative',
        ];
        yield 'suffix: /site/page/ -> /noslash?x=y' => [
            $suffix,
            '/site/page/',
            '/noslash?x=y',
            ['/demo/noslash', 'x' => 'y'],
            'relative',
        ];
        yield 'suffix: /site/page/ -> /slugnoslash/baz/bar/' => [
            $suffix,
            '/site/page/',
            '/slugnoslash/baz/bar/',
            ['/demo/slug-noslash', 'term' => 'baz'],
            'relative',
        ];
        yield 'suffix: /site/page/ -> /slugnoslash/baz/bar/?x=y' => [
            $suffix,
            '/site/page/',
            '/slugnoslash/baz/bar/?x=y',
            ['/demo/slug-noslash', 'term' => 'baz', 'x' => 'y'],
            'relative',
        ];
        yield 'suffix: /site/page/ -> http://localhost/' => [
            $suffix,
            '/site/page/',
            'http://localhost/',
            ['/site/index'],
            'absolute',
        ];
        yield 'suffix: /site/page/ -> http://localhost/?x=y' => [
            $suffix,
            '/site/page/',
            'http://localhost/?x=y',
            ['/site/index', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /site/page/ -> http://localhost/demo/action/' => [
            $suffix,
            '/site/page/',
            'http://localhost/demo/action/',
            ['/demo/action'],
            'absolute',
        ];
        yield 'suffix: /site/page/ -> http://localhost/demo/action/?x=y' => [
            $suffix,
            '/site/page/',
            'http://localhost/demo/action/?x=y',
            ['/demo/action', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /site/page/ -> http://localhost/foo/baz/bar/' => [
            $suffix,
            '/site/page/',
            'http://localhost/foo/baz/bar/',
            ['/demo/slug', 'term' => 'baz'],
            'absolute',
        ];
        yield 'suffix: /site/page/ -> http://localhost/foo/baz/bar/?x=y' => [
            $suffix,
            '/site/page/',
            'http://localhost/foo/baz/bar/?x=y',
            ['/demo/slug', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /site/page/ -> http://localhost/noslash' => [
            $suffix,
            '/site/page/',
            'http://localhost/noslash',
            ['/demo/noslash'],
            'absolute',
        ];
        yield 'suffix: /site/page/ -> http://localhost/noslash?x=y' => [
            $suffix,
            '/site/page/',
            'http://localhost/noslash?x=y',
            ['/demo/noslash', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /site/page/ -> http://localhost/slugnoslash/baz/bar/' => [
            $suffix,
            '/site/page/',
            'http://localhost/slugnoslash/baz/bar/',
            ['/demo/slug-noslash', 'term' => 'baz'],
            'absolute',
        ];
        yield 'suffix: /site/page/ -> http://localhost/slugnoslash/baz/bar/?x=y' => [
            $suffix,
            '/site/page/',
            'http://localhost/slugnoslash/baz/bar/?x=y',
            ['/demo/slug-noslash', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /site/page/ -> http://www.example.com/foo/baz/bar/' => [
            $suffix,
            '/site/page/',
            'http://www.example.com/foo/baz/bar/',
            ['/demo/absolute-slug', 'term' => 'baz'],
            'absolute',
        ];
        yield 'suffix: /site/page/ -> http://www.example.com/foo/baz/bar/?x=y' => [
            $suffix,
            '/site/page/',
            'http://www.example.com/foo/baz/bar/?x=y',
            ['/demo/absolute-slug', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> /de/' => [
            $suffix,
            '/de/site/page/',
            '/de/',
            ['/site/index'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /de/?x=y' => [
            $suffix,
            '/de/site/page/',
            '/de/?x=y',
            ['/site/index', 'x' => 'y'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /de/demo/action/' => [
            $suffix,
            '/de/site/page/',
            '/de/demo/action/',
            ['/demo/action'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /de/demo/action/?x=y' => [
            $suffix,
            '/de/site/page/',
            '/de/demo/action/?x=y',
            ['/demo/action', 'x' => 'y'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /de/foo/baz/bar/' => [
            $suffix,
            '/de/site/page/',
            '/de/foo/baz/bar/',
            ['/demo/slug', 'term' => 'baz'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /de/foo/baz/bar/?x=y' => [
            $suffix,
            '/de/site/page/',
            '/de/foo/baz/bar/?x=y',
            ['/demo/slug', 'term' => 'baz', 'x' => 'y'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /de/noslash' => [
            $suffix,
            '/de/site/page/',
            '/de/noslash',
            ['/demo/noslash'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /de/noslash?x=y' => [
            $suffix,
            '/de/site/page/',
            '/de/noslash?x=y',
            ['/demo/noslash', 'x' => 'y'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /de/slugnoslash/baz/bar/' => [
            $suffix,
            '/de/site/page/',
            '/de/slugnoslash/baz/bar/',
            ['/demo/slug-noslash', 'term' => 'baz'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /de/slugnoslash/baz/bar/?x=y' => [
            $suffix,
            '/de/site/page/',
            '/de/slugnoslash/baz/bar/?x=y',
            ['/demo/slug-noslash', 'term' => 'baz', 'x' => 'y'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/de/' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/de/',
            ['/site/index'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/de/?x=y' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/de/?x=y',
            ['/site/index', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/de/demo/action/' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/de/demo/action/',
            ['/demo/action'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/de/demo/action/?x=y' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/de/demo/action/?x=y',
            ['/demo/action', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/de/foo/baz/bar/' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/de/foo/baz/bar/',
            ['/demo/slug', 'term' => 'baz'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/de/foo/baz/bar/?x=y' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/de/foo/baz/bar/?x=y',
            ['/demo/slug', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/de/noslash' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/de/noslash',
            ['/demo/noslash'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/de/noslash?x=y' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/de/noslash?x=y',
            ['/demo/noslash', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/de/slugnoslash/baz/bar/' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/de/slugnoslash/baz/bar/',
            ['/demo/slug-noslash', 'term' => 'baz'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/de/slugnoslash/baz/bar/?x=y' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/de/slugnoslash/baz/bar/?x=y',
            ['/demo/slug-noslash', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://www.example.com/de/foo/baz/bar/' => [
            $suffix,
            '/de/site/page/',
            'http://www.example.com/de/foo/baz/bar/',
            ['/demo/absolute-slug', 'term' => 'baz'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://www.example.com/de/foo/baz/bar/?x=y' => [
            $suffix,
            '/de/site/page/',
            'http://www.example.com/de/foo/baz/bar/?x=y',
            ['/demo/absolute-slug', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> /en-us/' => [
            $suffix,
            '/de/site/page/',
            '/en-us/',
            ['/site/index', 'language' => 'en-US'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /en-us/?x=y' => [
            $suffix,
            '/de/site/page/',
            '/en-us/?x=y',
            ['/site/index', 'language' => 'en-US', 'x' => 'y'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /en-us/demo/action/' => [
            $suffix,
            '/de/site/page/',
            '/en-us/demo/action/',
            ['/demo/action', 'language' => 'en-US'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /en-us/demo/action/?x=y' => [
            $suffix,
            '/de/site/page/',
            '/en-us/demo/action/?x=y',
            ['/demo/action', 'language' => 'en-US', 'x' => 'y'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /en-us/foo/baz/bar/' => [
            $suffix,
            '/de/site/page/',
            '/en-us/foo/baz/bar/',
            ['/demo/slug', 'language' => 'en-US', 'term' => 'baz'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /en-us/foo/baz/bar/?x=y' => [
            $suffix,
            '/de/site/page/',
            '/en-us/foo/baz/bar/?x=y',
            ['/demo/slug', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /en-us/noslash' => [
            $suffix,
            '/de/site/page/',
            '/en-us/noslash',
            ['/demo/noslash', 'language' => 'en-US'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /en-us/noslash?x=y' => [
            $suffix,
            '/de/site/page/',
            '/en-us/noslash?x=y',
            ['/demo/noslash', 'language' => 'en-US', 'x' => 'y'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /en-us/slugnoslash/baz/bar/' => [
            $suffix,
            '/de/site/page/',
            '/en-us/slugnoslash/baz/bar/',
            ['/demo/slug-noslash', 'language' => 'en-US', 'term' => 'baz'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /en-us/slugnoslash/baz/bar/?x=y' => [
            $suffix,
            '/de/site/page/',
            '/en-us/slugnoslash/baz/bar/?x=y',
            ['/demo/slug-noslash', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/en-us/' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/en-us/',
            ['/site/index', 'language' => 'en-US'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/en-us/?x=y' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/en-us/?x=y',
            ['/site/index', 'language' => 'en-US', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/en-us/demo/action/' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/en-us/demo/action/',
            ['/demo/action', 'language' => 'en-US'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/en-us/demo/action/?x=y' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/en-us/demo/action/?x=y',
            ['/demo/action', 'language' => 'en-US', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/en-us/foo/baz/bar/' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/en-us/foo/baz/bar/',
            ['/demo/slug', 'language' => 'en-US', 'term' => 'baz'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/en-us/foo/baz/bar/?x=y' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/en-us/foo/baz/bar/?x=y',
            ['/demo/slug', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/en-us/noslash' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/en-us/noslash',
            ['/demo/noslash', 'language' => 'en-US'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/en-us/noslash?x=y' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/en-us/noslash?x=y',
            ['/demo/noslash', 'language' => 'en-US', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/en-us/slugnoslash/baz/bar/' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/en-us/slugnoslash/baz/bar/',
            ['/demo/slug-noslash', 'language' => 'en-US', 'term' => 'baz'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/en-us/slugnoslash/baz/bar/?x=y' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/en-us/slugnoslash/baz/bar/?x=y',
            ['/demo/slug-noslash', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://www.example.com/en-us/foo/baz/bar/' => [
            $suffix,
            '/de/site/page/',
            'http://www.example.com/en-us/foo/baz/bar/',
            ['/demo/absolute-slug', 'language' => 'en-US', 'term' => 'baz'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://www.example.com/en-us/foo/baz/bar/?x=y' => [
            $suffix,
            '/de/site/page/',
            'http://www.example.com/en-us/foo/baz/bar/?x=y',
            ['/demo/absolute-slug', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> /french/' => [
            $suffix,
            '/de/site/page/',
            '/french/',
            ['/site/index', 'language' => 'fr'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /french/?x=y' => [
            $suffix,
            '/de/site/page/',
            '/french/?x=y',
            ['/site/index', 'language' => 'fr', 'x' => 'y'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /french/demo/action/' => [
            $suffix,
            '/de/site/page/',
            '/french/demo/action/',
            ['/demo/action', 'language' => 'fr'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /french/demo/action/?x=y' => [
            $suffix,
            '/de/site/page/',
            '/french/demo/action/?x=y',
            ['/demo/action', 'language' => 'fr', 'x' => 'y'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /french/foo/baz/bar/' => [
            $suffix,
            '/de/site/page/',
            '/french/foo/baz/bar/',
            ['/demo/slug', 'language' => 'fr', 'term' => 'baz'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /french/foo/baz/bar/?x=y' => [
            $suffix,
            '/de/site/page/',
            '/french/foo/baz/bar/?x=y',
            ['/demo/slug', 'language' => 'fr', 'term' => 'baz', 'x' => 'y'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /french/noslash' => [
            $suffix,
            '/de/site/page/',
            '/french/noslash',
            ['/demo/noslash', 'language' => 'fr'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /french/noslash?x=y' => [
            $suffix,
            '/de/site/page/',
            '/french/noslash?x=y',
            ['/demo/noslash', 'language' => 'fr', 'x' => 'y'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /french/slugnoslash/baz/bar/' => [
            $suffix,
            '/de/site/page/',
            '/french/slugnoslash/baz/bar/',
            ['/demo/slug-noslash', 'language' => 'fr', 'term' => 'baz'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /french/slugnoslash/baz/bar/?x=y' => [
            $suffix,
            '/de/site/page/',
            '/french/slugnoslash/baz/bar/?x=y',
            ['/demo/slug-noslash', 'language' => 'fr', 'term' => 'baz', 'x' => 'y'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/french/' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/french/',
            ['/site/index', 'language' => 'fr'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/french/?x=y' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/french/?x=y',
            ['/site/index', 'language' => 'fr', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/french/demo/action/' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/french/demo/action/',
            ['/demo/action', 'language' => 'fr'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/french/demo/action/?x=y' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/french/demo/action/?x=y',
            ['/demo/action', 'language' => 'fr', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/french/foo/baz/bar/' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/french/foo/baz/bar/',
            ['/demo/slug', 'language' => 'fr', 'term' => 'baz'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/french/foo/baz/bar/?x=y' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/french/foo/baz/bar/?x=y',
            ['/demo/slug', 'language' => 'fr', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/french/noslash' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/french/noslash',
            ['/demo/noslash', 'language' => 'fr'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/french/noslash?x=y' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/french/noslash?x=y',
            ['/demo/noslash', 'language' => 'fr', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/french/slugnoslash/baz/bar/' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/french/slugnoslash/baz/bar/',
            ['/demo/slug-noslash', 'language' => 'fr', 'term' => 'baz'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/french/slugnoslash/baz/bar/?x=y' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/french/slugnoslash/baz/bar/?x=y',
            ['/demo/slug-noslash', 'language' => 'fr', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://www.example.com/french/foo/baz/bar/' => [
            $suffix,
            '/de/site/page/',
            'http://www.example.com/french/foo/baz/bar/',
            ['/demo/absolute-slug', 'language' => 'fr', 'term' => 'baz'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://www.example.com/french/foo/baz/bar/?x=y' => [
            $suffix,
            '/de/site/page/',
            'http://www.example.com/french/foo/baz/bar/?x=y',
            ['/demo/absolute-slug', 'language' => 'fr', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> /demo/ignored/' => [
            $suffix,
            '/de/site/page/',
            '/demo/ignored/',
            ['/demo/ignored'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /demo/ignored/?x=y' => [
            $suffix,
            '/de/site/page/',
            '/demo/ignored/?x=y',
            ['/demo/ignored', 'x' => 'y'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /custom/baz/bar/' => [
            $suffix,
            '/de/site/page/',
            '/custom/baz/bar/',
            ['/demo/custom', 'term' => 'baz'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /custom/baz/bar/?x=y' => [
            $suffix,
            '/de/site/page/',
            '/custom/baz/bar/?x=y',
            ['/demo/custom', 'term' => 'baz', 'x' => 'y'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/demo/ignored/' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/demo/ignored/',
            ['/demo/ignored'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/demo/ignored/?x=y' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/demo/ignored/?x=y',
            ['/demo/ignored', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/custom/baz/bar/' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/custom/baz/bar/',
            ['/demo/custom', 'term' => 'baz'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/custom/baz/bar/?x=y' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/custom/baz/bar/?x=y',
            ['/demo/custom', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> /' => [
            $suffix,
            '/de/site/page/',
            '/',
            ['/site/index', 'language' => ''],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /?x=y' => [
            $suffix,
            '/de/site/page/',
            '/?x=y',
            ['/site/index', 'language' => '', 'x' => 'y'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /demo/action/' => [
            $suffix,
            '/de/site/page/',
            '/demo/action/',
            ['/demo/action', 'language' => ''],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /demo/action/?x=y' => [
            $suffix,
            '/de/site/page/',
            '/demo/action/?x=y',
            ['/demo/action', 'language' => '', 'x' => 'y'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /foo/baz/bar/' => [
            $suffix,
            '/de/site/page/',
            '/foo/baz/bar/',
            ['/demo/slug', 'language' => '', 'term' => 'baz'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /foo/baz/bar/?x=y' => [
            $suffix,
            '/de/site/page/',
            '/foo/baz/bar/?x=y',
            ['/demo/slug', 'language' => '', 'term' => 'baz', 'x' => 'y'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /noslash' => [
            $suffix,
            '/de/site/page/',
            '/noslash',
            ['/demo/noslash', 'language' => ''],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /noslash?x=y' => [
            $suffix,
            '/de/site/page/',
            '/noslash?x=y',
            ['/demo/noslash', 'language' => '', 'x' => 'y'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /slugnoslash/baz/bar/' => [
            $suffix,
            '/de/site/page/',
            '/slugnoslash/baz/bar/',
            ['/demo/slug-noslash', 'language' => '', 'term' => 'baz'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> /slugnoslash/baz/bar/?x=y' => [
            $suffix,
            '/de/site/page/',
            '/slugnoslash/baz/bar/?x=y',
            ['/demo/slug-noslash', 'language' => '', 'term' => 'baz', 'x' => 'y'],
            'relative',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/',
            ['/site/index', 'language' => ''],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/?x=y' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/?x=y',
            ['/site/index', 'language' => '', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/demo/action/' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/demo/action/',
            ['/demo/action', 'language' => ''],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/demo/action/?x=y' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/demo/action/?x=y',
            ['/demo/action', 'language' => '', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/foo/baz/bar/' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/foo/baz/bar/',
            ['/demo/slug', 'language' => '', 'term' => 'baz'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/foo/baz/bar/?x=y' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/foo/baz/bar/?x=y',
            ['/demo/slug', 'language' => '', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/noslash' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/noslash',
            ['/demo/noslash', 'language' => ''],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/noslash?x=y' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/noslash?x=y',
            ['/demo/noslash', 'language' => '', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/slugnoslash/baz/bar/' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/slugnoslash/baz/bar/',
            ['/demo/slug-noslash', 'language' => '', 'term' => 'baz'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://localhost/slugnoslash/baz/bar/?x=y' => [
            $suffix,
            '/de/site/page/',
            'http://localhost/slugnoslash/baz/bar/?x=y',
            ['/demo/slug-noslash', 'language' => '', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://www.example.com/foo/baz/bar/' => [
            $suffix,
            '/de/site/page/',
            'http://www.example.com/foo/baz/bar/',
            ['/demo/absolute-slug', 'language' => '', 'term' => 'baz'],
            'absolute',
        ];
        yield 'suffix: /de/site/page/ -> http://www.example.com/foo/baz/bar/?x=y' => [
            $suffix,
            '/de/site/page/',
            'http://www.example.com/foo/baz/bar/?x=y',
            ['/demo/absolute-slug', 'language' => '', 'term' => 'baz', 'x' => 'y'],
            'absolute',
        ];
        yield 'keep-uppercase: /en-US/site/page -> /en-US/demo/action' => [
            $keepUppercase,
            '/en-US/site/page',
            '/en-US/demo/action',
            ['/demo/action'],
            'relative',
        ];
        yield 'keep-uppercase: /en-US/site/page -> /en-US/demo/action?x=y' => [
            $keepUppercase,
            '/en-US/site/page',
            '/en-US/demo/action?x=y',
            ['/demo/action', 'x' => 'y'],
            'relative',
        ];
        yield 'keep-uppercase: /en-US/site/page -> /en-US/foo/baz/bar' => [
            $keepUppercase,
            '/en-US/site/page',
            '/en-US/foo/baz/bar',
            ['/demo/slug', 'term' => 'baz'],
            'relative',
        ];
        yield 'keep-uppercase: /en-US/site/page -> /en-US/foo/baz/bar?x=y' => [
            $keepUppercase,
            '/en-US/site/page',
            '/en-US/foo/baz/bar?x=y',
            ['/demo/slug', 'term' => 'baz', 'x' => 'y'],
            'relative',
        ];
        yield 'keep-uppercase: /de/site/page -> /en-US/demo/action' => [
            $keepUppercase,
            '/de/site/page',
            '/en-US/demo/action',
            ['/demo/action', 'language' => 'en-US'],
            'relative',
        ];
        yield 'keep-uppercase: /de/site/page -> /en-US/demo/action?x=y' => [
            $keepUppercase,
            '/de/site/page',
            '/en-US/demo/action?x=y',
            ['/demo/action', 'language' => 'en-US', 'x' => 'y'],
            'relative',
        ];
        yield 'keep-uppercase: /de/site/page -> /en-US/foo/baz/bar' => [
            $keepUppercase,
            '/de/site/page',
            '/en-US/foo/baz/bar',
            ['/demo/slug', 'language' => 'en-US', 'term' => 'baz'],
            'relative',
        ];
        yield 'keep-uppercase: /de/site/page -> /en-US/foo/baz/bar?x=y' => [
            $keepUppercase,
            '/de/site/page',
            '/en-US/foo/baz/bar?x=y',
            ['/demo/slug', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],
            'relative',
        ];
        yield 'hostname-rule: /de/site/page -> http://www.example.com/de' => [
            $hostnameRule,
            '/de/site/page',
            'http://www.example.com/de',
            ['/site/index'],
            'forcedRelative',
        ];
        yield 'no-persistence: /de/site/page/ -> /en' => [
            $noPersistence,
            '/de/site/page/',
            '/en',
            ['/', 'language' => 'en'],
            'relative',
        ];
        yield 'no-persistence: /de/site/page/ -> /en/demo/action' => [
            $noPersistence,
            '/de/site/page/',
            '/en/demo/action',
            ['/demo/action', 'language' => 'en'],
            'relative',
        ];
        yield 'no-persistence: /de/site/page/ -> /en/demo/action?x=y' => [
            $noPersistence,
            '/de/site/page/',
            '/en/demo/action?x=y',
            ['/demo/action', 'language' => 'en', 'x' => 'y'],
            'relative',
        ];
        yield 'no-persistence: /de/site/page/ -> /en/foo/baz/bar' => [
            $noPersistence,
            '/de/site/page/',
            '/en/foo/baz/bar',
            ['/demo/slug', 'language' => 'en', 'term' => 'baz'],
            'relative',
        ];
        yield 'no-persistence: /de/site/page/ -> /en/foo/baz/bar?x=y' => [
            $noPersistence,
            '/de/site/page/',
            '/en/foo/baz/bar?x=y',
            ['/demo/slug', 'language' => 'en', 'term' => 'baz', 'x' => 'y'],
            'relative',
        ];
        yield 'no-detection: /de/site/page/ -> /en' => [
            $noDetection,
            '/de/site/page/',
            '/en',
            ['/', 'language' => 'en'],
            'relative',
        ];
        yield 'no-detection: /de/site/page/ -> /en/demo/action' => [
            $noDetection,
            '/de/site/page/',
            '/en/demo/action',
            ['/demo/action', 'language' => 'en'],
            'relative',
        ];
        yield 'no-detection: /de/site/page/ -> /en/demo/action?x=y' => [
            $noDetection,
            '/de/site/page/',
            '/en/demo/action?x=y',
            ['/demo/action', 'language' => 'en', 'x' => 'y'],
            'relative',
        ];
        yield 'no-detection: /de/site/page/ -> /en/foo/baz/bar' => [
            $noDetection,
            '/de/site/page/',
            '/en/foo/baz/bar',
            ['/demo/slug', 'language' => 'en', 'term' => 'baz'],
            'relative',
        ];
        yield 'no-detection: /de/site/page/ -> /en/foo/baz/bar?x=y' => [
            $noDetection,
            '/de/site/page/',
            '/en/foo/baz/bar?x=y',
            ['/demo/slug', 'language' => 'en', 'term' => 'baz', 'x' => 'y'],
            'relative',
        ];
        yield 'no-persistence-detection: /de/site/page/ -> /' => [
            $noPersistenceDetection,
            '/de/site/page/',
            '/',
            ['/', 'language' => 'en'],
            'relative',
        ];
        yield 'no-persistence-detection: /de/site/page/ -> /demo/action' => [
            $noPersistenceDetection,
            '/de/site/page/',
            '/demo/action',
            ['/demo/action', 'language' => 'en'],
            'relative',
        ];
        yield 'no-persistence-detection: /de/site/page/ -> /demo/action?x=y' => [
            $noPersistenceDetection,
            '/de/site/page/',
            '/demo/action?x=y',
            ['/demo/action', 'language' => 'en', 'x' => 'y'],
            'relative',
        ];
        yield 'no-persistence-detection: /de/site/page/ -> /foo/baz/bar' => [
            $noPersistenceDetection,
            '/de/site/page/',
            '/foo/baz/bar',
            ['/demo/slug', 'language' => 'en', 'term' => 'baz'],
            'relative',
        ];
        yield 'no-persistence-detection: /de/site/page/ -> /foo/baz/bar?x=y' => [
            $noPersistenceDetection,
            '/de/site/page/',
            '/foo/baz/bar?x=y',
            ['/demo/slug', 'language' => 'en', 'term' => 'baz', 'x' => 'y'],
            'relative',
        ];
        yield 'locale-urls-disabled: /site/page -> /' => [
            $localeUrlsDisabled,
            '/site/page',
            '/',
            ['/'],
            'relative',
        ];
        yield 'locale-urls-disabled: /site/page -> /demo/action?language=de' => [
            $localeUrlsDisabled,
            '/site/page',
            '/demo/action?language=de',
            ['/demo/action', 'language' => 'de'],
            'relative',
        ];
        yield 'locale-urls-disabled: /site/page -> /foo/baz/bar?language=de' => [
            $localeUrlsDisabled,
            '/site/page',
            '/foo/baz/bar?language=de',
            ['/demo/slug', 'language' => 'de', 'term' => 'baz'],
            'relative',
        ];
        yield 'no-languages: /site/page -> /' => [
            $noLanguages,
            '/site/page',
            '/',
            ['/'],
            'relative',
        ];
        yield 'no-languages: /site/page -> /demo/action?language=de' => [
            $noLanguages,
            '/site/page',
            '/demo/action?language=de',
            ['/demo/action', 'language' => 'de'],
            'relative',
        ];
        yield 'no-languages: /site/page -> /foo/baz/bar?language=de' => [
            $noLanguages,
            '/site/page',
            '/foo/baz/bar?language=de',
            ['/demo/slug', 'language' => 'de', 'term' => 'baz'],
            'relative',
        ];
        yield 'alias-and-code: /deutsch/site/page -> /de/demo/action' => [
            $aliasAndCode,
            '/deutsch/site/page',
            '/de/demo/action',
            ['/demo/action'],
            'relative',
        ];
        yield 'alias-and-code: /site/page -> /de/demo/action' => [
            $aliasAndCode,
            '/site/page',
            '/de/demo/action',
            ['/demo/action', 'language' => 'de'],
            'relative',
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
     * Returns the URL manager configuration with a rule bound to a host name.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function hostnameRule(): array
    {
        return [
            'languages' => self::LANGUAGES,
            'rules' => ['http://www.example.com' => 'site/index'],
        ];
    }

    /**
     * Returns the URL manager configuration with uppercase language codes kept in the URL.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function keepUppercase(): array
    {
        return [...self::slug(), 'keepUppercaseLanguageCode' => true];
    }

    /**
     * Returns the URL manager configuration with locale URLs disabled.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function localeUrlsDisabled(): array
    {
        return [...self::slug(), 'enableLocaleUrls' => false];
    }

    /**
     * Returns the URL manager configuration with detection disabled.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function noDetection(): array
    {
        return [...self::slug(), 'enableLanguageDetection' => false];
    }

    /**
     * Returns the URL manager configuration with no languages.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function noLanguages(): array
    {
        return [...self::slug(), 'languages' => []];
    }

    /**
     * Returns the URL manager configuration with persistence disabled.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function noPersistence(): array
    {
        return [...self::slug(), 'enableLanguagePersistence' => false];
    }

    /**
     * Returns the URL manager configuration with persistence and detection disabled.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function noPersistenceDetection(): array
    {
        return [...self::noDetection(), 'enableLanguagePersistence' => false];
    }

    /**
     * Returns the URL manager configuration with custom, slug, absolute and class rules plus ignore patterns.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function rules(): array
    {
        return [
            'languages' => self::ALIAS_LANGUAGES,
            'rules' => [
                ...self::SHARED_RULES,
                ['class' => UrlRule::class],
                ['pattern' => '/slash', 'route' => 'demo/slash', 'suffix' => '/'],
                ['pattern' => '/slugslash/<term:.+>/bar', 'route' => 'demo/slug-slash', 'suffix' => '/'],
            ],
            'ignoreLanguageUrlPatterns' => self::IGNORE_PATTERNS,
        ];
    }

    /**
     * Returns the URL manager configuration with the slug rule only.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function slug(): array
    {
        return [
            'languages' => self::LANGUAGES,
            'rules' => ['/foo/<term:.+>/bar' => 'demo/slug'],
        ];
    }

    /**
     * Returns the URL manager configuration with the `/` suffix, custom rules and ignore patterns.
     *
     * @phpstan-return array<string, mixed>
     */
    private static function suffix(): array
    {
        return [
            ...self::rules(),
            'suffix' => '/',
            'rules' => [
                ...self::SHARED_RULES,
                ['pattern' => '/noslash', 'route' => 'demo/noslash', 'suffix' => ''],
                ['pattern' => '/slugnoslash/<term:.+>/bar', 'route' => 'demo/slug-noslash', 'suffix' => '/'],
            ],
        ];
    }
}
