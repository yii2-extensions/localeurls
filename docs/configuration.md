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
    'en-US', // Specific locale
    'en',    // Language only
    'de',    // German
    'pt',    // Portuguese
],
```

### Language aliases

Map custom URL segments to language codes.

```php
'languages' => [
    'en-US',
    'en', 
    'deutsch' => 'de', // /deutsch/page → German (de)
    'french' => 'fr',  // /french/page → French (fr)
    'at' => 'de-AT',   // /at/page → Austrian German (de-AT)
],
```

### Wildcard language support

Support language variants with wildcards.

```php
'languages' => [
    'en-US',
    'en',
    'de-*', // Matches: de-AT, de-CH, de-DE, etc.
    'es-*', // Matches: es-MX, es-AR, es-ES, etc.
    'wc-*', // Custom wildcard pattern
],
```

## URL code configuration

### Default language URL code

Control whether the default language appears in the URL.

```php
// Default behavior: /page (default), /de/page (German)
'enableDefaultLanguageUrlCode' => false,

// Alternative: /en/page (default), /de/page (German)  
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
'enableLanguageDetection' => true,  // Default: detect from headers/GeoIP
'enableLanguageDetection' => false, // Disable automatic detection
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
'enableLanguagePersistence' => true,  // Default: persist language choice
'enableLanguagePersistence' => false, // Disable persistence
```

### Session configuration

Configure session-based language persistence.

```php
'languageSessionKey' => '_language', // Default session key
'languageSessionKey' => 'user_lang', // Custom session key
'languageSessionKey' => false,       // Disable session persistence
```

### Cookie configuration

Configure cookie-based language persistence.

```php
'languageCookieName' => '_language', // Default cookie name
'languageCookieDuration' => 2592000, // 30 days (default)
'languageCookieOptions' => [         // Additional cookie options
    'httpOnly' => true,
    'secure' => true,
    'sameSite' => 'Lax',
    'domain' => '.example.com',
],
```

To disable cookie persistence.

```php
'languageCookieDuration' => false, // Disable cookie persistence
```

## GeoIP detection

### Configuration

Configure automatic language detection based on visitor's country.

```php
'geoIpServerVar' => 'HTTP_X_GEO_COUNTRY', // Default header name
'geoIpLanguageCountries' => [
    'de' => ['DEU', 'AUT'],               // German for Germany, Austria
    'fr' => ['FRA'],                      // French for France
    'en' => ['USA', 'GBR'],               // English for US, UK
    'es' => ['ESP', 'MEX', 'ARG'],        // Spanish for multiple countries
],
```

### Custom header

If using a different GeoIP service.

```php
'geoIpServerVar' => 'HTTP_CF_IPCOUNTRY',   // CloudFlare
'geoIpServerVar' => 'HTTP_X_COUNTRY_CODE', // Custom header
```

## URL patterns and rules

### Ignore patterns

Exclude specific URLs from language processing.

```php
'ignoreLanguageUrlPatterns' => [
    '#^api/#' => '#^api/#',                              // Skip API routes
    '#^admin/#' => '#^admin/#',                          // Skip admin routes  
    '#^site/(login|register)#' => '#^(login|register)#', // Skip auth pages
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
'suffix' => '/', // Add trailing slash to all URL
'suffix' => '',  // No suffix (default)
```

Individual rules can override the global suffix.

```php
'rules' => [
    [
        'pattern' => '/noslash',
        'route' => 'test/noslash', 
        'suffix' => '', // Override global suffix
    ],
],
```

## Advanced configuration

### URL parameter

Customize the language parameter name used in URL generation.

```php
'languageParam' => 'language', // Default parameter name
'languageParam' => 'lang',     // Custom parameter name
```

Usage.

```php
Url::to(['site/index', 'language' => 'de']); // Default
Url::to(['site/index', 'lang' => 'de']);     // Custom
```

### Redirect status code

Configure the HTTP status code for language redirects.

```php
'languageRedirectCode' => 302, // Default: temporary redirect
'languageRedirectCode' => 301, // Permanent redirect
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
                '#^api/#' => '#^api/#', // Skip all API routes
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
            'baseUrl' => '/myapp', // Application subdirectory
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
            'showScriptName' => true, // Show `index.php` in URL
        ],
    ],
];
```

## Next steps

- 💡 [Usage Examples](examples.md)
