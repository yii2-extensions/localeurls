# Upgrade Guide

## 0.2.0

### Breaking changes

- PHP 8.3 or later is required (`composer.json`: `"php": ">=8.3"`). Applications on PHP 8.1 or 8.2 stay on the `0.1.x`
  releases.
- `yii2\extensions\localeurls\UrlLanguageManager::parseRequest()` no longer sends redirects. It throws
  `yii\web\UrlNormalizerRedirectException`, and `yii\web\Application::handleRequest()` turns it into the redirect
  response. `Yii::$app->response->redirect()` and `Yii::$app->end()` are no longer called, so a redirect never
  terminates the process with `exit()`.
- Redirects required only by the URL normalizer carry the normalizer status code (`301` by default for
  `yii\web\UrlNormalizer`) instead of `languageRedirectCode`. Redirect URLs are unchanged.
- The protected helpers receive the request being parsed, and `processLocaleUrl()` returns the redirect language
  instead of redirecting:
  - `processLocaleUrl(bool $normalized): void` became `processLocaleUrl(Request $request): string|null`.
  - `detectLanguage(): string|null` became `detectLanguage(Request $request): string|null`.
  - `loadPersistedLanguage(): string|null` became `loadPersistedLanguage(Request $request): string|null`.
  - `persistLanguage(string $language): void` became `persistLanguage(Request $request, string $language): void`.
- The protected properties `$_request` and `$_processed`, and the private methods `ensureRequest()` and
  `redirectToLanguage()` were removed.
- The query parameter of the incoming request named by `languageParam` (`language` by default) no longer selects the
  redirect language and is dropped from redirect URLs: `/en/site/page?language=fr` redirects to `/site/page` instead
  of `/fr/site/page`, and `?language=//evil.com` no longer produces `Location: ///evil.com/site/page`. Link to another
  language with its URL code, for example `Url::to(['site/page', 'language' => 'fr'])`.
- Redirects thrown by custom URL rules as `yii\web\UrlNormalizerRedirectException`:
  - A string URL, relative or absolute, propagates unchanged, whether or not a language redirect is pending.
  - An array URL is created through the manager, so it carries the language code of the request; while a language
    redirect is pending, both are merged into one redirect that keeps the pending language.
- GeoIP names that start with `HTTP_` (`geoIpServerVar`) are read from the request headers instead of `$_SERVER`, and
  other names from the server parameters of the request. A value that exists only in the server environment, for
  example set with Apache `SetEnvIf` or `fastcgi_param HTTP_X_GEO_COUNTRY`, may no longer be seen. Send the country as
  a request header, or use a server variable without the `HTTP_` prefix, such as `GEOIP_COUNTRY_CODE`.
- `createUrl()` without a route (`$params[0]` missing) adds the language code when `ignoreLanguageUrlPatterns` is
  configured, unless a route pattern matches the empty route. It previously returned the URL of the parent
  `yii\web\UrlManager`.
- A persisted or detected language is redirected to its configured form only: with `['en', 'es']`, a stale `es-MX`
  cookie redirects to `/es/...` instead of looping on `/es-mx/es-mx/...`. A value outside the configured languages is
  ignored and causes no redirect.
- URL parsing fixes:
  - A language code must be followed by `/` or the end of the path: `/en-us-variant/page` is no longer parsed as
    `en-us` plus the route `-variant/page`.
  - Aliases match case-insensitively: `/AT/page` with `'at' => 'de-AT'` redirects to `/at/page`, or sets `de-AT`
    without a redirect when `keepUppercaseLanguageCode` is `true`. The redirect keeps the alias or code used in the
    URL: with `['de', 'deutsch' => 'de']`, `/Deutsch/page` redirects to `/deutsch/page` and `/DE/page` to `/de/page`.
  - Wildcard matches keep the language and a 2-3 letter region only: `Accept-Language: en-US-variant` with `en-*`
    redirects to `/en-us/...` instead of `/en-us-variant/...`.
  - The language part of a wildcard matches case-insensitively: `/ES-MX/page` with `es-*` resolves to `es-MX` and,
    while `keepUppercaseLanguageCode` is `false`, redirects to `/es-mx/page`, instead of setting and persisting
    `ES-MX`. A literal `es-*` value never matches a wildcard.
  - A language configured with a lowercase region, such as `de-at`, applies on `/de-at/page` without a redirect.
  - An uppercase code without a region, such as `/DE/page` with `de`, redirects to `/de/page` while
    `keepUppercaseLanguageCode` is `false`, instead of applying the spelling `DE` without a redirect.
  - A default-language code spelled in another case, such as `/EN/page` with `en` as the default language, redirects
    to `/page` in one step while `enableDefaultLanguageUrlCode` is `false`, and persists `en` like `/en/page` does.
  - Language codes and aliases are regex-quoted, so an alias such as `en.us` matches only itself.
- With a normalizer `action` of `UrlNormalizer::ACTION_NOT_FOUND`, a language-only URL that is not canonical (for
  example, `/de` with suffix `/`) now returns 404 like any other non-canonical URL, instead of redirecting.

### Migration steps

#### 1) Update code that calls `parseRequest()` directly

Catch the exception and build the response the way `yii\web\Application::handleRequest()` does:

```php
use yii\helpers\Url;
use yii\web\UrlNormalizerRedirectException;

try {
    $result = Yii::$app->urlManager->parseRequest(Yii::$app->request);
} catch (UrlNormalizerRedirectException $e) {
    return Yii::$app->response->redirect(Url::to($e->url, $e->scheme), $e->statusCode);
}
```

#### 2) Update `UrlLanguageManager` subclasses

Update the signatures of overridden helpers:

```php
use yii\web\Request;

protected function processLocaleUrl(Request $request): string|null
protected function detectLanguage(Request $request): string|null
protected function loadPersistedLanguage(Request $request): string|null
protected function persistLanguage(Request $request, string $language): void
```

Then update their bodies:

- Replace `$this->_request` and `ensureRequest()` with the `$request` argument.
- Remove checks of `$this->_processed`; each request object is processed once.
- Replace calls to `redirectToLanguage($language)` with a return value of `processLocaleUrl()`: the language code to
  redirect to, `''` to redirect to the URL without a language code, or `null` for no language redirect.
  `parseRequest()` builds the URL and throws the redirect.

#### 3) Update tests that assert redirects

Tests that relied on `YII_ENV === 'test'` (a `yii\base\Exception` whose message was the redirect URL) must catch
`yii\web\UrlNormalizerRedirectException` and assert `$e->url` and `$e->statusCode`.

#### 4) Keep `302` for normalizer redirects

If clients depend on temporary redirects for non-canonical URLs, configure the normalizer action:

```php
'normalizer' => [
    'class' => \yii\web\UrlNormalizer::class,
    'action' => \yii\web\UrlNormalizer::ACTION_REDIRECT_TEMPORARY,
],
```

#### 5) Set `defaultLanguage` in long-running workers (optional)

The new `defaultLanguage` property is `null` by default, which keeps the previous behavior. Under `psr-bridge`, set it,
or set `language` in the application configuration, so a request without a language code never inherits the language
of the previous request:

```php
'urlManager' => [
    'class' => \yii2\extensions\localeurls\UrlLanguageManager::class,
    'defaultLanguage' => 'en',
    'languages' => ['en', 'de'],
],
```

See the [worker-mode section](docs/configuration.md#worker-mode) of the configuration guide for the session, cookie,
and event handler requirements.
