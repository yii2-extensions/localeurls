# Configuration guide

`yii2\extensions\localeurls\UrlLanguageManager` extends `yii\web\UrlManager` and replaces the `urlManager` component.
This guide shows how to combine its configuration properties.

## Basic configuration

### Minimal setup

```php
<?php

declare(strict_types=1);

use yii2\extensions\localeurls\UrlLanguageManager;

return [
    'components' => [
        'urlManager' => [
            'class' => UrlLanguageManager::class,
            'enablePrettyUrl' => true,
            'languages' => ['en', 'de'],
        ],
    ],
];
```

### Standard web application

```php
<?php

declare(strict_types=1);

use yii2\extensions\localeurls\UrlLanguageManager;

return [
    'components' => [
        'urlManager' => [
            'class' => UrlLanguageManager::class,
            'enableLanguageDetection' => true,
            'enableLanguagePersistence' => true,
            'enablePrettyUrl' => true,
            'languages' => ['en-US', 'en', 'es', 'fr', 'de'],
            'rules' => [
                '' => 'site/index',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ],
            'showScriptName' => false,
        ],
    ],
];
```

## Language configuration

### Language array

Define available languages for your application.

```php
'languages' => [
    // Specific locale
    'en-US',
    // Language only
    'en',
    // German
    'de',
    // Portuguese
    'pt',
],
```

### Language aliases

Map custom URL segments to language codes.

```php
'languages' => [
    'en-US',
    'en',
    // /deutsch/page → German (de)
    'deutsch' => 'de',
    // /french/page → French (fr)
    'french' => 'fr',
    // /at/page → Austrian German (de-AT)
    'at' => 'de-AT',
],
```

Aliases match in any case; see [Language code case](#language-code-case) for the redirect to the lowercase alias.

### Wildcard language support

Support language variants with wildcards.

```php
'languages' => [
    'en-US',
    'en',
    // Matches: de-AT, de-CH, de-DE, etc.
    'de-*',
    // Matches: es-MX, es-AR, es-ES, etc.
    'es-*',
    // Custom wildcard pattern
    'wc-*',
],
```

The language part of a wildcard matches in any case and keeps its configured spelling: `/ES-MX/page` resolves to
`es-MX`. The region must have two or three letters. A literal `*` never matches: a persisted or detected value `es-*`
resolves to `es`.

### Default language

The default language has no code in the URL unless `enableDefaultLanguageUrlCode` is `true`. Without
`defaultLanguage`, the manager uses the application `language` at the time the manager is created.

```php
// Default: the application language when the manager is created
'defaultLanguage' => null,
// Explicit default language, applied at the start of every request
'defaultLanguage' => 'en',
```

When `defaultLanguage` is set, the manager assigns it to `Yii::$app->language` once per request object, at the start
of `parseRequest()` and before the `enableLocaleUrls`, `languages`, and ignore pattern checks. The URL code, the
persisted language, or the detected language then replaces it as usual. A language set earlier in the same request,
for example by a `beforeRequest` handler, is overwritten. Use one of the configured `languages`, and see
[Worker mode](#worker-mode) for long-running workers.

## URL code configuration

### Default language URL code

Control whether the default language appears in the URL.

```php
// Default behavior: omit default code
'enableDefaultLanguageUrlCode' => false,
// Alternative: include default code
'enableDefaultLanguageUrlCode' => true,
```

### Language code case

Control case handling for language codes in URL.

```php
// Default: convert to lowercase (recommended)
'keepUppercaseLanguageCode' => false,  // /de-at/page
// Alternative: keep the uppercase region
'keepUppercaseLanguageCode' => true,   // /de-AT/page
```

With the default, `/de-AT/page` redirects to `/de-at/page`, and `/ES-MX/page` with `es-*` redirects to `/es-mx/page`.
A language configured with a lowercase region, such as `de-at`, applies on `/de-at/page` without a redirect.

A spelling that differs from its lowercase form redirects to the lowercase form of the alias or code used in the URL:
with `['de', 'deutsch' => 'de']`, `/Deutsch/page` redirects to `/deutsch/page` and `/DE/page` redirects to `/de/page`.

## Detection and persistence

### Language detection

Enable/disable automatic language detection.

```php
// Default: detect from the `Accept-Language` header
'enableLanguageDetection' => true,
// Disable header detection (GeoIP detection still applies)
'enableLanguageDetection' => false,
```

Detection priority order.

1. URL language code.
2. Session persisted language.
3. Cookie persisted language.
4. Browser `Accept-Language` headers.
5. GeoIP detection (if configured, regardless of `enableLanguageDetection`).

### Language persistence

Control language persistence in session and cookies.

```php
// Default: persist language choice
'enableLanguagePersistence' => true,
// Disable persistence
'enableLanguagePersistence' => false,
```

Before a redirect, the manager converts a persisted or detected language to its configured form, the language or the
language and its region: with `['en', 'es']`, a stale `es-MX` cookie redirects to `/es/...`. A value that matches no
configured language causes no redirect.

### Session configuration

Configure session-based language persistence.

```php
// Default session key
'languageSessionKey' => '_language',
// Custom session key
'languageSessionKey' => 'user_lang',
// Disable session persistence
'languageSessionKey' => false,
```

While `languageSessionKey` is a string, every request without a language code reads the session, which starts a
session and sends its cookie even for visitors that had none. Set it to `false` to persist the language in the cookie
only.

### Cookie configuration

Configure cookie-based language persistence.

```php
// Default cookie name
'languageCookieName' => '_language',
// 30 days (default)
'languageCookieDuration' => 2592000,
// Additional cookie options
'languageCookieOptions' => [
    'httpOnly' => true,
    'secure' => true,
    'sameSite' => 'Lax',
    'domain' => '.example.com',
],
```

To disable cookie persistence.

```php
// Disable cookie persistence
'languageCookieDuration' => 0,
```

## GeoIP detection

### Configuration

Configure automatic language detection based on visitor's country.

```php
// Default header name
'geoIpServerVar' => 'HTTP_X_GEO_COUNTRY',
'geoIpLanguageCountries' => [
    // German for Germany, Austria
    'de' => ['DEU', 'AUT'],
    // French for France
    'fr' => ['FRA'],
    // English for US, UK
    'en' => ['USA', 'GBR'],
    // Spanish for multiple countries
    'es' => ['ESP', 'MEX', 'ARG'],
],
```

Country values must match the GeoIP provider exactly. For example, Cloudflare uses two-letter codes such as
`DE` and `US`, so configure those values instead of `DEU` and `USA` when using `CF-IPCountry`.

### Custom header

If using a different GeoIP service.

```php
// CloudFlare
'geoIpServerVar' => 'HTTP_CF_IPCOUNTRY',
// Custom header
'geoIpServerVar' => 'HTTP_X_COUNTRY_CODE',
// Server variable, for example from mod_geoip
'geoIpServerVar' => 'GEOIP_COUNTRY_CODE',
```

The manager reads `geoIpServerVar` through the request:

- A name that starts with `HTTP_` is read from `Request::getHeaders()` as the header it represents
  (`HTTP_X_GEO_COUNTRY` is the `X-Geo-Country` header), never from the server parameters.
- Any other name is read from the server parameters of the request: `getServerParams()` when the request provides it,
  as under `psr-bridge`, otherwise `$_SERVER`.

Yii builds the request headers from `getallheaders()` when PHP provides it. Behind Apache or PHP-FPM, a value that the
web server sets only as a server variable with an `HTTP_` name, for example with `SetEnvIf` or
`fastcgi_param HTTP_X_GEO_COUNTRY`, may therefore be missing from the headers. Send the country as a real request
header, or set a server variable without the `HTTP_` prefix, such as `GEOIP_COUNTRY_CODE`.

## URL patterns and rules

### Ignore patterns

Exclude specific routes and URLs from language processing.

```php
'rules' => [
    'login' => 'site/login',
    'register' => 'site/register',
],
'ignoreLanguageUrlPatterns' => [
    // Routes `api/...`, created and requested as the URLs `api/...`
    '#^api/#' => '#^api/#',
    // Debug toolbar and debugger of `yiisoft/yii2-debug`
    '#^debug/#' => '#^debug/#',
    // Routes `site/login` and `site/register`, created as the URLs `login` and `register` by the rules above
    '#^site/(login|register)#' => '#^(login|register)#',
],
```

Pattern format: `'route_pattern' => 'url_pattern'`.

- Route patterns (keys) match the route in `createUrl()`, which then adds no language code.
- URL patterns (values) match the path info of the request in `parseRequest()`, which then resolves no language.

Each URL pattern must match the URLs that the URL rules create for the routes of its key. When they disagree, for
example with the `site/login` pattern above but without the `login` rule, `createUrl()` produces `/site/login` without
a code, `parseRequest()` does not ignore it, and a language redirect targets `/site/login?language=de` instead of a
language URL.

Ignored URLs resolve no language: they run in the default language, without the URL code, the persisted language, or
detection.

### Custom URL rules

Combine with standard Yii URL rules.

```php
'rules' => [
    '' => 'site/index',
    '/custom' => 'test/action',
    '/slug/<name>' => 'test/slug',
    [
        'pattern' => '/slash',
        'route' => 'test/slash',
        'suffix' => '/',
    ],
    '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
],
```

A catch-all rule such as `'<slug:.+>' => 'page/view'` encodes the `/` of its parameter as `%2F`. Set
`'encodeParams' => false` on the rule to keep the slashes.

### URL suffixes

Configure URL suffixes for all routes.

```php
// Add a trailing slash to every URL
'suffix' => '/',
// No suffix (default)
'suffix' => '',
```

Individual rules can override the global suffix.

```php
'rules' => [
    [
        'pattern' => '/noslash',
        'route' => 'test/noslash',
        // Override global suffix
        'suffix' => '',
    ],
],
```

## Advanced configuration

### URL parameter

Customize the language parameter name used in URL generation.

```php
// Default parameter name
'languageParam' => 'language',
// Custom parameter name
'languageParam' => 'lang',
```

Usage.

```php
// Default
Url::to(['site/index', 'language' => 'de']);
// Custom
Url::to(['site/index', 'lang' => 'de']);
```

### Redirect status code

Configure the HTTP status code for language redirects.

```php
// Default: temporary redirect
'languageRedirectCode' => 302,
// Permanent redirect
'languageRedirectCode' => 301,
```

A redirect required only by the URL normalizer keeps the normalizer status code (`301` by default).

### Redirect URLs

A language redirect keeps the route and the query parameters of the request, numeric keys included:
`/en/site/page?105=1&5=1`, with `en` as the default language, redirects to `/site/page?105=1&5=1`. The request query
parameter named by `languageParam` is dropped, so it never selects the redirect language or reaches the redirect URL:
`/en/site/page?language=fr` redirects to `/site/page`.

A custom URL rule may throw `yii\web\UrlNormalizerRedirectException` itself:

- A string URL, relative or absolute, propagates unchanged, whether or not a language redirect is pending.
- An array URL (`[route, ...params]`) is created through the manager, so it carries the language code of the request,
  or the pending language when the request also needs a language redirect, and both become one redirect.

A request whose URL lacks the code of the language that the manager selects is redirected whatever its HTTP method.
Browsers repeat a `POST` request as a `GET` after a `301` or `302` redirect, so the request body is lost, and an AJAX
request receives Yii's `X-Redirect` header instead of `Location`. Create form actions and AJAX URLs with `Url::to()`,
or exclude them with [ignore patterns](#ignore-patterns).

### URL normalizer integration

Combine with Yii URL normalizer. Extra slashes after the language code remain available to the normalizer,
including its redirect status or `ACTION_NOT_FOUND`. URL rules retain their own normalizer settings. Without a
normalizer, the remaining path is parsed with its extra slashes instead of silently removing them.

```php
<?php

declare(strict_types=1);

use yii\web\UrlNormalizer;
use yii2\extensions\localeurls\UrlLanguageManager;

return [
    'components' => [
        'urlManager' => [
            'class' => UrlLanguageManager::class,
            'languages' => ['en', 'de'],
            'normalizer' => [
                'class' => UrlNormalizer::class,
                'action' => UrlNormalizer::ACTION_REDIRECT_TEMPORARY,
            ],
        ],
    ],
];
```

## Event configuration

### Language changed event

React to language changes with event handlers.

```php
<?php

declare(strict_types=1);

use yii2\extensions\localeurls\{LanguageChangedEvent, UrlLanguageManager};

return [
    'components' => [
        'urlManager' => [
            'class' => UrlLanguageManager::class,
            'languages' => ['en', 'de', 'fr'],
            'on languageChanged' => static function (LanguageChangedEvent $event) {
                $oldLang = $event->oldLanguage;
                $newLang = $event->language;

                // Log language change
                Yii::info("Language changed from {$oldLang} to {$newLang}");

                // Update user preferences
                if (Yii::$app->user->isGuest === false) {
                    Yii::$app->user->identity->language = $newLang;
                    Yii::$app->user->identity->save();
                }
            },
        ],
    ],
];
```

### Event handler class

Alternative event handler configuration.

```php
'on languageChanged' => [\app\handlers\LanguageHandler::class, 'handleChange'],
```

## Specialized configurations

### API only application

For applications that only serve API.

```php
<?php

declare(strict_types=1);

use yii2\extensions\localeurls\UrlLanguageManager;

return [
    'components' => [
        'urlManager' => [
            'class' => UrlLanguageManager::class,
            'languages' => ['en', 'de'],
            'ignoreLanguageUrlPatterns' => [
                // Skip all API routes
                '#^api/#' => '#^api/#',
            ],
        ],
    ],
];
```

### Subdirectory installation

For applications installed in subdirectories.

```php
<?php

declare(strict_types=1);

use yii2\extensions\localeurls\UrlLanguageManager;

return [
    'components' => [
        'urlManager' => [
            'class' => UrlLanguageManager::class,
            'languages' => ['en', 'de'],
            // Application subdirectory
            'baseUrl' => '/myapp',
        ],
    ],
];
```

`baseUrl` is a path. To create absolute URLs for a specific host, set `hostInfo`, for example
`'hostInfo' => 'https://example.com'`; an absolute `baseUrl` is not supported. `createUrl()` treats a URL as absolute
only when it starts with a scheme, such as `https://`, so a relative URL whose path contains `://` gets its language
code after the base URL like any other URL.

### Script name visible

For environments where `index.php` must be visible.

```php
<?php

declare(strict_types=1);

use yii2\extensions\localeurls\UrlLanguageManager;

return [
    'components' => [
        'urlManager' => [
            'class' => UrlLanguageManager::class,
            'languages' => ['en', 'de'],
            // Show `index.php` in URL
            'showScriptName' => true,
        ],
    ],
];
```

## Worker mode

The manager runs unchanged in long-running workers, such as
[`yii2-extensions/psr-bridge`](https://github.com/yii2-extensions/psr-bridge) under FrankenPHP or RoadRunner.
`psr-bridge` creates new `request`, `response`, `session`, and `urlManager` components for every request; the rules
below cover the state that outlives a request: the application language, PHP's session, class-level event handlers,
and the server environment of the runtime.

### Redirects and requests

- Redirects are thrown from `parseRequest()` as `yii\web\UrlNormalizerRedirectException`, and
  `yii\web\Application::handleRequest()` turns them into a redirect response. The manager never calls `exit()`.
  String redirect URLs thrown by custom URL rules propagate unchanged; see [Redirect URLs](#redirect-urls).
- The manager keeps no request state: every step works on the request being parsed. Each `Request` object is processed
  once, and later `parseRequest()` calls only parse it. `psr-bridge` creates a new `Request` for every request; a
  runner or test that mutates and reuses one `Request` instance gets no language processing after its first request.
- Do not list `urlManager` in `persistentComponents`: it is a core component, and `psr-bridge` would rebuild it as a
  plain `yii\web\UrlManager`.

### Application language

Set `language` explicitly in the application configuration, or set [`defaultLanguage`](#default-language) on the
manager. The application is reconfigured for every request, and a configuration without `language` keeps the language
of the previous request, which also becomes the default language of the new manager. `defaultLanguage` resets
`Yii::$app->language` at the start of every request and does not depend on the application language when the manager
is created.

### Sessions

- Keep the `psr-bridge` `useSession` flag at `true`, its default. The bridge then opens PHP's session with the session
  cookie of each request and closes it afterwards. With `useSession` set to `false`, nothing resets PHP's session state
  between requests, and on RoadRunner later visitors receive the session id and the session data of earlier requests.
  If the application must run with `useSession` set to `false`, set `languageSessionKey` to `false` so the manager
  never opens a session.
- Call `ini_set('session.use_cookies', '0')` in the entry script before the application runs. `psr-bridge` emits the
  session cookie itself; without this setting, PHP adds its own `Set-Cookie: PHPSESSID` header under FrankenPHP.
- While `languageSessionKey` is a string, the manager reads the session on every request without a language code. A
  plain `yii\web\UrlManager` starts no session, but with this manager anonymous visitors, bots, and health checks get a
  `PHPSESSID` cookie and, under FrankenPHP, PHP's `no-store` cache headers. Set `languageSessionKey` to `false` to
  persist the language in the cookie only and keep these responses stateless.

### Cookies

With cookie validation enabled, the Yii default, set `cookieValidationKey` on the `request` component. `psr-bridge`
signs the `_language` cookie and ignores unsigned values, so a `_language` cookie written before validation was enabled
is not read. A missing key throws `yii\base\InvalidConfigException` on the first cookie read.

### HTTPS redirects

`yii\web\Response::redirect()` makes the redirect URL absolute with the host info of the request. Behind a
TLS-terminating proxy, set `trustedHosts` on the `request` component to the proxy addresses, so that
`X-Forwarded-Proto: https` marks the request as secure and redirect URLs start with `https://`. FrankenPHP sets
`$_SERVER['HTTPS']` for HTTPS requests; RoadRunner does not.

### Event handlers

Attach `languageChanged` handlers in the `urlManager` configuration (`'on languageChanged' => ...`), which attaches
them again to the manager of every request. `psr-bridge` calls `yii\base\Event::offAll()` after each request, so a
class-level handler attached with `Event::on(UrlLanguageManager::class, ...)` fires for the first request only.

### Server variables and runtimes

The manager reads headers, cookies, and server parameters only through the `Request` object: `getHeaders()`,
`getCookies()`, and `getServerParams()` when the request provides it. It falls back to `$_SERVER` only for a GeoIP
name without the `HTTP_` prefix on a request without `getServerParams()`, such as a plain `yii\web\Request`.

RoadRunner refreshes `$_SERVER` for every request through spiral's `PSR7Worker`, leaves `$_COOKIE` empty, and has no
`getallheaders()`. FrankenPHP refreshes `$_SERVER`, `$_COOKIE`, and `getallheaders()` for every request. Under
`psr-bridge`, the request reads headers, cookies, and server parameters from the PSR-7 request, so the manager behaves
the same on both runtimes.

### Example configuration

```php
<?php

declare(strict_types=1);

use yii2\extensions\localeurls\{LanguageChangedEvent, UrlLanguageManager};
use yii2\extensions\psrbridge\http\Request;

return [
    'components' => [
        'request' => [
            'class' => Request::class,
            // Signs the `_language` cookie; keep the real key out of version control.
            'cookieValidationKey' => 'replace-with-a-long-random-secret',
            // Proxy addresses whose `X-Forwarded-Proto` header marks HTTPS requests.
            'trustedHosts' => ['10.0.0.0/8'],
        ],
        'urlManager' => [
            'class' => UrlLanguageManager::class,
            // Applied to `Yii::$app->language` at the start of every request.
            'defaultLanguage' => 'en',
            'languages' => ['en', 'de', 'fr'],
            // Stateless option: persist the language in the cookie only and never open a session.
            'languageSessionKey' => false,
            'showScriptName' => false,
            // Attached again for every request, unlike class-level `Event::on()` handlers.
            'on languageChanged' => static function (LanguageChangedEvent $event): void {
                Yii::info("Language changed from {$event->oldLanguage} to {$event->language}.");
            },
        ],
    ],
];
```

[Back to documentation](../README.md#documentation)
