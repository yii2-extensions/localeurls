<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls;

use Yii;
use yii\base\{Exception, ExitException, InvalidConfigException, InvalidRouteException};
use yii\helpers\Url;
use yii\web\{Cookie, NotFoundHttpException, Request, UrlManager, UrlNormalizerRedirectException};

use function array_map;
use function array_search;
use function array_shift;
use function array_unshift;
use function count;
use function explode;
use function in_array;
use function is_array;
use function is_string;
use function mb_strlen;
use function mb_substr;
use function preg_match;
use function rtrim;
use function str_contains;
use function str_ends_with;
use function strlen;
use function strncmp;
use function strpos;
use function strtoupper;
use function substr;
use function substr_replace;
use function usort;

/**
 * URL manager with transparent language detection, persistence, and locale-aware URL generation.
 *
 * Extends Yii's {@see UrlManager} to provide automatic language detection from the URL, browser settings, session,
 * or GeoIP, and ensures the language is consistently reflected in all generated URLs.
 *
 * This enables seamless multilingual support for web applications, allowing users to interact in their preferred
 * language while maintaining clean and predictable URLs.
 *
 * The manager can persist the detected language in the user session and/or a cookie, and supports redirecting users
 * to the correct language-prefixed URL as needed.
 *
 * It also provides flexible configuration for language code formats, default language handling, and exclusion patterns
 * for routes or URLs that shouldn't be processed for localization.
 *
 * Key features:
 * - Automatic detection of language from URL, browser headers, session, cookie, or GeoIP.
 * - Configurable language code handling (case, default language prefix, aliases, wildcards).
 * - Event support for language change notifications.
 * - Exclusion patterns for routes/URLs to skip language processing.
 * - Integration with Yii's pretty URLs and URL normalization.
 * - Locale-aware URL creation with language parameter injection.
 * - Redirects to canonical URLs based on language and configuration.
 * - Transparent persistence of language selection in session and cookie.
 *
 * Usage example:
 * ```php
 * $manager = new UrlLanguageManager(
 *     [
 *         'languages' => ['en', 'fr', 'de', 'ru' => 'ru'],
 *         'enableLocaleUrls' => true,
 *         'enableLanguageDetection' => true,
 *         'enableLanguagePersistence' => true,
 *     ],
 * );
 *
 * // Generates /fr/site/index for French
 * $url = $manager->createUrl(['site/index', 'language' => 'fr']);
 * ```
 *
 * @see LanguageChangedEvent for event details when language changes.
 * @see UrlManager for base URL management and routing.
 *
 * @copyright Copyright (C) 2015-2025 Carsten Brandt, contributors.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
class UrlLanguageManager extends UrlManager
{
    public const EVENT_LANGUAGE_CHANGED = 'languageChanged';

    /**
     * @var array List of available language codes. More specific patterns should come first, for example, `en_us`
     * before `en`.
     *
     * This can also contain mapping of `<url_value> => <language>`, (for example, `'english' => 'en'`).
     *
     * @phpstan-var array<array-key,string>
     */
    public array $languages = [];

    /**
     * @var bool Whether to enable locale URL specific features.
     *
     * Default is `true`, which enables locale URLs, language detection, and language persistence.
     */
    public bool $enableLocaleUrls = true;

    /**
     * @var bool Whether the default language should use a URL code like any other configured language.
     *
     * By default, this is `false`, so for URLs without a language code, the default language is assumed.
     *
     * In addition, any request to a URL that contains the default language code will be redirected to the same URL
     * without a language code. So if the default language is `fr` and a user requests `/fr/some/page` he gets
     * redirected to `/some/page`. This way the persistent language can be reset to the default language.
     *
     * If this is `true`, then a URL that doesn't contain any language code will be redirected to the same URL with
     * default language code. So if, for example, the default language is `fr`, then any request to `/some/page` will be
     * redirected to `/fr/some/page`.
     *
     * Default is `false`.
     */
    public bool $enableDefaultLanguageUrlCode = false;

    /**
     * @var bool Whether to detect the app language from the HTTP headers (that is browser settings).
     *
     * Default is `true`, which means that the language will be detected from the `Accept-Language` header.
     */
    public bool $enableLanguageDetection = true;

    /**
     * @var bool Whether to store the detected language in session and (optionally) a cookie.
     *
     * If this is `true` (default) and a returning user tries to access any URL without a language prefix, they will be
     * redirected to the respective stored language URL (for example, `/some/page -> /fr/some/page`).
     *
     * Default is `true`.
     */
    public bool $enableLanguagePersistence = true;

    /**
     * @var bool Whether to keep upper case language codes in URL. Default is `false` which will, for example, redirect
     * de-AT` to `de-at`.
     *
     * Default is `false`.
     */
    public bool $keepUppercaseLanguageCode = false;

    /**
     * @var bool|string Name of the session key that is used to store the language. If `false` no session is used.
     *
     * Default is '_language'.
     */
    public string|bool $languageSessionKey = '_language';

    /**
     * @var string the name of the language cookie.
     *
     * Default is '_language'.
     */
    public string $languageCookieName = '_language';

    /**
     * @var int Number of seconds how long the language information should be stored in cookie.
     * - If  {@see LocaleUrls::enableLanguagePersistence} is `true`.
     * - Set to `false` to disable the language cookie completely.
     *
     * Default is `30` days.
     */
    public int $languageCookieDuration = 2592000;

    /**
     * @var array Configuration options for the language cookie.
     *
     * @see LocaleUrls::languageCookieName will override.
     * @see LocaleUrls::languageCookieDuration will override
     *
     * @phpstan-var array<string,bool|int|string>
     */
    public array $languageCookieOptions = [];

    /**
     * @var array List of route and URL regex patterns to ignore during language processing.
     *
     * The keys of the array are patterns for routes, the values are patterns for URLs.
     *
     * Route patterns are checked during URL creation.
     * - If a pattern matches, no language parameter will be added to the created URL.
     *
     * URL patterns are checked during processing incoming requests.
     * - If a pattern matches, the language processing will be skipped for that URL.
     *
     * Usage example:
     *
     * ```php
     * [
     *     '#^site/(login|register)#' => '#^(login|register)#'
     *     '#^api/#' => '#^api/#',
     * ]
     * ```
     *
     * @phpstan-var array<string,string>
     */
    public array $ignoreLanguageUrlPatterns = [];

    public $enablePrettyUrl = true;

    /**
     * @var string If a parameter with this name is passed to any {@see LocaleUrls::createUrl} Method, the created URL
     * will use the language specified there.
     *
     * URLs created this way can be used to switch to a different language.
     *
     * If no such parameter is used, the currently detected application language is used.
     *
     * Default is 'language'.
     */
    public string $languageParam = 'language';

    /**
     * @var string Key in that contains the detected GeoIP country.
     *
     * Default is 'HTTP_X_GEO_COUNTRY' as used by mod_geoip in apache.
     */
    public string $geoIpServerVar = 'HTTP_X_GEO_COUNTRY';

    /**
     * @var array List of GeoIP countries indexed by corresponding language code.
     *
     * The default is an empty list which disables GeoIP detection.
     *
     * Usage example:
     *
     * ```php
     * [
     *     // Set app language to 'ru' for these GeoIp countries
     *     'ru' => ['RUS','AZE','ARM','BLR','KAZ','KGZ','MDA','TJK','TKM','UZB','UKR']
     * ]
     * ```
     *
     * @phpstan-var array<string,array<int, string>>
     */
    public array $geoIpLanguageCountries = [];

    /**
     * @var int HTTP status code. Default is 302.
     */
    public int $languageRedirectCode = 302;

    /**
     * @var string Language that was initially set in the application configuration.
     */
    protected string $_defaultLanguage = '';

    /**
     * @var Request|null Request object that is currently being processed.
     */
    protected Request|null $_request = null;

    /**
     * @var bool Whether locale URL was processed.
     */
    protected bool $_processed = false;

    /**
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     */
    public function init(): void
    {
        if ($this->enableLocaleUrls && $this->languages !== [] && $this->enablePrettyUrl === false) {
            throw new InvalidConfigException('Locale URL support requires enablePrettyUrl to be set to true.');
        }

        $this->_defaultLanguage = Yii::$app->language;

        parent::init();
    }

    /**
     * Returns the application's default language as set in the initial configuration.
     *
     * Usage example:
     * ```php
     * $default = $manager->getDefaultLanguage();
     *
     * if ($language === $default) {
     *     // your code here
     * }
     * ```
     *
     * @return string Language code initially set in the application configuration (for example, 'en', 'fr').
     */
    public function getDefaultLanguage(): string
    {
        return $this->_defaultLanguage;
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws ExitException if execution should be halted without exiting the process.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     *
     * @return array|bool Parsed request parameters or `false` if parsing failed.
     *
     * @phpstan-return array<string>|bool
     */
    public function parseRequest($request): array|bool
    {
        if ($this->enableLocaleUrls && $this->languages !== []) {
            $this->_request = $request;
            $process = true;

            if ($this->ignoreLanguageUrlPatterns !== []) {
                $pathInfo = $request->getPathInfo();

                foreach ($this->ignoreLanguageUrlPatterns as $k => $pattern) {
                    if (preg_match($pattern, $pathInfo) !== 0) {
                        $message = "Ignore pattern '{$pattern}' matches '{$pathInfo}.' Skipping language processing.";

                        Yii::debug($message, __METHOD__);

                        $process = false;
                    }
                }
            }

            if ($process && $this->_processed === false) {
                // Check if a normalizer wants to redirect
                $normalized = false;

                if ($this->normalizer !== false) {
                    try {
                        parent::parseRequest($request);
                    } catch (UrlNormalizerRedirectException) {
                        $normalized = true;
                    }
                }

                $this->_processed = true;

                $this->processLocaleUrl($normalized);
            }
        }

        /** @phpstan-var array<string>|bool $result */
        $result = parent::parseRequest($request);

        return $result;
    }

    /**
     * @param array|string $params Parameters to be used for URL creation.
     *
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     *
     * @return string the created URL with appropriate language handling applied.
     *
     * @phpstan-param array<string,string>|string $params
     *
     * @phpstan-ignore method.childParameterType
     */
    public function createUrl($params): string
    {
        if ($this->ignoreLanguageUrlPatterns !== []) {
            $params = is_string($params) ? [$params] : $params;

            if (isset($params[0]) === false) {
                return parent::createUrl($params);
            }

            $route = trim($params[0], '/');

            foreach ($this->ignoreLanguageUrlPatterns as $pattern => $v) {
                if (preg_match($pattern, $route) !== 0) {
                    return parent::createUrl($params);
                }
            }
        }

        if ($this->enableLocaleUrls && $this->languages !== []) {
            $params = is_string($params) ? [$params] : $params;
            $isLanguageGiven = isset($params[$this->languageParam]);
            $language = $params[$this->languageParam] ?? Yii::$app->language;
            $isDefaultLanguage = $language === $this->getDefaultLanguage();

            if ($isLanguageGiven) {
                unset($params[$this->languageParam]);
            }

            $url = parent::createUrl($params);

            if (
                // Only add language if it is not empty and ...
                $language !== '' && (
                    // ... it is not the default language or default language uses URL code ...
                    $isDefaultLanguage === false || $this->enableDefaultLanguageUrlCode ||

                    /**
                     * ... or if a language is explicitly given, but only if either persistence or detection is enabled.
                     * This way, a "reset URL" can be created for the default language.
                     */
                    ($isLanguageGiven && ($this->enableLanguagePersistence || $this->enableLanguageDetection))
                )
            ) {
                $key = array_search($language, $this->languages, true);

                if (is_string($key)) {
                    $language = $key;
                }

                if ($this->keepUppercaseLanguageCode === false) {
                    $language = strtolower($language);
                }

                /**
                 * Calculate the position where the language code has to be inserted depending on the showScriptName and
                 * baseUrl configuration:
                 * - /foo/bar -> /de/foo/bar
                 * - /base/foo/bar -> /base/de/foo/bar
                 * - /index.php/foo/bar -> /index.php/de/foo/bar
                 * - /base/index.php/foo/bar -> /base/index.php/de/foo/bar
                 */
                $prefix = $this->showScriptName ? $this->getScriptUrl() : $this->getBaseUrl();
                $insertPos = strlen($prefix);

                // Remove any trailing slashes for root URLs
                if ($this->suffix !== '/') {
                    if (count($params) === 1) {
                        /**
                         * / -> ''
                         * /base/ -> /base
                         * /index.php/ -> /index.php
                         * /base/index.php/ -> /base/index.php
                         */
                        if ($url === $prefix . '/') {
                            $url = rtrim($url, '/');
                        }
                    } elseif (strncmp($url, $prefix . '/?', $insertPos + 2) === 0) {
                        /**
                         * /?x=y -> ?x=y
                         * /base/?x=y -> /base?x=y
                         * /index.php/?x=y -> /index.php?x=y
                         * /base/index.php/?x=y -> /base/index.php?x=y
                         */
                        $url = substr_replace($url, '', $insertPos, 1);
                    }
                }

                /**
                 * If we have an absolute URL, the length of the host URL has to be added:
                 * - http://www.example.com
                 * - http://www.example.com?x=y
                 * - http://www.example.com/foo/bar
                 */
                if (str_contains($url, '://')) {
                    // Host URL ends at first '/' or '?' after the schema
                    if (($pos = strpos($url, '/', 8)) !== false || ($pos = strpos($url, '?', 8)) !== false) {
                        $insertPos += $pos;
                    } else {
                        $insertPos += strlen($url);
                    }
                }

                if ($insertPos > 0) {
                    return substr_replace($url, '/' . $language, $insertPos, 0);
                }

                return '/' . $language . $url;
            }

            return $url;
        }

        return parent::createUrl($params);
    }

    /**
     * Processes the current request to extract and apply a language or locale code from the URL, session, cookie, or
     * browser settings.
     *
     * This method checks the incoming URL of a language or locale prefix and updates the application's language
     * accordingly.
     * - If no language is found in the URL, it attempts to load a persisted language from session or cookie, or detect
     *   it from browser headers or GeoIP.
     * - If a valid language is found, it is set as the application language and optionally persisted.
     *
     * The method also handles redirects to canonical URLs based on language configuration, ensuring consistent and
     * SEO-friendly.
     *
     * @param bool $normalized Whether a UrlNormalizer attempted a redirect for this request.
     *
     * @throws Exception if an unexpected error occurs during execution.
     * @throws ExitException if execution should be halted without exiting the process.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    protected function processLocaleUrl(bool $normalized): void
    {
        $pathInfo = $this->_request?->getPathInfo();
        $parts = [];

        foreach ($this->languages as $k => $v) {
            $value = is_string($k) ? $k : $v;

            if (str_ends_with($value, '-*')) {
                // @infection-ignore-all
                $lng = substr($value, 0, -2);
                $parts[] = "{$lng}\-[a-z]{2,3}";
                $parts[] = $lng;
            } else {
                $parts[] = $value;
            }
        }

        // order by length to make longer patterns match before short patterns, for example, put "en-GB" before "en"
        usort($parts, static fn($a, $b): int => mb_strlen($b) <=> mb_strlen($a));

        $pattern = implode('|', $parts);

        if ($pathInfo !== null && preg_match("#^($pattern)\b(/?)#i", $pathInfo, $m) !== 0) {
            $this->_request?->setPathInfo(mb_substr($pathInfo, mb_strlen(($m[1] ?? '') . ($m[2] ?? ''))));
            $code = $m[1] ?? '';

            if (isset($this->languages[$code])) {
                // Replace alias with language code
                $language = $this->languages[$code];
            } else {
                // lowercase language, uppercase country
                [$language, $country] = $this->matchCode($code);

                if ($country !== null) {
                    if ($code === "$language-$country" && $this->keepUppercaseLanguageCode === false) {
                        $this->redirectToLanguage($code);
                    } else {
                        $language = "$language-$country";
                    }
                }

                if ($language === null) {
                    $language = $code;
                }
            }

            Yii::$app->language = $language;
            Yii::debug("Language code found in URL. Setting application language to '{$language}'.", __METHOD__);

            if ($this->enableLanguagePersistence) {
                $this->persistLanguage($language);
            }

            /**
             * "Reset" case: We called for example, /fr/demo/page so the persisted language was set back to "fr".
             * Now we can redirect to the URL without language prefix, if default prefixes are disabled.
             */
            $reset = $this->enableDefaultLanguageUrlCode === false && $language === $this->_defaultLanguage;

            if ($reset || $normalized) {
                $this->redirectToLanguage('');
            }
        } else {
            $language = null;

            if ($this->enableLanguagePersistence) {
                $language = $this->loadPersistedLanguage();
            }

            if ($language === null) {
                $language = $this->detectLanguage();
            }

            if ($language === null || $language === $this->_defaultLanguage) {
                if ($this->enableDefaultLanguageUrlCode === false) {
                    return;
                }

                $language = $this->_defaultLanguage;
            }

            // #35: Only redirect if a valid language was found
            if ($this->matchCode($language) === [null, null]) {
                return;
            }

            $key = array_search($language, $this->languages, true);

            if (is_string($key)) {
                $language = $key;
            }

            $this->redirectToLanguage($language);
        }
    }

    /**
     * Persists the given language code in the user session and/or a cookie for future requests.
     *
     * This method stores the provided language code in the session (if enabled) and in a cookie (if configured),
     * allowing the application to remember the user's language preference across requests and browser sessions.
     *
     * If the language changes, the {@see LanguageChangedEvent} event is triggered, providing both the old and new
     * language codes.
     *
     * @param string $language Language code to persist in session and cookie (for example, 'en', 'de-AT').
     */
    protected function persistLanguage(string $language): void
    {
        if ($this->hasEventHandlers(self::EVENT_LANGUAGE_CHANGED)) {
            $oldLanguage = $this->loadPersistedLanguage();

            if ($oldLanguage !== $language) {
                Yii::debug("Triggering languageChanged event: {$oldLanguage} -> {$language}", __METHOD__);

                $this->trigger(
                    self::EVENT_LANGUAGE_CHANGED,
                    new LanguageChangedEvent(
                        [
                            'oldLanguage' => $oldLanguage,
                            'language' => $language,
                        ],
                    ),
                );
            }
        }

        if (is_string($this->languageSessionKey)) {
            Yii::$app->session->set($this->languageSessionKey, $language);
            Yii::debug("Persisting language '$language' in session.", __METHOD__);
        }

        if ($this->languageCookieDuration > 0) {
            $cookie = new Cookie(
                array_merge(
                    ['httpOnly' => true],
                    $this->languageCookieOptions,
                    [
                        'name' => $this->languageCookieName,
                        'value' => $language,
                        'expire' => time() + $this->languageCookieDuration,
                    ],
                ),
            );

            Yii::$app->getResponse()->cookies->add($cookie);
            Yii::debug("Persisting language '{$language}' in cookie.", __METHOD__);
        }
    }

    /**
     * Retrieves the persisted language code from session or cookie storage, if available.
     *
     * This method checks for a previously stored language preference in the user session (if enabled) and then in the
     * language cookie.
     *
     * This mechanism allows the application to remember a user's language choice across requests and browser sessions,
     * supporting seamless language persistence and user experience.
     *
     * @return string|null Persisted language code, or `null` if none is found in session or cookie.
     */
    protected function loadPersistedLanguage(): string|null
    {
        if (is_string($this->languageSessionKey)) {
            $language = Yii::$app->session->get($this->languageSessionKey);

            if (is_string($language)) {
                Yii::debug("Found persisted language '$language' in session.", __METHOD__);

                return $language;
            }
        }

        $language = $this->_request?->getCookies()->getValue($this->languageCookieName);

        if (is_string($language)) {
            Yii::debug("Found persisted language '$language' in cookie.", __METHOD__);

            return $language;
        }

        return null;
    }

    /**
     * Detects the preferred language from the current request using browser headers or GeoIP information.
     *
     * This method attempts to determine the most appropriate language for the user by checking, in order:
     * - The `Accept-Language` HTTP header sent by the browser, matching it against configured languages and wildcards.
     * - The GeoIP country code (if available and configured), mapping it to a language if a match is found.
     *
     * This detection is used as a fallback when no language is found in the URL or persisted storage, ensuring users
     * are served content in their likely preferred language.
     *
     * @return string|null Detected language code, or `null` if no suitable language is found.
     */
    protected function detectLanguage(): string|null
    {
        if ($this->enableLanguageDetection) {
            /** @phpstan-var list<string> $acceptableLanguages */
            $acceptableLanguages = $this->_request?->getAcceptableLanguages() ?? [];

            foreach ($acceptableLanguages as $acceptable) {
                [$language, $country] = $this->matchCode($acceptable);

                if ($language !== null) {
                    $language = $country === null ? $language : "$language-$country";
                    Yii::debug("Detected browser language '{$language}'.", __METHOD__);

                    return $language;
                }
            }
        }

        if (isset($_SERVER[$this->geoIpServerVar])) {
            foreach ($this->geoIpLanguageCountries as $key => $codes) {
                $country = $_SERVER[$this->geoIpServerVar];

                if (in_array($country, $codes, true)) {
                    Yii::debug("Detected GeoIp language '{$key}'.", __METHOD__);

                    return $key;
                }
            }
        }

        return null;
    }

    /**
     * Determines if the provided language code matches any configured language or language pattern.
     *
     * This method checks the given code against the list of configured languages, supporting exact matches,
     * language-country pairs, and wildcards (for example, `en-*`).
     *
     * The return value is an array of the form `[$language, $country]`, where either value can be `null` if no match
     * is found.
     *
     * Matching rules:
     * - If `$code` is a single language (for example, `en`), returns `[$language, null]` if it matches an exact
     *   language or a wildcard (for example, `en-*`).
     * - If `$code` is a language-country pair (for example, `en-US`), returns `[$language, $country]` if it matches an
     *   exact language-country code or a wildcard (for example, `en-*`).
     * - If only the language part matches, returns `[$language, null]`.
     * - If no match is found, returns `[null, null]`.
     *
     * This method is used to validate and normalize language codes from URLs, browser headers, or persisted values,
     * ensuring that only supported languages are accepted and mapped correctly.
     *
     * @param string $code Language code to match (for example, `en`, `en-US`, `es-MX`).
     *
     * @return array An array with the matched language and country, or `null` values if not matched.
     *
     * @phpstan-return array{string|null, string|null}
     */
    protected function matchCode(string $code): array
    {
        $hasDash = str_contains($code, '-');
        $lcCode = strtolower($code);
        $lcLanguages = array_map('strtolower', $this->languages);

        if (($key = array_search($lcCode, $lcLanguages, true)) === false) {
            if ($hasDash) {
                $parts = explode('-', $code, 2);
                $language = $parts[0];
                $country = $parts[1] ?? null;
            } else {
                $language = $code;
                $country = null;
            }

            if (in_array($language . '-*', $this->languages, true)) {
                if ($hasDash && $country !== null) {
                    return [$language, strtoupper($country)];
                }

                return [$language, null];
            }

            if ($hasDash && in_array($language, $this->languages, true)) {
                return [$language, null];
            }

            return [null, null];
        }

        $result = $this->languages[$key] ?? $lcCode;

        if ($hasDash) {
            $parts = explode('-', $result, 2);
            return [$parts[0], $parts[1] ?? null];
        }

        return [$result, null];
    }

    /**
     * Redirects the user to the current URL with the specified language code applied.
     *
     * This method generates a new URL of the current route, injecting the provided language code as a parameter.
     *
     * If the language code is empty, the URL will be generated without a language prefix, resetting to the default
     * language if configured.
     *
     * The method ensures that redirects don't occur if the generated URL matches the current request URL, preventing
     * redirect loops.
     *
     * It also handles special cases for URL suffixes and script name configurations to produce canonical URLs.
     *
     * @param string $language Language code to add to the URL. Can be empty to remove the language code.
     *
     * @throws Exception if an unexpected error occurs during execution.
     * @throws ExitException if execution should be halted without exiting the process.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws InvalidRouteException if the route can't be resolved.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    private function redirectToLanguage(string $language): void
    {
        try {
            /** @phpstan-var array{0: string, 1: array<string, string>}|false $result */
            $result = $this->_request !== null ? parent::parseRequest($this->_request) : false;
        } catch (UrlNormalizerRedirectException $e) {
            if (is_array($e->url)) {
                $params = $e->url;
                $route = array_shift($params);
                $result = [$route, $params];
            } else {
                $result = [$e->url, []];
            }
        }

        if ($result === false) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }

        [$route, $params] = $result;

        if ($language !== '') {
            $params[$this->languageParam] = $language;
        }

        // See Yii Issues #8291 and #9161:
        $params += $this->_request?->getQueryParams();

        array_unshift($params, $route);

        /** @phpstan-var non-empty-array<string,string> $params */
        $url = $this->createUrl($params);

        // Required to prevent double slashes on generated URLs
        if ($route === '' && count($params) === 1) {
            $url = rtrim($url, '/') . '/';
        }

        /**
         * Prevent redirects to the same URL which could happen in certain UrlNormalizer / custom rule combinations
         */
        if ($url === $this->_request?->url) {
            return;
        }

        Yii::debug("Redirecting to {$url}.", __METHOD__);
        Yii::$app->response->redirect($url, $this->languageRedirectCode);

        if (YII_ENV === 'test') {
            /**
             * Response::redirect($url) above will call `Url::to()` internally.
             * So to test for the same final redirect URL here, we need to call Url::to(), too.
             */
            throw new Exception(Url::to($url));
        }

        Yii::$app->end();
    }
}
