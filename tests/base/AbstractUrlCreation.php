<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests\base;

use JsonException;
use yii\base\{Exception, InvalidConfigException};
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii2\extensions\localeurls\tests\stub\UrlRule;
use yii2\extensions\localeurls\tests\TestCase;

/**
 * Base class for URL creation and parsing tests in the Yii2 LocaleUrls extension.
 *
 * Provides comprehensive tests for language-aware URL generation and route parsing, ensuring correct handling of
 * language codes, aliases, custom rules, and parameter extraction across multiple configuration scenarios.
 *
 * This class validates the LocaleUrls URL manager by simulating URL creation and parsing with different language
 * configurations, including alias mapping, suffix management, and query string preservation.
 *
 * It covers normalization, language code detection, custom and slug-based route mapping, and the consistent extraction
 * of routes and parameters for both relative and absolute URLs.
 *
 * Test coverage.
 * - Aliased language code handling and mapping to canonical language codes.
 * - Consistent behavior for both HTTP and HTTPS, and for host-based rules.
 * - Custom URL rule processing and mapping.
 * - Ignore pattern matching for URLs excluded from language processing.
 * - Language code detection and normalization in URLs (including aliases and case handling).
 * - Query string and parameter preservation during URL creation and parsing.
 * - Route and parameter extraction for both relative and absolute URLs.
 * - Slug-based route parsing and parameter extraction.
 * - Suffix management for routes with and without trailing slashes.
 *
 * @copyright Copyright (C) 2023 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
abstract class AbstractUrlCreation extends TestCase
{
    /**
     * @var array Set of test configurations to test.
     */
    public array $testConfigs = [
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'french' => 'fr', 'de'],
                'rules' => [
                    '' => 'site/index',
                    '/foo/<term:.+>/bar' => 'demo/slug',
                    '/custom/<term:.+>/bar' => 'demo/custom',
                    'http://www.example.com/foo/<term:.+>/bar' => 'demo/absolute-slug',
                    [
                        'class' => UrlRule::class,
                    ],
                    [
                        'pattern' => '/slash',
                        'route' => 'demo/slash',
                        'suffix' => '/',
                    ],
                    [
                        'pattern' => '/slugslash/<term:.+>/bar',
                        'route' => 'demo/slug-slash',
                        'suffix' => '/',
                    ],
                ],
                'ignoreLanguageUrlPatterns' => [
                    '#^.*/ignored$#' => '#not/used#',
                    '#^.*/custom$#' => '#not/used#',
                ],
            ],
            'urls' => [
                // No language code in request
                '/site/page' => [
                    '/' => ['/site/index'],
                    '/?x=y' => ['/site/index', 'x' => 'y'],
                    '/demo/action' => ['/demo/action'],
                    '/demo/action?x=y' => ['/demo/action', 'x' => 'y'],
                    '/foo/baz/bar' => ['/demo/slug', 'term' => 'baz'],
                    '/foo/baz/bar?x=y' => ['/demo/slug', 'term' => 'baz', 'x' => 'y'],
                    '/ruleclass-english' => ['/ruleclass/test'],
                    '/slash/' => ['/demo/slash'],
                    '/slash/?x=y' => ['/demo/slash', 'x' => 'y'],
                    '/slugslash/baz/bar/' => ['/demo/slug-slash', 'term' => 'baz'],
                    '/slugslash/baz/bar/?x=y' => ['/demo/slug-slash', 'term' => 'baz', 'x' => 'y'],

                    'http://localhost/' => ['/site/index'],
                    'http://localhost/?x=y' => ['/site/index', 'x' => 'y'],
                    'http://localhost/demo/action' => ['/demo/action'],
                    'http://localhost/demo/action?x=y' => ['/demo/action', 'x' => 'y'],
                    'http://localhost/foo/baz/bar' => ['/demo/slug', 'term' => 'baz'],
                    'http://localhost/foo/baz/bar?x=y' => ['/demo/slug', 'term' => 'baz', 'x' => 'y'],
                    'http://localhost/ruleclass-english' => ['/ruleclass/test'],
                    'http://localhost/slash/' => ['/demo/slash'],
                    'http://localhost/slash/?x=y' => ['/demo/slash', 'x' => 'y'],
                    'http://localhost/slugslash/baz/bar/' => ['/demo/slug-slash', 'term' => 'baz'],
                    'http://localhost/slugslash/baz/bar/?x=y' => ['/demo/slug-slash', 'term' => 'baz', 'x' => 'y'],

                    'http://www.example.com/foo/baz/bar' => ['/demo/absolute-slug', 'term' => 'baz'],
                    'http://www.example.com/foo/baz/bar?x=y' => ['/demo/absolute-slug', 'term' => 'baz', 'x' => 'y'],
                ],
                // Language code in request
                '/de/site/page' => [
                    // Request language
                    '/de' => ['/site/index'],
                    '/de?x=y' => ['/site/index', 'x' => 'y'],
                    '/de/demo/action' => ['/demo/action'],
                    '/de/demo/action?x=y' => ['/demo/action', 'x' => 'y'],
                    '/de/foo/baz/bar' => ['/demo/slug', 'term' => 'baz'],
                    '/de/foo/baz/bar?x=y' => ['/demo/slug', 'term' => 'baz', 'x' => 'y'],
                    '/de/ruleclass-deutsch' => ['/ruleclass/test', 'slugLanguage' => 'de'],
                    '/de/slash/' => ['/demo/slash'],
                    '/de/slash/?x=y' => ['/demo/slash', 'x' => 'y'],
                    '/de/slugslash/baz/bar/' => ['/demo/slug-slash', 'term' => 'baz'],
                    '/de/slugslash/baz/bar/?x=y' => ['/demo/slug-slash', 'term' => 'baz', 'x' => 'y'],

                    'http://localhost/de' => ['/site/index'],
                    'http://localhost/de?x=y' => ['/site/index', 'x' => 'y'],
                    'http://localhost/de/demo/action' => ['/demo/action'],
                    'http://localhost/de/demo/action?x=y' => ['/demo/action', 'x' => 'y'],
                    'http://localhost/de/foo/baz/bar' => ['/demo/slug', 'term' => 'baz'],
                    'http://localhost/de/foo/baz/bar?x=y' => ['/demo/slug', 'term' => 'baz', 'x' => 'y'],
                    'http://localhost/de/ruleclass-deutsch' => ['/ruleclass/test', 'slugLanguage' => 'de'],
                    'http://localhost/de/slash/' => ['/demo/slash'],
                    'http://localhost/de/slash/?x=y' => ['/demo/slash', 'x' => 'y'],
                    'http://localhost/de/slugslash/baz/bar/' => ['/demo/slug-slash', 'term' => 'baz'],
                    'http://localhost/de/slugslash/baz/bar/?x=y' => ['/demo/slug-slash', 'term' => 'baz', 'x' => 'y'],

                    'http://www.example.com/de/foo/baz/bar' => ['/demo/absolute-slug', 'term' => 'baz'],
                    'http://www.example.com/de/foo/baz/bar?x=y' => ['/demo/absolute-slug', 'term' => 'baz', 'x' => 'y'],

                    // Other language
                    '/en-us' => ['/', 'language' => 'en-US'],
                    '/en-us?x=y' => ['/site/index', 'language' => 'en-US', 'x' => 'y'],
                    '/en-us/demo/action' => ['/demo/action', 'language' => 'en-US'],
                    '/en-us/demo/action?x=y' => ['/demo/action', 'language' => 'en-US', 'x' => 'y'],
                    '/en-us/foo/baz/bar' => ['/demo/slug', 'language' => 'en-US', 'term' => 'baz'],
                    '/en-us/foo/baz/bar?x=y' => ['/demo/slug', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],
                    '/en-us/ruleclass-english' => ['/ruleclass/test', 'language' => 'en-US', 'slugLanguage' => 'en-US'],
                    '/en-us/slash/' => ['/demo/slash', 'language' => 'en-US'],
                    '/en-us/slash/?x=y' => ['/demo/slash', 'language' => 'en-US', 'x' => 'y'],
                    '/en-us/slugslash/baz/bar/' => ['/demo/slug-slash', 'language' => 'en-US', 'term' => 'baz'],
                    '/en-us/slugslash/baz/bar/?x=y' => ['/demo/slug-slash', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],

                    'http://localhost/en-us' => ['/', 'language' => 'en-US'],
                    'http://localhost/en-us?x=y' => ['/', 'language' => 'en-US', 'x' => 'y'],
                    'http://localhost/en-us/demo/action' => ['/demo/action', 'language' => 'en-US'],
                    'http://localhost/en-us/demo/action?x=y' => ['/demo/action', 'language' => 'en-US', 'x' => 'y'],
                    'http://localhost/en-us/foo/baz/bar' => ['/demo/slug', 'language' => 'en-US', 'term' => 'baz'],
                    'http://localhost/en-us/foo/baz/bar?x=y' => ['/demo/slug', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],
                    'http://localhost/en-us/ruleclass-english' => ['/ruleclass/test', 'language' => 'en-US', 'slugLanguage' => 'en-US'],
                    'http://localhost/en-us/slash/' => ['/demo/slash', 'language' => 'en-US'],
                    'http://localhost/en-us/slash/?x=y' => ['/demo/slash', 'language' => 'en-US', 'x' => 'y'],
                    'http://localhost/en-us/slugslash/baz/bar/' => ['/demo/slug-slash', 'language' => 'en-US', 'term' => 'baz'],
                    'http://localhost/en-us/slugslash/baz/bar/?x=y' => ['/demo/slug-slash', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],

                    'http://www.example.com/en-us/foo/baz/bar' => ['/demo/absolute-slug', 'language' => 'en-US', 'term' => 'baz'],
                    'http://www.example.com/en-us/foo/baz/bar?x=y' => ['/demo/absolute-slug', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],

                    // Aliased language
                    '/french' => ['/', 'language' => 'fr'],
                    '/french?x=y' => ['/site/index', 'language' => 'fr', 'x' => 'y'],
                    '/french/demo/action' => ['/demo/action', 'language' => 'fr'],
                    '/french/demo/action?x=y' => ['/demo/action', 'language' => 'fr', 'x' => 'y'],
                    '/french/foo/baz/bar' => ['/demo/slug', 'language' => 'fr', 'term' => 'baz'],
                    '/french/foo/baz/bar?x=y' => ['/demo/slug', 'language' => 'fr', 'term' => 'baz', 'x' => 'y'],
                    '/french/ruleclass-francais' => ['/ruleclass/test', 'language' => 'fr', 'slugLanguage' => 'fr'],
                    '/french/slash/' => ['/demo/slash', 'language' => 'fr'],
                    '/french/slash/?x=y' => ['/demo/slash', 'language' => 'fr', 'x' => 'y'],
                    '/french/slugslash/baz/bar/' => ['/demo/slug-slash', 'language' => 'fr', 'term' => 'baz'],
                    '/french/slugslash/baz/bar/?x=y' => ['/demo/slug-slash', 'language' => 'fr', 'term' => 'baz', 'x' => 'y'],

                    'http://localhost/french' => ['/', 'language' => 'fr'],
                    'http://localhost/french?x=y' => ['/', 'language' => 'fr', 'x' => 'y'],
                    'http://localhost/french/demo/action' => ['/demo/action', 'language' => 'fr'],
                    'http://localhost/french/demo/action?x=y' => ['/demo/action', 'language' => 'fr', 'x' => 'y'],
                    'http://localhost/french/foo/baz/bar' => ['/demo/slug', 'language' => 'fr', 'term' => 'baz'],
                    'http://localhost/french/foo/baz/bar?x=y' => ['/demo/slug', 'language' => 'fr', 'term' => 'baz', 'x' => 'y'],
                    'http://localhost/french/ruleclass-francais' => ['/ruleclass/test', 'language' => 'fr', 'slugLanguage' => 'fr'],
                    'http://localhost/french/slash/' => ['/demo/slash', 'language' => 'fr'],
                    'http://localhost/french/slash/?x=y' => ['/demo/slash', 'language' => 'fr', 'x' => 'y'],
                    'http://localhost/french/slugslash/baz/bar/' => ['/demo/slug-slash', 'language' => 'fr', 'term' => 'baz'],
                    'http://localhost/french/slugslash/baz/bar/?x=y' => ['/demo/slug-slash', 'language' => 'fr', 'term' => 'baz', 'x' => 'y'],

                    'http://www.example.com/french/foo/baz/bar' => ['/demo/absolute-slug', 'language' => 'fr', 'term' => 'baz'],
                    'http://www.example.com/french/foo/baz/bar?x=y' => ['/demo/absolute-slug', 'language' => 'fr', 'term' => 'baz', 'x' => 'y'],

                    // No language code added for ignored patterns
                    '/demo/ignored' => ['/demo/ignored'],
                    '/demo/ignored?x=y' => ['/demo/ignored', 'x' => 'y'],
                    '/custom/baz/bar' => ['/demo/custom', 'term' => 'baz'],
                    '/custom/baz/bar?x=y' => ['/demo/custom', 'term' => 'baz', 'x' => 'y'],
                    'http://localhost/demo/ignored' => ['/demo/ignored'],
                    'http://localhost/demo/ignored?x=y' => ['/demo/ignored', 'x' => 'y'],
                    'http://localhost/custom/baz/bar' => ['/demo/custom', 'term' => 'baz'],
                    'http://localhost/custom/baz/bar?x=y' => ['/demo/custom', 'term' => 'baz', 'x' => 'y'],

                    // No language
                    '/' => ['/site/index', 'language' => ''],
                    '/?x=y' => ['/site/index', 'language' => '', 'x' => 'y'],
                    '/demo/action' => ['/demo/action', 'language' => ''],
                    '/demo/action?x=y' => ['/demo/action', 'language' => '', 'x' => 'y'],
                    '/foo/baz/bar' => ['/demo/slug', 'language' => '', 'term' => 'baz'],
                    '/foo/baz/bar?x=y' => ['/demo/slug', 'language' => '', 'term' => 'baz', 'x' => 'y'],
                    '/ruleclass-english' => ['/ruleclass/test', 'language' => '', 'slugLanguage' => 'en'],
                    '/slash/' => ['/demo/slash', 'language' => ''],
                    '/slash/?x=y' => ['/demo/slash', 'language' => '', 'x' => 'y'],
                    '/slugslash/baz/bar/' => ['/demo/slug-slash', 'language' => '', 'term' => 'baz'],
                    '/slugslash/baz/bar/?x=y' => ['/demo/slug-slash', 'language' => '', 'term' => 'baz', 'x' => 'y'],

                    'http://localhost/' => ['/site/index', 'language' => ''],
                    'http://localhost/?x=y' => ['/site/index', 'language' => '', 'x' => 'y'],
                    'http://localhost/demo/action' => ['/demo/action', 'language' => ''],
                    'http://localhost/demo/action?x=y' => ['/demo/action', 'language' => '', 'x' => 'y'],
                    'http://localhost/foo/baz/bar' => ['/demo/slug', 'language' => '', 'term' => 'baz'],
                    'http://localhost/foo/baz/bar?x=y' => ['/demo/slug', 'language' => '', 'term' => 'baz', 'x' => 'y'],
                    'http://localhost/ruleclass-english' => ['/ruleclass/test', 'language' => '', 'slugLanguage' => 'en'],
                    'http://localhost/slash/' => ['/demo/slash', 'language' => ''],
                    'http://localhost/slash/?x=y' => ['/demo/slash', 'language' => '', 'x' => 'y'],
                    'http://localhost/slugslash/baz/bar/' => ['/demo/slug-slash', 'language' => '', 'term' => 'baz'],
                    'http://localhost/slugslash/baz/bar/?x=y' => ['/demo/slug-slash', 'language' => '', 'term' => 'baz', 'x' => 'y'],
                ],
            ],
        ],

        // Trailing slashes
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'french' => 'fr', 'de'],
                'suffix' => '/',
                'rules' => [
                    '' => 'site/index',
                    '/foo/<term:.+>/bar' => 'demo/slug',
                    '/custom/<term:.+>/bar' => 'demo/custom',
                    'http://www.example.com/foo/<term:.+>/bar' => 'demo/absolute-slug',
                    [
                        'pattern' => '/noslash',
                        'route' => 'demo/noslash',
                        'suffix' => '',
                    ],
                    [
                        'pattern' => '/slugnoslash/<term:.+>/bar',
                        'route' => 'demo/slug-noslash',
                        'suffix' => '/',
                    ],
                ],
                'ignoreLanguageUrlPatterns' => [
                    '#^.*/ignored$#' => '#not/used#',
                    '#^.*/custom$#' => '#not/used#',
                ],
            ],
            'urls' => [
                // No language code in request
                '/site/page/' => [
                    '/' => ['/site/index'],
                    '/?x=y' => ['/site/index', 'x' => 'y'],
                    '/demo/action/' => ['/demo/action'],
                    '/demo/action/?x=y' => ['/demo/action', 'x' => 'y'],
                    '/foo/baz/bar/' => ['/demo/slug', 'term' => 'baz'],
                    '/foo/baz/bar/?x=y' => ['/demo/slug', 'term' => 'baz', 'x' => 'y'],
                    '/noslash' => ['/demo/noslash'],
                    '/noslash?x=y' => ['/demo/noslash', 'x' => 'y'],
                    '/slugnoslash/baz/bar/' => ['/demo/slug-noslash', 'term' => 'baz'],
                    '/slugnoslash/baz/bar/?x=y' => ['/demo/slug-noslash', 'term' => 'baz', 'x' => 'y'],

                    'http://localhost/' => ['/site/index'],
                    'http://localhost/?x=y' => ['/site/index', 'x' => 'y'],
                    'http://localhost/demo/action/' => ['/demo/action'],
                    'http://localhost/demo/action/?x=y' => ['/demo/action', 'x' => 'y'],
                    'http://localhost/foo/baz/bar/' => ['/demo/slug', 'term' => 'baz'],
                    'http://localhost/foo/baz/bar/?x=y' => ['/demo/slug', 'term' => 'baz', 'x' => 'y'],
                    'http://localhost/noslash' => ['/demo/noslash'],
                    'http://localhost/noslash?x=y' => ['/demo/noslash', 'x' => 'y'],
                    'http://localhost/slugnoslash/baz/bar/' => ['/demo/slug-noslash', 'term' => 'baz'],
                    'http://localhost/slugnoslash/baz/bar/?x=y' => ['/demo/slug-noslash', 'term' => 'baz', 'x' => 'y'],

                    'http://www.example.com/foo/baz/bar/' => ['/demo/absolute-slug', 'term' => 'baz'],
                    'http://www.example.com/foo/baz/bar/?x=y' => ['/demo/absolute-slug', 'term' => 'baz', 'x' => 'y'],
                ],
                // Language code in request
                '/de/site/page/' => [
                    // Request language
                    '/de/' => ['/site/index'],
                    '/de/?x=y' => ['/site/index', 'x' => 'y'],
                    '/de/demo/action/' => ['/demo/action'],
                    '/de/demo/action/?x=y' => ['/demo/action', 'x' => 'y'],
                    '/de/foo/baz/bar/' => ['/demo/slug', 'term' => 'baz'],
                    '/de/foo/baz/bar/?x=y' => ['/demo/slug', 'term' => 'baz', 'x' => 'y'],
                    '/de/noslash' => ['/demo/noslash'],
                    '/de/noslash?x=y' => ['/demo/noslash', 'x' => 'y'],
                    '/de/slugnoslash/baz/bar/' => ['/demo/slug-noslash', 'term' => 'baz'],
                    '/de/slugnoslash/baz/bar/?x=y' => ['/demo/slug-noslash', 'term' => 'baz', 'x' => 'y'],

                    'http://localhost/de/' => ['/site/index'],
                    'http://localhost/de/?x=y' => ['/site/index', 'x' => 'y'],
                    'http://localhost/de/demo/action/' => ['/demo/action'],
                    'http://localhost/de/demo/action/?x=y' => ['/demo/action', 'x' => 'y'],
                    'http://localhost/de/foo/baz/bar/' => ['/demo/slug', 'term' => 'baz'],
                    'http://localhost/de/foo/baz/bar/?x=y' => ['/demo/slug', 'term' => 'baz', 'x' => 'y'],
                    'http://localhost/de/noslash' => ['/demo/noslash'],
                    'http://localhost/de/noslash?x=y' => ['/demo/noslash', 'x' => 'y'],
                    'http://localhost/de/slugnoslash/baz/bar/' => ['/demo/slug-noslash', 'term' => 'baz'],
                    'http://localhost/de/slugnoslash/baz/bar/?x=y' => ['/demo/slug-noslash', 'term' => 'baz', 'x' => 'y'],

                    'http://www.example.com/de/foo/baz/bar/' => ['/demo/absolute-slug', 'term' => 'baz'],
                    'http://www.example.com/de/foo/baz/bar/?x=y' => ['/demo/absolute-slug', 'term' => 'baz', 'x' => 'y'],

                    // Other language
                    '/en-us/' => ['/site/index', 'language' => 'en-US'],
                    '/en-us/?x=y' => ['/site/index', 'language' => 'en-US', 'x' => 'y'],
                    '/en-us/demo/action/' => ['/demo/action', 'language' => 'en-US'],
                    '/en-us/demo/action/?x=y' => ['/demo/action', 'language' => 'en-US', 'x' => 'y'],
                    '/en-us/foo/baz/bar/' => ['/demo/slug', 'language' => 'en-US', 'term' => 'baz'],
                    '/en-us/foo/baz/bar/?x=y' => ['/demo/slug', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],
                    '/en-us/noslash' => ['/demo/noslash', 'language' => 'en-US'],
                    '/en-us/noslash?x=y' => ['/demo/noslash', 'language' => 'en-US', 'x' => 'y'],
                    '/en-us/slugnoslash/baz/bar/' => ['/demo/slug-noslash', 'language' => 'en-US', 'term' => 'baz'],
                    '/en-us/slugnoslash/baz/bar/?x=y' => ['/demo/slug-noslash', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],

                    'http://localhost/en-us/' => ['/site/index', 'language' => 'en-US'],
                    'http://localhost/en-us/?x=y' => ['/site/index', 'language' => 'en-US', 'x' => 'y'],
                    'http://localhost/en-us/demo/action/' => ['/demo/action', 'language' => 'en-US'],
                    'http://localhost/en-us/demo/action/?x=y' => ['/demo/action', 'language' => 'en-US', 'x' => 'y'],
                    'http://localhost/en-us/foo/baz/bar/' => ['/demo/slug', 'language' => 'en-US', 'term' => 'baz'],
                    'http://localhost/en-us/foo/baz/bar/?x=y' => ['/demo/slug', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],
                    'http://localhost/en-us/noslash' => ['/demo/noslash', 'language' => 'en-US'],
                    'http://localhost/en-us/noslash?x=y' => ['/demo/noslash', 'language' => 'en-US', 'x' => 'y'],
                    'http://localhost/en-us/slugnoslash/baz/bar/' => ['/demo/slug-noslash', 'language' => 'en-US', 'term' => 'baz'],
                    'http://localhost/en-us/slugnoslash/baz/bar/?x=y' => ['/demo/slug-noslash', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],

                    'http://www.example.com/en-us/foo/baz/bar/' => ['/demo/absolute-slug', 'language' => 'en-US', 'term' => 'baz'],
                    'http://www.example.com/en-us/foo/baz/bar/?x=y' => ['/demo/absolute-slug', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],


                    // Aliased language
                    '/french/' => ['/site/index', 'language' => 'fr'],
                    '/french/?x=y' => ['/site/index', 'language' => 'fr', 'x' => 'y'],
                    '/french/demo/action/' => ['/demo/action', 'language' => 'fr'],
                    '/french/demo/action/?x=y' => ['/demo/action', 'language' => 'fr', 'x' => 'y'],
                    '/french/foo/baz/bar/' => ['/demo/slug', 'language' => 'fr', 'term' => 'baz'],
                    '/french/foo/baz/bar/?x=y' => ['/demo/slug', 'language' => 'fr', 'term' => 'baz', 'x' => 'y'],
                    '/french/noslash' => ['/demo/noslash', 'language' => 'fr'],
                    '/french/noslash?x=y' => ['/demo/noslash', 'language' => 'fr', 'x' => 'y'],
                    '/french/slugnoslash/baz/bar/' => ['/demo/slug-noslash', 'language' => 'fr', 'term' => 'baz'],
                    '/french/slugnoslash/baz/bar/?x=y' => ['/demo/slug-noslash', 'language' => 'fr', 'term' => 'baz', 'x' => 'y'],

                    'http://localhost/french/' => ['/site/index', 'language' => 'fr'],
                    'http://localhost/french/?x=y' => ['/site/index', 'language' => 'fr', 'x' => 'y'],
                    'http://localhost/french/demo/action/' => ['/demo/action', 'language' => 'fr'],
                    'http://localhost/french/demo/action/?x=y' => ['/demo/action', 'language' => 'fr', 'x' => 'y'],
                    'http://localhost/french/foo/baz/bar/' => ['/demo/slug', 'language' => 'fr', 'term' => 'baz'],
                    'http://localhost/french/foo/baz/bar/?x=y' => ['/demo/slug', 'language' => 'fr', 'term' => 'baz', 'x' => 'y'],
                    'http://localhost/french/noslash' => ['/demo/noslash', 'language' => 'fr'],
                    'http://localhost/french/noslash?x=y' => ['/demo/noslash', 'language' => 'fr', 'x' => 'y'],
                    'http://localhost/french/slugnoslash/baz/bar/' => ['/demo/slug-noslash', 'language' => 'fr', 'term' => 'baz'],
                    'http://localhost/french/slugnoslash/baz/bar/?x=y' => ['/demo/slug-noslash', 'language' => 'fr', 'term' => 'baz', 'x' => 'y'],

                    'http://www.example.com/french/foo/baz/bar/' => ['/demo/absolute-slug', 'language' => 'fr', 'term' => 'baz'],
                    'http://www.example.com/french/foo/baz/bar/?x=y' => ['/demo/absolute-slug', 'language' => 'fr', 'term' => 'baz', 'x' => 'y'],


                    // No language code added for ignored patterns
                    '/demo/ignored/' => ['/demo/ignored'],
                    '/demo/ignored/?x=y' => ['/demo/ignored', 'x' => 'y'],
                    '/custom/baz/bar/' => ['/demo/custom', 'term' => 'baz'],
                    '/custom/baz/bar/?x=y' => ['/demo/custom', 'term' => 'baz', 'x' => 'y'],
                    'http://localhost/demo/ignored/' => ['/demo/ignored'],
                    'http://localhost/demo/ignored/?x=y' => ['/demo/ignored', 'x' => 'y'],
                    'http://localhost/custom/baz/bar/' => ['/demo/custom', 'term' => 'baz'],
                    'http://localhost/custom/baz/bar/?x=y' => ['/demo/custom', 'term' => 'baz', 'x' => 'y'],


                    // No language
                    '/' => ['/site/index', 'language' => ''],
                    '/?x=y' => ['/site/index', 'language' => '', 'x' => 'y'],
                    '/demo/action/' => ['/demo/action', 'language' => ''],
                    '/demo/action/?x=y' => ['/demo/action', 'language' => '', 'x' => 'y'],
                    '/foo/baz/bar/' => ['/demo/slug', 'language' => '', 'term' => 'baz'],
                    '/foo/baz/bar/?x=y' => ['/demo/slug', 'language' => '', 'term' => 'baz', 'x' => 'y'],
                    '/noslash' => ['/demo/noslash', 'language' => ''],
                    '/noslash?x=y' => ['/demo/noslash', 'language' => '', 'x' => 'y'],
                    '/slugnoslash/baz/bar/' => ['/demo/slug-noslash', 'language' => '', 'term' => 'baz'],
                    '/slugnoslash/baz/bar/?x=y' => ['/demo/slug-noslash', 'language' => '', 'term' => 'baz', 'x' => 'y'],

                    'http://localhost/' => ['/site/index', 'language' => ''],
                    'http://localhost/?x=y' => ['/site/index', 'language' => '', 'x' => 'y'],
                    'http://localhost/demo/action/' => ['/demo/action', 'language' => ''],
                    'http://localhost/demo/action/?x=y' => ['/demo/action', 'language' => '', 'x' => 'y'],
                    'http://localhost/foo/baz/bar/' => ['/demo/slug', 'language' => '', 'term' => 'baz'],
                    'http://localhost/foo/baz/bar/?x=y' => ['/demo/slug', 'language' => '', 'term' => 'baz', 'x' => 'y'],
                    'http://localhost/noslash' => ['/demo/noslash', 'language' => ''],
                    'http://localhost/noslash?x=y' => ['/demo/noslash', 'language' => '', 'x' => 'y'],
                    'http://localhost/slugnoslash/baz/bar/' => ['/demo/slug-noslash', 'language' => '', 'term' => 'baz'],
                    'http://localhost/slugnoslash/baz/bar/?x=y' => ['/demo/slug-noslash', 'language' => '', 'term' => 'baz', 'x' => 'y'],

                    'http://www.example.com/foo/baz/bar/' => ['/demo/absolute-slug', 'language' => '', 'term' => 'baz'],
                    'http://www.example.com/foo/baz/bar/?x=y' => ['/demo/absolute-slug', 'language' => '', 'term' => 'baz', 'x' => 'y'],
                ],
            ],
        ],

        // Keep Upper case
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de'],
                'keepUppercaseLanguageCode' => true,
                'rules' => [
                    '/foo/<term:.+>/bar' => 'demo/slug',
                ],
            ],
            'urls' => [
                '/en-US/site/page' => [
                    '/en-US/demo/action' => ['/demo/action'],
                    '/en-US/demo/action?x=y' => ['/demo/action', 'x' => 'y'],
                    '/en-US/foo/baz/bar' => ['/demo/slug', 'term' => 'baz'],
                    '/en-US/foo/baz/bar?x=y' => ['/demo/slug', 'term' => 'baz', 'x' => 'y'],
                ],
                '/de/site/page' => [
                    '/en-US/demo/action' => ['/demo/action', 'language' => 'en-US'],
                    '/en-US/demo/action?x=y' => ['/demo/action', 'language' => 'en-US', 'x' => 'y'],
                    '/en-US/foo/baz/bar' => ['/demo/slug', 'language' => 'en-US', 'term' => 'baz'],
                    '/en-US/foo/baz/bar?x=y' => ['/demo/slug', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],
                ],
            ],
        ],

        // Hostname rule
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de'],
                'rules' => [
                    'http://www.example.com' => 'site/index',
                ],
            ],
            'urls' => [
                '/de/site/page' => [
                    // false forces creation as relative URL
                    'http://www.example.com/de' => [false, '/site/index'],
                ],
            ],
        ],

        // Persistence disabled
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de'],
                'enableLanguagePersistence' => false,
                'rules' => [
                    '/foo/<term:.+>/bar' => 'demo/slug',
                ],
            ],
            'urls' => [
                '/de/site/page/' => [
                    '/en' => ['/', 'language' => 'en'],
                    '/en/demo/action' => ['/demo/action', 'language' => 'en'],
                    '/en/demo/action?x=y' => ['/demo/action', 'language' => 'en', 'x' => 'y'],
                    '/en/foo/baz/bar' => ['/demo/slug', 'language' => 'en', 'term' => 'baz'],
                    '/en/foo/baz/bar?x=y' => ['/demo/slug', 'language' => 'en', 'term' => 'baz', 'x' => 'y'],
                ],
            ],
        ],
        // Detection disabled
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de'],
                'enableLanguageDetection' => false,
                'rules' => [
                    '/foo/<term:.+>/bar' => 'demo/slug',
                ],
            ],
            'urls' => [
                '/de/site/page/' => [
                    '/en' => ['/', 'language' => 'en'],
                    '/en/demo/action' => ['/demo/action', 'language' => 'en'],
                    '/en/demo/action?x=y' => ['/demo/action', 'language' => 'en', 'x' => 'y'],
                    '/en/foo/baz/bar' => ['/demo/slug', 'language' => 'en', 'term' => 'baz'],
                    '/en/foo/baz/bar?x=y' => ['/demo/slug', 'language' => 'en', 'term' => 'baz', 'x' => 'y'],
                ],
            ],
        ],
        // Persistence and detection disabled
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de'],
                'enableLanguageDetection' => false,
                'enableLanguagePersistence' => false,
                'rules' => [
                    '/foo/<term:.+>/bar' => 'demo/slug',
                ],
            ],
            'urls' => [
                '/de/site/page/' => [
                    '/' => ['/', 'language' => 'en'],
                    '/demo/action' => ['/demo/action', 'language' => 'en'],
                    '/demo/action?x=y' => ['/demo/action', 'language' => 'en', 'x' => 'y'],
                    '/foo/baz/bar' => ['/demo/slug', 'language' => 'en', 'term' => 'baz'],
                    '/foo/baz/bar?x=y' => ['/demo/slug', 'language' => 'en', 'term' => 'baz', 'x' => 'y'],
                ],
            ],
        ],


        // Locale URLs disabled
        [
            'urlManager' => [
                'enableLocaleUrls' => false,
                'languages' => ['en-US', 'en', 'de'],
                'rules' => [
                    '/foo/<term:.+>/bar' => 'demo/slug',
                ],
            ],
            'urls' => [
                '/site/page' => [
                    '/' => ['/'],
                    '/demo/action?language=de' => ['/demo/action', 'language' => 'de'],
                    '/foo/baz/bar?language=de' => ['/demo/slug', 'language' => 'de', 'term' => 'baz'],
                ],
            ],
        ],
        [
            'urlManager' => [
                'languages' => [],
                'rules' => [
                    '/foo/<term:.+>/bar' => 'demo/slug',
                ],
            ],
            'urls' => [
                '/site/page' => [
                    '/' => ['/'],
                    '/demo/action?language=de' => ['/demo/action', 'language' => 'de'],
                    '/foo/baz/bar?language=de' => ['/demo/slug', 'language' => 'de', 'term' => 'baz'],
                ],
            ],
        ],
    ];

    public function testGenerateUrlsWithMultipleLanguageConfigurations(): void
    {
        foreach ($this->testConfigs as $config) {
            $urlManager = $config['urlManager'] ?? [];

            foreach ($config['urls'] as $requestUrl => $routes) {
                $this->performUrlCreationTest($requestUrl, $urlManager, $routes);
            }
        }
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws JsonException if there is an error encoding or decodes JSON data.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    private function performUrlCreationTest(string $requestUrl, array $urlManager, array $routes): void
    {
        $this->resetEnvironment();
        $this->mockUrlLanguageManager($urlManager);

        $this->mockRequest($requestUrl);

        foreach ($routes as $url => $route) {
            if (preg_match('#^(https?)://([^/]*)(.*)#', $url, $matches)) {
                $schema = $matches[1];
                $host = $matches[2];
                $relativeUrl = $matches[3];

                if ($route[0] === false) {
                    array_shift($route);
                    $this->assertSame(
                        $schema . '://' . $host . $this->prepareUrl($relativeUrl),
                        Url::to($route),
                        'Absolute URL should be generated correctly as relative URL when forced with \'false\' ' .
                        'parameter  for route: ' . json_encode($route, JSON_THROW_ON_ERROR),
                    );
                } else {
                    $this->assertSame(
                        $schema . '://' . $host . $this->prepareUrl($relativeUrl),
                        Url::to($route, $schema),
                        "Absolute URL should be generated correctly with schema '{$schema}' for route: " .
                        json_encode($route, JSON_THROW_ON_ERROR),
                    );
                }
            } else {
                $this->assertSame(
                    $this->prepareUrl($url),
                    Url::to($route),
                    'Relative URL should be generated correctly for route: ' .
                    json_encode($route, JSON_THROW_ON_ERROR) . " with expected URL: '{$url}'",
                );
            }
        }
    }
}
