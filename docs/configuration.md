# Configuration reference

## Overview

This guide covers all configuration options for the Yii LocaleUrls extension, from basic setup to advanced multilingual 
scenarios.

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
// Alternative: preserve an original case
'keepUppercaseLanguageCode' => true,   // /de-AT/page
```

## Detection and persistence

### Language detection

Enable/disable automatic language detection.

```php
// Default: detect from headers/GeoIP
'enableLanguageDetection' => true,
// Disable automatic detection
'enableLanguageDetection' => false,
```

Detection priority order.

1. URL language code.
2. Session persisted language.
3. Cookie persisted language.
4. Browser `Accept-Language` headers.
5. GeoIP detection (if configured).

### Language persistence

Control language persistence in session and cookies.

```php
// Default: persist language choice
'enableLanguagePersistence' => true,
// Disable persistence
'enableLanguagePersistence' => false,
```

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
'languageCookieDuration' => false,
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

### Custom header

If using a different GeoIP service.

```php
// CloudFlare
'geoIpServerVar' => 'HTTP_CF_IPCOUNTRY',
// Custom header
'geoIpServerVar' => 'HTTP_X_COUNTRY_CODE',
```

## URL patterns and rules

### Ignore patterns

Exclude specific URLs from language processing.

```php
'ignoreLanguageUrlPatterns' => [
    // Skip API routes
    '#^api/#' => '#^api/#',
    // Skip admin routes
    '#^admin/#' => '#^admin/#',
    // Skip auth pages
    '#^site/(login|register)#' => '#^(login|register)#',
],
```

Pattern format: `'route_pattern' => 'url_pattern'`.

- Route patterns: checked during URL creation
- URL patterns: checked during request processing

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

### URL suffixes

Configure URL suffixes for all routes.

```php
// Add trailing slash to all URL
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

### URL normalizer integration

Combine with Yii URL normalizer.

```php
<?php

declare(strict_types=1);

use yii\web\UrlNormalizer;

return [
    'components' => [
        'normalizer' => [
            'class' => UrlNormalizer::class,
            'action' => UrlNormalizer::ACTION_REDIRECT_TEMPORARY,
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

use yii2\extensions\localeurls\LanguageChangedEvent;
use yii2\extensions\localeurls\UrlLanguageManager;

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

## Next steps

- 💡 [Usage Examples](examples.md)
- 🧪 [Testing Guide](testing.md)
