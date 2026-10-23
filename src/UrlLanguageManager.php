<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls;

use ReflectionProperty;
use WeakMap;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\{Cookie, NotFoundHttpException, Request, UrlManager, UrlNormalizer, UrlNormalizerRedirectException};
use yii2\extensions\localeurls\exception\Message;

use function array_key_exists;
use function array_keys;
use function array_search;
use function count;
use function explode;
use function http_build_query;
use function implode;
use function in_array;
use function is_array;
use function is_string;
use function method_exists;
use function preg_match;
use function preg_quote;
use function rtrim;
use function str_contains;
use function str_ends_with;
use function str_replace;
use function str_starts_with;
use function strcasecmp;
use function strcspn;
use function strlen;
use function strpos;
use function strtolower;
use function strtoupper;
use function substr;
use function substr_replace;
use function time;
use function trim;

/**
 * Resolves the application language from a URL code, the persisted choice, the browser, or GeoIP, and adds the language
 * code to created URLs.
 *
 * A URL without a code redirects to the persisted or detected language; a URL with the default language code redirects
 * to the URL without it unless {@see $enableDefaultLanguageUrlCode} is `true`.
 *
 * The manager keeps no per-request state and is safe for long-running workers; redirects are raised as
 * {@see UrlNormalizerRedirectException} and handled by the application.
 *
 * @see LanguageChangedEvent
 */
class UrlLanguageManager extends UrlManager
{
    /**
     * @event LanguageChangedEvent Raised before a language from the URL that differs from the persisted one is
     * persisted.
     */
    public const EVENT_LANGUAGE_CHANGED = 'languageChanged';
    /**
     * Default language: the language of URLs without a code when {@see $enableDefaultLanguageUrlCode} is `false`.
     *
     * `null` captures `Yii::$app->language` in {@see init()}. Set it explicitly in long-running workers, where the
     * application object and its language persist between requests; it is also applied to `Yii::$app->language` at the
     * start of each request.
     */
    public string|null $defaultLanguage = null;
    /**
     * Whether the default language uses a URL code like any other language.
     *
     * When `false`, a URL without a code uses the default language, and a URL with the default code redirects to the
     * URL without it (default `fr`: `/fr/some/page` -> `/some/page`), which also resets the persisted language. When
     * `true`, a URL without a code redirects to the default code (`/some/page` -> `/fr/some/page`).
     */
    public bool $enableDefaultLanguageUrlCode = false;
    /**
     * Whether to detect the language from the `Accept-Language` header when the URL has no code and no language is
     * persisted.
     *
     * GeoIP detection through {@see $geoIpLanguageCountries} applies regardless of this setting.
     */
    public bool $enableLanguageDetection = true;
    /**
     * Whether to persist the language from the URL in the session and the language cookie.
     *
     * When `true`, a URL without a code redirects to the persisted language (`/some/page` -> `/fr/some/page`).
     */
    public bool $enableLanguagePersistence = true;
    /**
     * Whether to enable language processing; when `false`, the manager behaves like {@see UrlManager}.
     */
    public bool $enableLocaleUrls = true;
    /**
     * Whether to enable pretty URLs; must be `true` while locale URLs are enabled.
     *
     * Declared without a native type to match {@see UrlManager::$enablePrettyUrl}.
     */
    public $enablePrettyUrl = true;
    /**
     * GeoIP country codes indexed by the language they select.
     *
     * ```php
     * [
     *     'de' => ['DEU', 'AUT'],
     *     'ru' => ['RUS', 'BLR', 'KAZ'],
     * ]
     * ```
     *
     * @phpstan-var array<string, array<int, string>>
     */
    public array $geoIpLanguageCountries = [];
    /**
     * Name of the server variable that carries the GeoIP country code.
     *
     * Names starting with `HTTP_` are read as the request header they represent (`HTTP_X_GEO_COUNTRY` ->
     * `X-Geo-Country`); other names are read from `getServerParams()` when the request provides it, otherwise from
     * `$_SERVER`.
     */
    public string $geoIpServerVar = 'HTTP_X_GEO_COUNTRY';
    /**
     * Regular expressions that exclude routes and URLs from language processing.
     *
     * Keys match routes in {@see createUrl()}, which then adds no language code; values match the path info in
     * {@see parseRequest()}, which then resolves no language.
     *
     * The first pair assumes URL rules that map `login` and `register` to `site/login` and `site/register`.
     *
     * ```php
     * [
     *     '#^site/(login|register)#' => '#^(login|register)#',
     *     '#^api/#' => '#^api/#',
     * ]
     * ```
     *
     * @phpstan-var array<string, string>
     */
    public array $ignoreLanguageUrlPatterns = [];
    /**
     * Whether to keep uppercase letters of language codes in URLs.
     *
     * When `false`, created URLs use lowercase codes, and a URL with an uppercase country (`/de-AT/page`) or with an
     * alias spelled differently from its configuration (`/AT/page`) redirects to the lowercase URL.
     */
    public bool $keepUppercaseLanguageCode = false;
    /**
     * Lifetime of the language cookie in seconds; `0` disables the cookie.
     *
     * Applies only when {@see $enableLanguagePersistence} is `true`.
     */
    public int $languageCookieDuration = 2592000;
    /**
     * Name of the language cookie.
     */
    public string $languageCookieName = '_language';
    /**
     * Additional {@see Cookie} properties for the language cookie.
     *
     * `httpOnly` defaults to `true`; `name`, `value`, and `expire` always come from {@see $languageCookieName}, the
     * language, and {@see $languageCookieDuration}.
     *
     * @phpstan-var array<string, bool|int|string>
     */
    public array $languageCookieOptions = [];
    /**
     * Name of the {@see createUrl()} parameter that selects the language of the created URL.
     *
     * URLs created with this parameter switch to that language; without it, the current application language is used.
     */
    public string $languageParam = 'language';
    /**
     * HTTP status code of language redirects.
     *
     * Redirects caused only by the URL normalizer keep the normalizer status code.
     */
    public int $languageRedirectCode = 302;
    /**
     * Available languages: codes (`en-US`), wildcards (`es-*`), or URL aliases mapped to codes (`'english' => 'en'`).
     *
     * @phpstan-var array<array-key, string>
     */
    public array $languages = [];
    /**
     * Session key that stores the language; `false` disables session persistence.
     */
    public string|bool $languageSessionKey = '_language';

    /**
     * Default language resolved by {@see init()} from {@see $defaultLanguage} or the application language.
     */
    protected string $_defaultLanguage = '';

    /**
     * Requests already processed by {@see parseRequest()}; entries disappear with their request objects.
     *
     * @phpstan-var WeakMap<Request, true>|null
     */
    private WeakMap|null $processedRequests = null;

    /**
     * Creates a URL and inserts the language code after the base URL or the script name.
     *
     * @param array|string $params Route and parameters; {@see $languageParam} selects the language of the URL.
     *
     * @throws InvalidConfigException if the base URL or the script URL cannot be determined.
     *
     * @return string Created URL.
     *
     * @phpstan-param array<array-key, string>|string $params
     *
     * @phpstan-ignore method.childParameterType
     */
    public function createUrl($params): string
    {
        $params = (array) $params;

        if ($this->enableLocaleUrls === false || $this->languages === []) {
            return parent::createUrl($params);
        }

        $route = trim($params[0] ?? '', '/');

        if ($this->firstMatchingPattern(array_keys($this->ignoreLanguageUrlPatterns), $route) !== null) {
            return parent::createUrl($params);
        }

        $isLanguageGiven = isset($params[$this->languageParam]);
        $language = $params[$this->languageParam] ?? Yii::$app->language;

        unset($params[$this->languageParam]);

        $url = parent::createUrl($params);

        if ($this->shouldPrefixLanguage($language, $isLanguageGiven) === false) {
            return $url;
        }

        return $this->insertLanguageCode($url, $this->toUrlCode($language), count($params));
    }

    /**
     * Returns the default language: {@see $defaultLanguage}, or the application language captured when the manager was
     * initialized.
     *
     * @return string Default language code, for example `en`.
     */
    public function getDefaultLanguage(): string
    {
        return $this->_defaultLanguage;
    }

    /**
     * Captures the default language and validates the configuration.
     *
     * @throws InvalidConfigException if locale URLs are enabled while {@see $enablePrettyUrl} is `false`, or if the
     * parent configuration is invalid.
     */
    public function init(): void
    {
        if ($this->enableLocaleUrls && $this->languages !== [] && $this->enablePrettyUrl === false) {
            throw new InvalidConfigException(Message::LOCALE_URLS_REQUIRE_PRETTY_URL->getMessage());
        }

        $this->_defaultLanguage = $this->defaultLanguage ?? Yii::$app->language;

        parent::init();
    }

    /**
     * Resolves the request language, removes its code from the path info, and parses the request.
     *
     * Each request object is processed once; later calls only parse it. A request not processed yet first resets
     * `Yii::$app->language` to {@see $defaultLanguage} when it is set. When the request must move to its canonical
     * language URL, {@see UrlNormalizerRedirectException} carries the target URL and {@see $languageRedirectCode}, or
     * the normalizer status code when only the URL normalizer requires the redirect. A redirect raised by a URL rule
     * with a string URL is rethrown unchanged.
     *
     * @param Request $request Request to parse.
     *
     * @throws InvalidConfigException if the path info of the request cannot be determined.
     * @throws NotFoundHttpException if no rule matches the request while a language redirect is pending, or if the URL
     * normalizer action is `ACTION_NOT_FOUND` and the path info is not normalized.
     * @throws UrlNormalizerRedirectException if the request must be redirected.
     *
     * @return array|bool Route and parameters, or `false` if no rule matches the request.
     *
     * @phpstan-return array<array-key, mixed>|bool
     */
    public function parseRequest($request): array|bool
    {
        $isProcessed = $this->processedRequests[$request] ?? false;

        // Long-running workers keep the application language of the previous request.
        if ($isProcessed === false && $this->defaultLanguage !== null) {
            Yii::$app->language = $this->defaultLanguage;
        }

        if (
            $this->enableLocaleUrls === false
            || $this->languages === []
            || $isProcessed
            || $this->matchesIgnoredUrl($request)
        ) {
            return parent::parseRequest($request);
        }

        $this->processedRequests ??= new WeakMap();
        $this->processedRequests[$request] = true;

        $pathInfo = $request->getPathInfo();
        // `null`: no language redirect; `''`: redirect to the URL without code; otherwise the code to redirect to.
        $language = $this->processLocaleUrl($request);
        [$result, $redirect] = $this->parseLocalizedRequest($request, $pathInfo, $language === null);

        if ($result === false) {
            // A pending language redirect needs a route; without one, the page does not exist.
            return $language === null ? false : throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }

        if ($language === null && $redirect === null) {
            return $result;
        }

        $url = $this->createRedirectUrl($request, $result, $language ?? '');

        // Prevents redirect loops in some UrlNormalizer and custom rule combinations.
        if ($url === $request->getUrl()) {
            return $result;
        }

        Yii::debug("Redirecting to {$url}.", __METHOD__);

        // A redirect caused only by the normalizer keeps its status code; a rule redirect always keeps its scheme.
        [$statusCode, $scheme] = $language === null
            ? [$redirect->statusCode, $redirect->scheme]
            : [$this->languageRedirectCode, $redirect->scheme ?? false];

        throw new UrlNormalizerRedirectException($url, $statusCode, $scheme);
    }

    /**
     * Detects the language from the `Accept-Language` header, then from the GeoIP country.
     *
     * @param Request $request Request that provides the header and the GeoIP country.
     *
     * @return string|null Detected language code, or `null` if no configured language matches.
     */
    protected function detectLanguage(Request $request): string|null
    {
        return $this->detectBrowserLanguage($request) ?? $this->detectGeoIpLanguage($request);
    }

    /**
     * Returns the language persisted in the session or, failing that, in the language cookie.
     *
     * @param Request $request Request that provides the language cookie.
     *
     * @return string|null Persisted language code, or `null` if none is stored.
     */
    protected function loadPersistedLanguage(Request $request): string|null
    {
        $sessionKey = $this->sessionKey();

        if ($sessionKey !== null) {
            $language = Yii::$app->session->get($sessionKey);

            if (is_string($language)) {
                Yii::debug("Found persisted language '{$language}' in session.", __METHOD__);

                return $language;
            }
        }

        $language = $request->getCookies()->getValue($this->languageCookieName);

        if (is_string($language)) {
            Yii::debug("Found persisted language '{$language}' in cookie.", __METHOD__);

            return $language;
        }

        return null;
    }

    /**
     * Matches a language code against {@see $languages}, case-insensitively and including wildcards.
     *
     * Exact matches skip wildcards and return the configured spelling. A wildcard match (`es-*`) returns the configured
     * spelling of the language and the first subtag when it is a 2-3 letter region, in uppercase: `ES-mx-variant` ->
     * `['es', 'MX']`; any other subtag is dropped: `es-Latn` -> `['es', null]`. A code whose region is not configured
     * falls back to its language when that language is configured: `de-CH` -> `['de', null]`.
     *
     * @param string $code Language code to match, for example `en`, `en-US`, or `es-MX`.
     *
     * @return array Matched language and country, or `[null, null]` if no configured language matches.
     *
     * @phpstan-return array{string|null, string|null}
     */
    protected function matchCode(string $code): array
    {
        foreach ($this->languages as $configured) {
            // A wildcard is not a language code: `es-*` never matches exactly.
            if (str_ends_with($configured, '-*')) {
                continue;
            }

            if (strcasecmp($configured, $code) === 0) {
                $position = strpos($configured, '-');

                return $position === false
                    ? [$configured, null]
                    : [substr($configured, 0, $position), substr($configured, $position + 1)];
            }
        }

        $parts = explode('-', $code);
        $language = $parts[0];

        foreach ($this->languages as $configured) {
            if (strcasecmp($configured, "{$language}-*") === 0) {
                $region = $parts[1] ?? '';

                return [
                    substr($configured, 0, -2),
                    preg_match('/^[a-z]{2,3}$/i', $region) === 1 ? strtoupper($region) : null,
                ];
            }
        }

        foreach ($this->languages as $configured) {
            if (strcasecmp($configured, $language) === 0) {
                return [$configured, null];
            }
        }

        return [null, null];
    }

    /**
     * Stores the language in the session and the language cookie.
     *
     * Triggers {@see EVENT_LANGUAGE_CHANGED} first when handlers are attached and the language differs from the
     * persisted one.
     *
     * @param Request $request Request that provides the previously persisted language.
     * @param string $language Language code to persist, for example `en` or `de-AT`.
     */
    protected function persistLanguage(Request $request, string $language): void
    {
        if ($this->hasEventHandlers(self::EVENT_LANGUAGE_CHANGED)) {
            $oldLanguage = $this->loadPersistedLanguage($request);

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

        $sessionKey = $this->sessionKey();

        if ($sessionKey !== null) {
            Yii::$app->session->set($sessionKey, $language);
            Yii::debug("Persisting language '{$language}' in session.", __METHOD__);
        }

        if ($this->languageCookieDuration > 0) {
            $cookie = new Cookie(
                [
                    ...$this->languageCookieOptions,
                    'name' => $this->languageCookieName,
                    'value' => $language,
                    'expire' => time() + $this->languageCookieDuration,
                ],
            );

            Yii::$app->getResponse()->cookies->add($cookie);
            Yii::debug("Persisting language '{$language}' in cookie.", __METHOD__);
        }
    }

    /**
     * Resolves the language of the request and returns the language redirect it requires.
     *
     * A language code at the start of the path info is removed from it, applied to `Yii::$app->language`, and
     * persisted. Without a code, the persisted language, then the detected language, then (with
     * {@see $enableDefaultLanguageUrlCode}) the default language selects the redirect target.
     *
     * @param Request $request Request whose path info is processed.
     *
     * @throws InvalidConfigException if the path info of the request cannot be determined.
     *
     * @return string|null Language code or alias to redirect to, `''` to redirect to the URL without a language code,
     * or `null` if no language redirect is required.
     */
    protected function processLocaleUrl(Request $request): string|null
    {
        $pathInfo = $request->getPathInfo();

        if (preg_match($this->buildLanguagePattern(), $pathInfo, $matches) !== 1) {
            return $this->findRedirectLanguage($request);
        }

        /** @phpstan-var array{string, string} $matches */
        $code = $matches[1];

        $this->removeLanguageCode($request, $pathInfo, $code);

        [$language, $isCanonical] = $this->resolveCode($code);

        // The language applies before the URL rules parse the request, so they resolve the route for this language.
        Yii::$app->language = $language;
        Yii::debug("Language code found in URL. Setting application language to '{$language}'.", __METHOD__);

        // "Reset" case: the default language code is removed from the URL, whatever its spelling.
        $isReset = $this->enableDefaultLanguageUrlCode === false && $language === $this->getDefaultLanguage();

        // A code or alias spelled differently from its canonical form redirects to the lowercase spelling of the URL.
        if ($isReset === false && $isCanonical === false && $this->keepUppercaseLanguageCode === false) {
            return strtolower($code);
        }

        if ($this->enableLanguagePersistence) {
            $this->persistLanguage($request, $language);
        }

        return $isReset ? '' : null;
    }

    /**
     * Builds the pattern that matches a configured language code or alias as the first path info segment.
     *
     * @return string Case-insensitive regular expression; group `1` captures the code.
     */
    private function buildLanguagePattern(): string
    {
        $parts = [];

        foreach ($this->languages as $key => $value) {
            $code = preg_quote(is_string($key) ? $key : $value, '#');

            // `\-\*` is the quoted wildcard suffix `-*`.
            if (str_ends_with($code, '\-\*')) {
                $language = substr($code, 0, -4);
                $parts[] = "{$language}-[a-z]{2,3}";
                $parts[] = $language;
            } else {
                $parts[] = $code;
            }
        }

        return '#^(' . implode('|', $parts) . ')(?:/|$)#i';
    }

    /**
     * Creates the URL of the parsed route with the given language and the query parameters of the request.
     *
     * @param Request $request Request that provides the query parameters.
     * @param array $result Parsed route and parameters.
     * @param string $language Language code of the URL, or `''` for the current application language.
     *
     * @throws InvalidConfigException if the base URL or the script URL cannot be determined.
     *
     * @return string Redirect URL.
     *
     * @phpstan-param array{0: string, 1: array<array-key, mixed>} $result
     */
    private function createRedirectUrl(Request $request, array $result, string $language): string
    {
        [$route, $params] = $result;

        // The language of the redirect never comes from the query: `?language=//evil.com` must not become the host.
        $query = $request->getQueryParams();

        unset($query[$this->languageParam]);

        if ($language !== '') {
            $params[$this->languageParam] = $language;
        }

        // See Yii issues #8291 and #9161; the union keeps numeric query keys, and route parameters win over the query.
        $params += $query;

        // `createUrl()` reads the route from index `0`, so a parameter with that key is added after the URL is created.
        $zero = array_key_exists(0, $params) ? [$params[0]] : null;

        unset($params[0]);

        $url = $this->createUrl([$route] + $params);

        // Prevents double slashes on root URLs.
        if ($route === '' && $params === []) {
            $url = rtrim($url, '/') . '/';
        }

        // The parameter goes before the fragment.
        if ($zero !== null) {
            $fragmentPos = strpos($url, '#');
            $fragment = $fragmentPos === false ? '' : substr($url, $fragmentPos);
            $url = $fragmentPos === false ? $url : substr($url, 0, $fragmentPos);
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($zero) . $fragment;
        }

        return $url;
    }

    /**
     * Detects the language from the `Accept-Language` header while {@see $enableLanguageDetection} is `true`.
     *
     * @param Request $request Request that provides the header.
     *
     * @return string|null Detected language code, or `null` if no acceptable language matches.
     */
    private function detectBrowserLanguage(Request $request): string|null
    {
        if ($this->enableLanguageDetection === false) {
            return null;
        }

        /** @phpstan-var list<string> $acceptableLanguages */
        $acceptableLanguages = $request->getAcceptableLanguages();

        foreach ($acceptableLanguages as $acceptable) {
            [$language, $country] = $this->matchCode($acceptable);

            if ($language !== null) {
                $language = $country === null ? $language : "{$language}-{$country}";

                Yii::debug("Detected browser language '{$language}'.", __METHOD__);

                return $language;
            }
        }

        return null;
    }

    /**
     * Detects the language from the GeoIP country through {@see $geoIpLanguageCountries}.
     *
     * @param Request $request Request that provides the GeoIP country.
     *
     * @return string|null Detected language code, or `null` if no configured country matches.
     */
    private function detectGeoIpLanguage(Request $request): string|null
    {
        $geoCountry = $this->readGeoIpCountry($request);

        foreach ($this->geoIpLanguageCountries as $language => $countries) {
            if (in_array($geoCountry, $countries, true)) {
                Yii::debug("Detected GeoIp language '{$language}'.", __METHOD__);

                return $language;
            }
        }

        return null;
    }

    /**
     * Returns the language a URL without a language code redirects to.
     *
     * @param Request $request Request that provides the persisted and detected language.
     *
     * @return string|null Configured language code, or `null` if the URL stays without a code.
     */
    private function findRedirectLanguage(Request $request): string|null
    {
        $language = $this->enableLanguagePersistence ? $this->loadPersistedLanguage($request) : null;
        $language ??= $this->detectLanguage($request);

        if ($language === null || $language === $this->getDefaultLanguage()) {
            if ($this->enableDefaultLanguageUrlCode === false) {
                return null;
            }

            $language = $this->getDefaultLanguage();
        }

        // Redirects only to the canonical form of a configured language, never to the raw value
        // (codemix/yii2-localeurls#35); no match yields `[null, null]`, so no redirect.
        [$code, $country] = $this->matchCode($language);

        return $country === null ? $code : "{$code}-{$country}";
    }

    /**
     * Returns the first regular expression of a list that matches a subject.
     *
     * @param array $patterns Regular expressions to try in order.
     * @param string $subject Route or path info to match.
     *
     * @return string|null Matching regular expression, or `null` if none matches.
     *
     * @phpstan-param array<array-key, string> $patterns
     */
    private function firstMatchingPattern(array $patterns, string $subject): string|null
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $subject) === 1) {
                return $pattern;
            }
        }

        return null;
    }

    /**
     * Inserts the language code after the base URL or the script name of a created URL.
     *
     * @param string $url URL created by the parent {@see UrlManager}.
     * @param string $code Language code as it appears in the URL.
     * @param int $paramCount Number of route parameters, the route included.
     *
     * @throws InvalidConfigException if the base URL or the script URL cannot be determined.
     *
     * @return string URL with the language code.
     */
    private function insertLanguageCode(string $url, string $code, int $paramCount): string
    {
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
            if ($paramCount === 1) {
                /**
                 * / -> ''
                 * /base/ -> /base
                 * /index.php/ -> /index.php
                 * /base/index.php/ -> /base/index.php
                 */
                if ($url === $prefix . '/') {
                    $url = rtrim($url, '/');
                }
            } elseif (str_starts_with($url, "{$prefix}/?")) {
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
         * For an absolute or protocol-relative URL, add the length of its authority:
         * - http://www.example.com
         * - http://www.example.com?x=y
         * - http://www.example.com#top
         * - http://www.example.com/foo/bar
         */
        if (preg_match('#^(?:[a-z][a-z0-9+.-]*:)?//#i', $url, $matches) === 1) {
            // The host URL ends at the first `/`, `?` or `#` after the authority delimiter.
            $authorityStart = strlen($matches[0]);
            $insertPos += $authorityStart + strcspn($url, '/?#', $authorityStart);
        }

        return substr_replace($url, '/' . $code, $insertPos, 0);
    }

    /**
     * Returns whether a URL pattern of {@see $ignoreLanguageUrlPatterns} matches the path info of the request.
     *
     * @param Request $request Request to check.
     *
     * @throws InvalidConfigException if the path info of the request cannot be determined.
     *
     * @return bool Whether language processing is skipped for the request.
     */
    private function matchesIgnoredUrl(Request $request): bool
    {
        $pathInfo = $request->getPathInfo();
        $pattern = $this->firstMatchingPattern($this->ignoreLanguageUrlPatterns, $pathInfo);

        if ($pattern === null) {
            return false;
        }

        Yii::debug("Ignore pattern '{$pattern}' matches '{$pathInfo}.' Skipping language processing.", __METHOD__);

        return true;
    }

    /**
     * Applies the URL normalizer to the route of a URL that consists of a language code only, such as `/de` with
     * suffix `/`.
     *
     * The parent cannot normalize such a URL because the path info is empty once the code is removed.
     *
     * @param string $pathInfo Path info before the language code was removed.
     * @param array $result Route and parameters parsed from the empty path info.
     *
     * @throws InvalidConfigException if the normalizer action is invalid.
     * @throws NotFoundHttpException if the path info changes and the normalizer action is `ACTION_NOT_FOUND`.
     * @throws UrlNormalizerRedirectException if the path info changes and the normalizer action is a redirect.
     *
     * @return array Route and parameters, normalized when the path info changes.
     *
     * @phpstan-param array{0: string, 1: array<array-key, mixed>} $result
     *
     * @phpstan-return array{0: string, 1: array<array-key, mixed>}
     */
    private function normalizeLanguageRoot(string $pathInfo, array $result): array
    {
        if ($this->normalizer instanceof UrlNormalizer === false) {
            return $result;
        }

        // The parent declares `$suffix` as `string`, but it defaults to `null`.
        $this->normalizer->normalizePathInfo($pathInfo, (string) $this->suffix, $changed);

        if ($changed === false) {
            return $result;
        }

        return $this->normalizer->normalizeRoute($result);
    }

    /**
     * Parses a request whose language code was removed and turns an array redirect of a URL rule into its route.
     *
     * @param Request $request Request to parse.
     * @param string $pathInfo Path info before the language code was removed.
     * @param bool $normalizeRoot Whether a language-only URL is normalized on the parsed route.
     *
     * @throws InvalidConfigException if the path info of the request cannot be determined.
     * @throws NotFoundHttpException if the URL normalizer action is `ACTION_NOT_FOUND` and the path info changes.
     * @throws UrlNormalizerRedirectException if a URL rule redirects to a string URL.
     *
     * @return array Route and parameters, or `false`, and the redirect raised by the parent, or `null`.
     *
     * @phpstan-return array{
     *   array{0: string, 1: array<array-key, mixed>}|false,
     *   UrlNormalizerRedirectException|null,
     * }
     */
    private function parseLocalizedRequest(Request $request, string $pathInfo, bool $normalizeRoot): array
    {
        try {
            /** @phpstan-var array{0: string, 1: array<array-key, mixed>}|false $result */
            $result = parent::parseRequest($request);

            if ($result !== false && $normalizeRoot && $pathInfo !== '' && $request->getPathInfo() === '') {
                $result = $this->normalizeLanguageRoot($pathInfo, $result);
            }

            return [$result, null];
        } catch (UrlNormalizerRedirectException $e) {
            $params = $e->url;

            // A string URL is a final target chosen by a URL rule; an array URL needs its route at index `0`.
            if (is_array($params) === false || is_string($params[0] ?? null) === false) {
                throw $e;
            }

            $route = $params[0];

            // Keeps the keys of the parameters, numeric ones included.
            unset($params[0]);

            return [[$route, $params], $e];
        }
    }

    /**
     * Reads the GeoIP country code named by {@see $geoIpServerVar} from the request.
     *
     * @param Request $request Request that provides the headers or the server parameters.
     *
     * @return mixed Country code, or `null` if the request does not provide it.
     */
    private function readGeoIpCountry(Request $request): mixed
    {
        $name = $this->geoIpServerVar;

        if (str_starts_with($name, 'HTTP_')) {
            return $request->getHeaders()->get(str_replace('_', '-', substr($name, 5)));
        }

        /** @phpstan-var array<array-key, mixed> $params */
        $params = method_exists($request, 'getServerParams') ? $request->getServerParams() : $_SERVER;

        return $params[$name] ?? null;
    }

    /**
     * Removes one language segment and its delimiter without discarding extra slashes needed by URL normalizers.
     *
     * @param Request $request Request whose path info is updated.
     * @param string $pathInfo Path info before the language code was removed.
     * @param string $code Matched language code or alias.
     *
     * @throws \ReflectionException if the installed Yii request no longer exposes its path-info cache.
     */
    private function removeLanguageCode(Request $request, string $pathInfo, string $code): void
    {
        $remaining = substr($pathInfo, strlen($code));
        $request->setPathInfo($remaining);

        if (str_starts_with($remaining, '//')) {
            // Yii's setter trims every leading slash; its path-info cache must retain the extra delimiters for rules.
            // Use the supplied path info, which can differ from the URI after an application rewrite.
            (new ReflectionProperty(Request::class, '_pathInfo'))->setValue($request, substr($remaining, 1));
        }
    }

    /**
     * Resolves a language code or alias found in the URL to its configured language.
     *
     * @param string $code Code as written in the URL.
     *
     * @return array Language code, and whether the URL spelling is canonical: the lowercase alias, or the lowercase
     * language code.
     *
     * @phpstan-return array{string, bool}
     */
    private function resolveCode(string $code): array
    {
        foreach ($this->languages as $alias => $language) {
            if (is_string($alias) && strcasecmp($alias, $code) === 0) {
                return [$language, strtolower($alias) === $code];
            }
        }

        [$language, $country] = $this->matchCode($code);

        $language ??= $code;

        if ($country !== null) {
            $language = "{$language}-{$country}";
        }

        return [$language, strtolower($language) === $code];
    }

    /**
     * Returns the session key of the language, or `null` when {@see $languageSessionKey} disables session persistence.
     *
     * @return string|null Session key, or `null` if the language is not stored in the session.
     */
    private function sessionKey(): string|null
    {
        return is_string($this->languageSessionKey) ? $this->languageSessionKey : null;
    }

    /**
     * Returns whether a created URL gets a language code.
     *
     * @param string $language Language of the URL.
     * @param bool $isLanguageGiven Whether the language comes from {@see $languageParam}.
     *
     * @return bool Whether the language code is added to the URL.
     */
    private function shouldPrefixLanguage(string $language, bool $isLanguageGiven): bool
    {
        // Only add language if it is not empty and ...
        return $language !== '' && (
            // ... it is not the default language or default language uses URL code ...
            $language !== $this->getDefaultLanguage() || $this->enableDefaultLanguageUrlCode
            // ... or it is given explicitly while persistence or detection is enabled ("reset URL").
            || ($isLanguageGiven && ($this->enableLanguagePersistence || $this->enableLanguageDetection))
        );
    }

    /**
     * Returns the URL spelling of a language: its alias when one is configured, lowercase unless
     * {@see $keepUppercaseLanguageCode} is `true`.
     *
     * @param string $language Language code, for example `de-AT`.
     *
     * @return string Language code or alias as it appears in the URL.
     */
    private function toUrlCode(string $language): string
    {
        $alias = array_search($language, $this->languages, true);

        if (is_string($alias)) {
            $language = $alias;
        }

        return $this->keepUppercaseLanguageCode ? $language : strtolower($language);
    }
}
