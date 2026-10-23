# Upgrade Guide

## 0.2.0

- Upgrade PHP to 8.3 or later and Yii to `~2.0.55 || ^22.0@dev`.
- `UrlLanguageManager::parseRequest()` throws `yii\web\UrlNormalizerRedirectException` instead of sending the redirect
  and ending the application. Code that calls it directly must catch the exception and redirect to
  `Url::to($e->url, $e->scheme)` with `$e->statusCode`.
- Tests that caught the `YII_ENV === 'test'` redirect exception must catch `UrlNormalizerRedirectException` and assert
  `$e->url` and `$e->statusCode`.
- Redirects required only by the URL normalizer use its status code (`301` by default) instead of
  `languageRedirectCode`. Set the normalizer `action` to `UrlNormalizer::ACTION_REDIRECT_TEMPORARY` to keep `302`.
- In `UrlLanguageManager` subclasses, the protected helpers receive the request:
  `processLocaleUrl(Request $request): string|null`, `detectLanguage(Request $request)`,
  `loadPersistedLanguage(Request $request)` and `persistLanguage(Request $request, string $language)`. Return the
  redirect language from `processLocaleUrl()` (`''` for no language code, `null` for no redirect) instead of calling
  `redirectToLanguage()`, and replace `$_request`, `$_processed` and `ensureRequest()` with the `$request` argument.
- The `languageParam` query parameter no longer selects the redirect language and is dropped from redirect URLs. Link to
  another language with its URL code, for example `Url::to(['site/page', 'language' => 'fr'])`.
- `geoIpServerVar` names that start with `HTTP_` are read from request headers. Send the country as a request header, or
  use a server variable without the `HTTP_` prefix, such as `GEOIP_COUNTRY_CODE`.
- Language codes and aliases are parsed strictly and case-insensitively, redirects use the configured language form,
  `createUrl()` without a route adds the language code when `ignoreLanguageUrlPatterns` is set, and
  `UrlNormalizer::ACTION_NOT_FOUND` returns 404 for non-canonical language-only URLs. Update tests that asserted the
  previous URLs.
