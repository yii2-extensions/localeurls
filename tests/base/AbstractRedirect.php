<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests\base;

use Yii;
use yii\base\Exception;
use yii\helpers\Url;
use yii\web\{UrlNormalizer, UrlNormalizerRedirectException};
use yii2\extensions\localeurls\tests\stub\UrlRule;
use yii2\extensions\localeurls\tests\TestCase;

use function is_array;
use function is_string;
use function ltrim;
use function print_r;
use function session_start;

/**
 * Base class for redirect tests in the Yii LocaleUrls extension.
 *
 * Provides comprehensive tests for URL redirection, ensuring correct language detection and redirect behavior across
 * multiple sources and configuration scenarios.
 *
 * This class validates the LocaleUrls redirection system by simulating requests with different language sources, such
 * as cookies, sessions, headers, and GeoIP data.
 *
 * It covers normalization, language code handling, custom URL rules, suffix management, and parameter preservation
 * during redirects.
 *
 * Test coverage.
 * - Cookie-based language detection and redirection behavior.
 * - Custom URL rule processing and redirection logic.
 * - Default language handling in URLs (with and without language codes).
 * - GeoIP-based language detection and automatic redirection.
 * - Language code case conversion (uppercase to lowercase).
 * - Language persistence configuration effects on redirection.
 * - Parameter preservation during URL redirection.
 * - Session-based language detection and redirection behavior.
 * - Suffix handling in URL normalization and redirection.
 * - URL normalization with different language code configurations.
 * - UrlNormalizerRedirectException handling and proper redirect execution.
 * - Wildcard language pattern matching and redirection.
 *
 * @copyright Copyright (C) 2023 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
abstract class AbstractRedirect extends TestCase
{
    /**
     * Set of test configurations to test.
     *
     * @phpstan-var array<
     *   array{
     *     urlLanguageManager: array<string, mixed>,
     *     redirects: array<string, array<int, array<int|string, array<string, mixed>|string|false>>|string|false>
     *   }
     * >
     */
    public array $testConfigs = [
        // No URL code for default language
        [
            'urlLanguageManager' => [
                'languages' => ['en-US', 'en', 'de', 'pt', 'at' => 'de-AT', 'alias' => 'fr', 'es-BO', 'wc-*'],
                'geoIpLanguageCountries' => [
                    'de' => ['DEU'],
                    'de-AT' => ['AUT'],
                    'fr' => ['FRA'],
                    'en' => ['USA', 'GBR'],
                ],
                'rules' => [
                    '/custom' => 'test/action',
                    '/slug/<name>' => 'test/slug',
                    [
                        'class' => UrlRule::class,
                    ],
                    [
                        'pattern' => '/slash',
                        'route' => 'test/slash',
                        'suffix' => '/',
                    ],
                ],
            ],
            'redirects' => [
                // Default language in URL
                '/en/site/page' => '/site/page',
                '/en' => '/',

                // No code in URL but params in session, cookie or headers
                '/site/page' => [
                    // Country in GeoIp server var
                    ['/de/site/page', 'server' => ['HTTP_X_GEO_COUNTRY' => 'DEU']],
                    ['/at/site/page', 'server' => ['HTTP_X_GEO_COUNTRY' => 'AUT']],
                    ['/alias/site/page', 'server' => ['HTTP_X_GEO_COUNTRY' => 'FRA']],

                    // Acceptable languages in request
                    ['/de/site/page', 'request' => ['acceptableLanguages' => ['de']]],
                    ['/at/site/page', 'request' => ['acceptableLanguages' => ['de-at', 'de']]],
                    ['/wc/site/page', 'request' => ['acceptableLanguages' => ['wc']]],
                    ['/es-bo/site/page', 'request' => ['acceptableLanguages' => ['es-BO', 'es', 'en']]],
                    ['/es-bo/site/page', 'request' => ['acceptableLanguages' => ['es-bo', 'es', 'en']]],
                    ['/wc-at/site/page', 'request' => ['acceptableLanguages' => ['wc-AT', 'de', 'en']]],
                    ['/pt/site/page', 'request' => ['acceptableLanguages' => ['pt-br']]],
                    ['/alias/site/page', 'request' => ['acceptableLanguages' => ['fr']]],

                    // Language in session
                    ['/de/site/page', 'session' => ['_language' => 'de']],
                    ['/at/site/page', 'session' => ['_language' => 'de-AT']],
                    ['/wc/site/page', 'session' => ['_language' => 'wc']],
                    ['/es-bo/site/page', 'session' => ['_language' => 'es-BO']],
                    ['/wc-at/site/page', 'session' => ['_language' => 'wc-AT']],
                    ['/pt/site/page', 'session' => ['_language' => 'pt']],
                    ['/alias/site/page', 'session' => ['_language' => 'fr']],

                    // Language in cookie
                    ['/de/site/page', 'cookie' => ['_language' => 'de']],
                    ['/at/site/page', 'cookie' => ['_language' => 'de-AT']],
                    ['/wc/site/page', 'cookie' => ['_language' => 'wc']],
                    ['/es-bo/site/page', 'cookie' => ['_language' => 'es-BO']],
                    ['/wc-at/site/page', 'cookie' => ['_language' => 'wc-AT']],
                    ['/pt/site/page', 'cookie' => ['_language' => 'pt']],
                    ['/alias/site/page', 'cookie' => ['_language' => 'fr']],

                    // Default language in GeoIp/cookie/session/header
                    [false, 'server' => ['HTTP_X_GEO_COUNTRY' => 'USA']],
                    [false, 'request' => ['acceptableLanguages' => ['en']]],
                    [false, 'session' => ['_language' => 'en']],
                    [false, 'cookie' => ['_language' => 'en']],

                    // Session precedes cookie precedes header precedes GeoIp
                    ['/de/site/page',
                        'session' => ['_language' => 'de'],
                        'cookie' => ['_language' => 'fr'],
                        'request' => ['acceptableLanguages' => ['pt']],
                        'server' => ['HTTP_X_GEO_COUNTRY' => 'USA'],
                    ],
                    ['/alias/site/page',
                        'cookie' => ['_language' => 'fr'],
                        'request' => ['acceptableLanguages' => ['pt']],
                        'server' => ['HTTP_X_GEO_COUNTRY' => 'USA'],
                    ],
                    ['/pt/site/page',
                        'request' => ['acceptableLanguages' => ['pt']],
                        'server' => ['HTTP_X_GEO_COUNTRY' => 'USA'],
                    ],
                ],

                // Code in URL different from language in session, cookie, headers, or GeoIp
                '/de/site/page' => [
                    [false, 'session' => ['_language' => 'wc']],
                    [false, 'cookie' => ['_language' => 'wc']],
                    [false, 'request' => ['acceptableLanguages' => ['en']]],
                    [false, 'server' => ['HTTP_X_GEO_COUNTRY' => 'USA']],
                ],

                // Lowercase conversion
                '/es-BO/site/page' => [
                    ['/es-bo/site/page'],
                    ['/es-bo/site/page', 'session' => ['_language' => 'de']],
                    ['/es-bo/site/page', 'cookie' => ['_language' => 'de']],
                ],
                '/wc-BB/site/page' => [
                    ['/wc-bb/site/page'],
                    ['/wc-bb/site/page', 'session' => ['_language' => 'de']],
                    ['/wc-bb/site/page', 'cookie' => ['_language' => 'de']],
                ],

                // Custom URL rule
                '/custom' => false,
                '/en/custom' => '/custom',
                '/de/custom' => false,
                '/slash/' => false,
                '/en/slash/' => '/slash/',
                '/de/slash/' => false,

                // Params
                '/en?a=b' => '/?a=b',
                '/en/site/page?a=b' => '/site/page?a=b',
                '/en/custom?a=b' => '/custom?a=b',
                '/en/slash/?a=b' => '/slash/?a=b',
                '/site/page?a=b' => [
                    ['/de/site/page?a=b', 'request' => ['acceptableLanguages' => ['de']]],
                ],
                '/slug/value' => false,
                '/en/slug/value' => '/slug/value',
                '/ruleclass-test-url' => false,
                '/en/ruleclass-test-url' => '/ruleclass-test-url',
            ],
        ],

        // URL code for default language
        [
            'urlLanguageManager' => [
                'languages' => ['en-US', 'en', 'de', 'pt', 'at' => 'de-AT', 'alias' => 'fr', 'es-BO', 'wc-*'],
                'enableDefaultLanguageUrlCode' => true,
                'geoIpLanguageCountries' => [
                    'de' => ['DEU'],
                    'de-AT' => ['AUT'],
                    'fr' => ['FRA'],
                    'en' => ['USA', 'GBR'],
                ],
                'rules' => [
                    '/custom' => 'test/action',
                    '/slug/<name>' => 'test/slug',
                    [
                        'class' => UrlRule::class,
                    ],
                    [
                        'pattern' => '/slash',
                        'route' => 'test/slash',
                        'suffix' => '/',
                    ],
                ],
            ],
            'redirects' => [
                // No code in URL
                '/' => [
                    ['/en'],    // default language
                    ['/de', 'session' => ['_language' => 'de']],
                    ['/alias', 'cookie' => ['_language' => 'fr']],
                    ['/de', 'server' => ['HTTP_X_GEO_COUNTRY' => 'DEU']],
                ],
                '/site/page' => [
                    ['/en/site/page', ],  // default language
                    ['/de/site/page', 'session' => ['_language' => 'de']],
                    ['/alias/site/page', 'cookie' => ['_language' => 'fr']],
                    ['/de/site/page', 'server' => ['HTTP_X_GEO_COUNTRY' => 'DEU']],
                ],

                // Lang requests with different language in session, cookie, headers, or GeoIp
                '/en/site/page' => [
                    [false, 'session' => ['_language' => 'wc']],
                    [false, 'cookie' => ['_language' => 'wc']],
                    [false, 'request' => ['acceptableLanguages' => ['en']]],
                    [false, 'server' => ['HTTP_X_GEO_COUNTRY' => 'USA']],
                ],
                '/de/site/page' => [
                    [false, 'session' => ['_language' => 'wc']],
                    [false, 'cookie' => ['_language' => 'wc']],
                    [false, 'request' => ['acceptableLanguages' => ['en']]],
                    [false, 'server' => ['HTTP_X_GEO_COUNTRY' => 'USA']],
                ],

                // Custom URL rule
                '/custom' => '/en/custom',
                '/en/custom' => false,
                '/de/custom' => false,
                '/slash/' => '/en/slash/',
                '/en/slash/' => false,
                '/de/slash/' => false,

                // Params
                '/?a=b' => '/en?a=b',
                '/site/page1?a=b' => '/en/site/page1?a=b',
                '/custom?a=b' => '/en/custom?a=b',
                '/slash/?a=b' => '/en/slash/?a=b',
                '/site/page2?a=b' => [
                    ['/de/site/page2?a=b', 'request' => ['acceptableLanguages' => ['de']]],
                ],
                '/slug/value' => '/en/slug/value',
                '/en/slug/value' => false,
                '/ruleclass-english' => '/en/ruleclass-english',
                '/en/ruleclass-deutsch' => '/en/ruleclass-english',
                '/en/ruleclass-english' => false,
            ],
        ],

        // Upper case language codes allowed in URL
        [
            'urlLanguageManager' => [
                'languages' => ['en-US', 'deutsch' => 'de', 'es-BO'],
                'keepUppercaseLanguageCode' => true,
            ],
            'redirects' => [
                // No code in URL
                '/site/page' => [
                    ['/en-US/site/page', 'session' => ['_language' => 'en-US']],
                    ['/en-US/site/page', 'cookie' => ['_language' => 'en-US']],
                ],
                // Upper case code in URL
                '/es-BO/site/page' => false,
            ],
        ],

        // Ignore patterns
        [
            'urlLanguageManager' => [
                'languages' => ['en-US', 'en', 'de'],
                'enableDefaultLanguageUrlCode' => true,
                'ignoreLanguageUrlPatterns' => [
                    '#not/used#' => '#^site/other#',
                ],
            ],
            'redirects' => [
                '/site/page' => '/en/site/page',
                '/site/other' => false,
            ],
        ],

        // No persistence / detection
        [
            'urlLanguageManager' => [
                'languages' => ['en-US', 'en', 'de', 'pt', 'at' => 'de-AT', 'alias' => 'fr', 'es-BO', 'wc-*'],
                'enableLanguageDetection' => false,
                'enableLanguagePersistence' => false,
            ],
            'redirects' => [
                '/' => [
                    [false],
                    [false,
                        'session' => ['_language' => 'de'],
                        'cookie' => ['_language' => 'fr'],
                        'request' => ['acceptableLanguages' => ['pt']],
                        'server' => ['HTTP_X_GEO_COUNTRY' => 'DEU'],
                    ],
                ],
                '/site/page' => [
                    [false],
                    [false,
                        'session' => ['_language' => 'de'],
                        'cookie' => ['_language' => 'fr'],
                        'request' => ['acceptableLanguages' => ['pt']],
                        'server' => ['HTTP_X_GEO_COUNTRY' => 'DEU'],
                    ],
                ],
                '/de' => [
                    [false],
                    [false,
                        'session' => ['_language' => 'en'],
                        'cookie' => ['_language' => 'fr'],
                        'request' => ['acceptableLanguages' => ['pt']],
                        'server' => ['HTTP_X_GEO_COUNTRY' => 'FRA'],
                    ],
                ],
                '/de/site/page' => [
                    [false],
                    [false,
                        'session' => ['_language' => 'en'],
                        'cookie' => ['_language' => 'fr'],
                        'request' => ['acceptableLanguages' => ['pt']],
                        'server' => ['HTTP_X_GEO_COUNTRY' => 'FRA'],
                    ],
                ],
                '/en' => '/',
                '/en/site/page' => '/site/page',
            ],
        ],

        // Suffix in UrlLanguageManager, with + w/o URL code for default language
        [
            'urlLanguageManager' => [
                'languages' => ['en-US', 'en', 'de'],
                'suffix' => '/',
                'rules' => [
                    '/custom' => 'test/action',
                    '/slug/<name>' => 'test/slug',
                    [
                        'pattern' => '/noslash',
                        'route' => 'test/slash',
                        'suffix' => '',
                    ],
                ],
            ],
            'redirects' => [
                '/en' => '/',
                '/en/site/page/' => '/site/page/',
                '/site/page/' => false,
                '/de/site/page/' => false,

                // Custom URL rule
                '/custom/' => false,
                '/en/custom/' => '/custom/',
                '/de/custom/' => false,
                '/noslash' => false,
                '/en/noslash' => '/noslash',
                '/de/noslash' => false,

                // Params
                '/en?a=b' => '/?a=b',
                '/en/site/page/?a=b' => '/site/page/?a=b',
                '/en/custom/?a=b' => '/custom/?a=b',
                '/en/noslash?a=b' => '/noslash?a=b',
                '/site/page/?a=b' => [
                    ['/de/site/page/?a=b', 'request' => ['acceptableLanguages' => ['de']]],
                ],
                '/slug/value/' => false,
                '/en/slug/value/' => '/slug/value/',
            ],
        ],
        [
            'urlLanguageManager' => [
                'languages' => ['en-US', 'en', 'de', 'pt', 'at' => 'de-AT', 'alias' => 'fr', 'es-BO', 'wc-*'],
                'geoIpLanguageCountries' => [
                    'de' => ['DEU'],
                    'de-AT' => ['AUT'],
                    'fr' => ['FRA'],
                    'en' => ['USA', 'GBR'],
                ],
                'enableDefaultLanguageUrlCode' => true,
                'suffix' => '/',
                'rules' => [
                    '/custom' => 'test/action',
                    '/slug/<name>' => 'test/slug',
                    [
                        'pattern' => '/noslash',
                        'route' => 'test/slash',
                        'suffix' => '',
                    ],
                ],
            ],
            'redirects' => [
                '/' => [
                    ['/en/'],    // default language
                    ['/de/', 'session' => ['_language' => 'de']],
                    ['/alias/', 'cookie' => ['_language' => 'fr']],
                    ['/de/', 'server' => ['HTTP_X_GEO_COUNTRY' => 'DEU']],
                ],
                '/site/page/' => [
                    ['/en/site/page/'],  // default language
                    ['/de/site/page/', 'session' => ['_language' => 'de']],
                    ['/alias/site/page/', 'cookie' => ['_language' => 'fr']],
                    ['/de/site/page/', 'server' => ['HTTP_X_GEO_COUNTRY' => 'DEU']],
                ],
                '/en/site/page/' => [
                    [false],
                    [false, 'session' => ['_language' => 'de']],
                    [false, 'cookie' => ['_language' => 'fr']],
                    [false, 'server' => ['HTTP_X_GEO_COUNTRY' => 'FRA']],
                ],
                '/pt/site/page/' => [
                    [false],
                    [false, 'session' => ['_language' => 'de']],
                    [false, 'cookie' => ['_language' => 'fr']],
                    [false, 'server' => ['HTTP_X_GEO_COUNTRY' => 'FRA']],
                ],

                // Custom URL rule
                '/custom/' => '/en/custom/',
                '/en/custom/' => false,
                '/de/custom/' => false,
                '/noslash' => '/en/noslash',
                '/en/noslash' => false,
                '/de/noslash' => false,

                // Params
                '/?a=b' => '/en/?a=b',
                '/site/page1/?a=b' => '/en/site/page1/?a=b',
                '/custom/?a=b' => '/en/custom/?a=b',
                '/noslash?a=b' => '/en/noslash?a=b',
                '/site/page2/?a=b' => [
                    ['/de/site/page2/?a=b', 'request' => ['acceptableLanguages' => ['de']]],
                ],
                '/slug/value/' => '/en/slug/value/',
                '/en/slug/value/' => false,
            ],
        ],

        // Normalizer with + w/o suffix, no URL code for default language
        [
            'urlLanguageManager' => [
                'languages' => ['en-US', 'en', 'de'],
                'suffix' => '/',
                'normalizer' => [
                    'class' => UrlNormalizer::class,
                ],
                'rules' => [
                    '/custom' => 'test/action',
                    '/slug/<name>' => 'test/slug',
                    [
                        'pattern' => '/noslash',
                        'route' => 'test/slash',
                        'suffix' => '',
                    ],
                ],
            ],
            'redirects' => [
                '' => '',
                '/site/page' => '/site/page/',
                '/site/page/' => false,

                '/de' => '/de/',    // normalizer
                '/de/' => false,

                '/de/site/login' => '/de/site/login/',  // normalizer
                '/de/site/login/' => false,

                '/en/site/login' => '/site/login/',     // normalizer
                '/en/site/login/' => '/site/login/',    // localeurls

                // Custom URL rule
                '/custom' => '/custom/',
                '/custom/' => false,
                '/en/custom' => '/custom/',
                '/en/custom/' => '/custom/',
                '/de/custom' => '/de/custom/',
                '/de/custom/' => false,
                '/noslash' => false,
                '/noslash/' => '/noslash',
                '/en/noslash' => '/noslash',
                '/en/noslash/' => '/noslash',
                '/de/noslash' => false,
                '/de/noslash/' => '/de/noslash',

                // Params
                '/site/page?a=b' => '/site/page/?a=b',
                '/de?a=b' => '/de/?a=b',
                '/de/site/login?a=b' => '/de/site/login/?a=b',
                '/en/site/login?a=b' => '/site/login/?a=b',
                '/en/site/login/?a=b' => '/site/login/?a=b',
                '/custom?a=b' => '/custom/?a=b',
                '/en/custom?a=b' => '/custom/?a=b',
                '/en/custom/?a=b' => '/custom/?a=b',
                '/de/custom?a=b' => '/de/custom/?a=b',
                '/noslash/?a=b' => '/noslash?a=b',
                '/en/noslash?a=b' => '/noslash?a=b',
                '/en/noslash/?a=b' => '/noslash?a=b',
                '/de/noslash/?a=b' => '/de/noslash?a=b',
                '/slug/value' => '/slug/value/',
                '/en/slug/value' => '/slug/value/',
                '/de/slug/value' => '/de/slug/value/',
            ],
        ],
        [
            'urlLanguageManager' => [
                'languages' => ['en-US', 'en', 'de'],
                'normalizer' => [
                    'class' => UrlNormalizer::class,
                ],
                'rules' => [
                    '/custom' => 'test/action',
                    '/slug/<name>' => 'test/slug',
                    [
                        'pattern' => '/slash',
                        'route' => 'test/slash',
                        'suffix' => '/',
                    ],
                ],
            ],
            'redirects' => [
                '' => '',
                '/site/page/' => '/site/page',
                '/site/page' => false,

                '/de/' => '/de',    // normalizer
                '/de' => false,

                '/de/site/login/' => '/de/site/login',  // normalizer
                '/de/site/login' => false,

                '/en/site/login/' => '/site/login',     // normalizer
                '/en/site/login' => '/site/login',      // localeurls

                // Custom URL rule
                '/custom' => false,
                '/custom/' => '/custom',
                '/en/custom' => '/custom',
                '/en/custom/' => '/custom',
                '/de/custom' => false,
                '/de/custom/' => '/de/custom',
                '/slash' => '/slash/',
                '/slash/' => false,
                '/en/slash' => '/slash/',
                '/en/slash/' => '/slash/',
                '/de/slash' => '/de/slash/',
                '/de/slash/' => false,

                // Params
                '/site/page/?a=b' => '/site/page?a=b',
                '/de/?a=b' => '/de?a=b',    // normalizer
                '/de/site/login/?a=b' => '/de/site/login?a=b',  // normalizer
                '/en/site/login/?a=b' => '/site/login?a=b',     // normalizer
                '/en/site/login?a=b' => '/site/login?a=b',      // localeurls
                '/custom/?a=b' => '/custom?a=b',
                '/en/custom?a=b' => '/custom?a=b',
                '/en/custom/?a=b' => '/custom?a=b',
                '/de/custom/?a=b' => '/de/custom?a=b',
                '/slash?a=b' => '/slash/?a=b',
                '/en/slash?a=b' => '/slash/?a=b',
                '/en/slash/?a=b' => '/slash/?a=b',
                '/de/slash?a=b' => '/de/slash/?a=b',
                '/slug/value/' => '/slug/value',
                '/en/slug/value/' => '/slug/value',
                '/de/slug/value/' => '/de/slug/value',
            ],
        ],

        // Normalizer with + w/o suffix, with URL code for default language
        [
            'urlLanguageManager' => [
                'languages' => ['en-US', 'en', 'de', 'pt', 'at' => 'de-AT', 'alias' => 'fr', 'es-BO', 'wc-*'],
                'geoIpLanguageCountries' => [
                    'de' => ['DEU'],
                    'de-AT' => ['AUT'],
                    'fr' => ['FRA'],
                    'en' => ['USA', 'GBR'],
                ],
                'enableDefaultLanguageUrlCode' => true,
                'suffix' => '/',
                'normalizer' => [
                    'class' => UrlNormalizer::class,
                ],
                'rules' => [
                    '/custom' => 'test/action',
                    '/slug/<name>' => 'test/slug',
                    [
                        'pattern' => '/noslash',
                        'route' => 'test/slash',
                        'suffix' => '',
                    ],
                ],
            ],
            'redirects' => [
                '' => [
                    ['/en/'],    // default language
                    ['/de/', 'session' => ['_language' => 'de']],
                    ['/alias/', 'cookie' => ['_language' => 'fr']],
                    ['/de/', 'server' => ['HTTP_X_GEO_COUNTRY' => 'DEU']],
                ],
                '/site/page' => [
                    ['/en/site/page/'],  // default language
                    ['/de/site/page/', 'session' => ['_language' => 'de']],
                    ['/alias/site/page/', 'cookie' => ['_language' => 'fr']],
                    ['/de/site/page/', 'server' => ['HTTP_X_GEO_COUNTRY' => 'DEU']],
                ],
                '/en/site/page' => '/en/site/page/',

                // Custom URL rule
                '/custom' => '/en/custom/',
                '/custom/' => '/en/custom/',
                '/en/custom' => '/en/custom/',
                '/en/custom/' => false,
                '/de/custom' => '/de/custom/',
                '/de/custom/' => false,
                '/noslash' => '/en/noslash',
                '/noslash/' => '/en/noslash',
                '/en/noslash' => false,
                '/en/noslash/' => '/en/noslash',
                '/de/noslash' => false,
                '/de/noslash/' => '/de/noslash',

                // Params
                '?a=b' => '/en/?a=b',
                '/site/page?a=b' => '/en/site/page/?a=b',
                '/custom?a=b' => '/en/custom/?a=b',
                '/custom/?a=b' => '/en/custom/?a=b',
                '/en/custom?a=b' => '/en/custom/?a=b',
                '/de/custom?a=b' => '/de/custom/?a=b',
                '/noslash?a=b' => '/en/noslash?a=b',
                '/noslash/?a=b' => '/en/noslash?a=b',
                '/en/noslash/?a=b' => '/en/noslash?a=b',
                '/de/noslash/?a=b' => '/de/noslash?a=b',
                '/slug/value' => '/en/slug/value/',
                '/en/slug/value' => '/en/slug/value/',
                '/de/slug/value' => '/de/slug/value/',
            ],
        ],
        [
            'urlLanguageManager' => [
                'languages' => ['en-US', 'en', 'de', 'pt', 'at' => 'de-AT', 'alias' => 'fr', 'es-BO', 'wc-*'],
                'geoIpLanguageCountries' => [
                    'de' => ['DEU'],
                    'de-AT' => ['AUT'],
                    'fr' => ['FRA'],
                    'en' => ['USA', 'GBR'],
                ],
                'enableDefaultLanguageUrlCode' => true,
                'normalizer' => [
                    'class' => UrlNormalizer::class,
                ],
                'rules' => [
                    '/custom' => 'test/action',
                    '/slug/<name>' => 'test/slug',
                    [
                        'pattern' => '/slash',
                        'route' => 'test/slash',
                        'suffix' => '/',
                    ],
                ],
            ],
            'redirects' => [
                '/' => [
                    ['/en'],    // default language
                    ['/de', 'session' => ['_language' => 'de']],
                    ['/alias', 'cookie' => ['_language' => 'fr']],
                    ['/de', 'server' => ['HTTP_X_GEO_COUNTRY' => 'DEU']],
                ],
                '/site/page/' => [
                    ['/en/site/page'],  // default language
                    ['/de/site/page', 'session' => ['_language' => 'de']],
                    ['/alias/site/page', 'cookie' => ['_language' => 'fr']],
                    ['/de/site/page', 'server' => ['HTTP_X_GEO_COUNTRY' => 'DEU']],
                ],
                '/en/site/page/' => '/en/site/page',

                // Custom URL rule
                '/custom' => '/en/custom',
                '/custom/' => '/en/custom',
                '/en/custom' => false,
                '/en/custom/' => '/en/custom',
                '/de/custom' => false,
                '/de/custom/' => '/de/custom',
                '/slash' => '/en/slash/',
                '/slash/' => '/en/slash/',
                '/en/slash' => '/en/slash/',
                '/en/slash/' => false,
                '/de/slash' => '/de/slash/',
                '/de/slash/' => false,

                // Params
                '/?a=b' => '/en?a=b',
                '/site/page/?a=b' => '/en/site/page?a=b',
                '/en/site/page/?a=b' => '/en/site/page?a=b',
                '/custom?a=b' => '/en/custom?a=b',
                '/custom/?a=b' => '/en/custom?a=b',
                '/de/custom/?a=b' => '/de/custom?a=b',
                '/slash?a=b' => '/en/slash/?a=b',
                '/slash/?a=b' => '/en/slash/?a=b',
                '/en/slash?a=b' => '/en/slash/?a=b',
                '/de/slash?a=b' => '/de/slash/?a=b',
                '/slug/value/' => '/en/slug/value',
                '/en/slug/value/' => '/en/slug/value',
                '/de/slug/value/' => '/de/slug/value',
            ],
        ],
    ];

    public function testRedirectUrlsWithMultipleConfigurations(): void
    {
        foreach ($this->testConfigs as $config) {
            $urlLanguageManager = $config['urlLanguageManager'];

            foreach ($config['redirects'] as $from => $to) {
                if (is_array($to)) {
                    foreach ($to as $params) {
                        $url = $params[0] ?? '';
                        $request = $params['request'] ?? [];
                        $session = $params['session'] ?? [];
                        $cookie = $params['cookie'] ?? [];
                        $server = $params['server'] ?? [];

                        $this->performRedirectTest(
                            $from,
                            $url,
                            $urlLanguageManager,
                            $request,
                            $session,
                            $cookie,
                            $server,
                        );
                    }
                } else {
                    $this->performRedirectTest($from, $to, $urlLanguageManager);
                }
            }
        }
    }

    /**
     * @phpstan-param array<string, mixed>|false|null|string $to
     * @phpstan-param array<string, mixed>|string|false $urlLanguageManager
     * @phpstan-param array<string, mixed>|string|false $request
     * @phpstan-param array<string, mixed>|string|false $session
     * @phpstan-param array<string, mixed>|string|false $cookie
     * @phpstan-param array<string, mixed>|string|false $server
     */
    private function performRedirectTest(
        string $from,
        array|false|null|string $to,
        array|string|false $urlLanguageManager,
        array|string|false $request = [],
        array|string|false $session = [],
        array|string|false $cookie = [],
        array|string|false $server = [],
    ): void {
        $this->resetEnvironment();
        $this->mockUrlLanguageManager($urlLanguageManager);

        if ($session !== []) {
            @session_start();

            $_SESSION = $session;
        }

        if ($cookie !== []) {
            $_COOKIE = $cookie;
        }

        if (is_array($server)) {
            foreach ($server as $key => $value) {
                $_SERVER[$key] = $value;
            }
        }

        $configMessage = print_r(
            [
                'from' => $from,
                'to' => $to,
                'urlManager' => $urlLanguageManager,
                'request' => $request,
                'session' => $session,
                'cookie' => $cookie,
                'server' => $server,
            ],
            true,
        );

        try {
            $this->mockRequest($from, $request);
        } catch (UrlNormalizerRedirectException $e) {
            $url = $e->url;

            if (is_array($url)) {
                if (isset($url[0]) && is_string($url[0])) {
                    // ensure the route is absolute
                    $url[0] = '/' . ltrim($url[0], '/');
                }

                $url += Yii::$app->request->getQueryParams();
            }

            if (is_string($to)) {
                self::assertSame(
                    $this->prepareUrl($to),
                    Url::to($url, $e->scheme),
                    'UrlNormalizerRedirectException redirect URL should match expected URL. Configuration: ' .
                     $configMessage,
                );
            }
        } catch (Exception $e) {
            if ($to === false || $to === null) {
                self::fail(
                    "Expected redirect from '$from' to '$to' but no redirect occurred. Configuration: $configMessage",
                );
            }

            if (is_array($to) === false) {
                self::assertSame(
                    $this->prepareUrl($to),
                    $e->getMessage(),
                    "Exception redirect URL should match expected URL. Configuration: {$configMessage}",
                );
            }
        }
    }
}
