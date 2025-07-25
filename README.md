<p align="center">
    <a href="https://github.com/yii2-extensions/localeurls" target="_blank">
        <img src="https://www.yiiframework.com/image/yii_logo_light.svg" alt="Yii Framework">
    </a>
    <h1 align="center">Locale URLs</h1>
    <br>
</p>

<p align="center">
    <a href="https://www.php.net/releases/8.1/en.php" target="_blank">
        <img src="https://img.shields.io/badge/PHP-%3E%3D8.1-787CB5" alt="PHP Version">
    </a>
    <a href="https://github.com/yiisoft/yii2/tree/2.0.53" target="_blank">
        <img src="https://img.shields.io/badge/Yii2%20-2.0.53-blue" alt="Yii2 2.0.53">
    </a>
    <a href="https://github.com/yiisoft/yii2/tree/22.0" target="_blank">
        <img src="https://img.shields.io/badge/Yii2%20-22-blue" alt="Yii2 22.0">
    </a>
    <a href="https://github.com/yii2-extensions/localeurls/actions/workflows/build.yml" target="_blank">
        <img src="https://github.com/yii2-extensions/localeurls/actions/workflows/build.yml/badge.svg" alt="PHPUnit">
    </a>
    <a href="https://dashboard.stryker-mutator.io/reports/github.com/yii2-extensions/localeurls/main" target="_blank">
        <img src="https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyii2-extensions%2Flocaleurls%2Fmain" alt="Mutation Testing">
    </a>    
    <a href="https://github.com/yii2-extensions/localeurls/actions/workflows/static.yml" target="_blank">        
        <img src="https://github.com/yii2-extensions/localeurls/actions/workflows/static.yml/badge.svg" alt="Static Analysis">
    </a>  
</p>

A powerful URL manager extension that provides transparent language detection, persistence, and locale-aware URL
generation for Yii applications.

Create SEO-friendly multilingual URLs with automatic language switching, GeoIP detection, and comprehensive fallback 
mechanisms.

## Features

- ✅ **Automatic Language Detection** - From URL, browser headers, session, or GeoIP.
- ✅ **Flexible Configuration** - Supports language aliases, wildcards, and custom mappings.
- ✅ **Language Persistence** - Remembers user's language choice.
- ✅ **SEO-Friendly URLs** - Clean URLs like `/en/about` or `/es/acerca`.

## Quick start

### Installation

```bash
composer require yii2-extensions/localeurls
```

### How it works

1. **Detects language** from URL path (`/es/about` → Spanish).
2. **Falls back** to browser headers, session, or GeoIP.
3. **Adds language prefix** to all generated URLs.
4. **Remember choice** in session and cookie.

### Basic Configuration

Replace your `urlManager` component in `config/web.php`.

```php
<?php

declare(strict_types=1);

use yii2\extensions\localeurls\UrlLanguageManager;

return [
    'components' => [
        'urlManager' => [
            'class' => UrlLanguageManager::class,
            'languages' => ['en', 'es', 'fr', 'de'],
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'site/index',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ],
        ],
    ],
];
```

### Basic Usage

#### Automatic URL generation

```php
<?php

declare(strict_types=1);

use yii\helpers\Url;

// URL are automatically localized based on the current language

// /en/ (if current language is 'en')
Url::to(['site/index']);
// /es/site/about (if current language is 'es')
Url::to(['site/about']);
// Force specific language
Url::to(['site/contact', 'language' => 'fr']); // /fr/site/contact
```

#### Language switching

```php
<?php

declare(strict_types=1);

use yii\helpers\{Html, Url};

// Create language switcher links
foreach (Yii::$app->urlManager->languages as $language) {
    echo Html::a(
        strtoupper($language),
        Url::current(['language' => $language]),
    );
}
```

#### Current language access

```php
<?php

declare(strict_types=1);

// Get current language
$currentLang = Yii::$app->language;
// Get default language
$defaultLang = Yii::$app->urlManager->getDefaultLanguage();
```

## Documentation

For detailed configuration options and advanced usage patterns.

- 📚 [Installation Guide](docs/installation.md)
- ⚙️ [Configuration Reference](docs/configuration.md) 
- 💡 [Usage Examples](docs/examples.md)
- 🧪 [Testing Guide](docs/testing.md)

## Quality code

[![Latest Stable Version](https://poser.pugx.org/yii2-extensions/localeurls/v)](https://github.com/yii2-extensions/localeurls/releases)
[![Total Downloads](https://poser.pugx.org/yii2-extensions/localeurls/downloads)](https://packagist.org/packages/yii2-extensions/localeurls)
[![codecov](https://codecov.io/gh/yii2-extensions/localeurls/graph/badge.svg?token=lYVGC7ZVCu)](https://codecov.io/gh/yii2-extensions/localeurls)
[![phpstan-level](https://img.shields.io/badge/PHPStan%20level-max-blue)](https://github.com/yii2-extensions/localeurls/actions/workflows/static.yml)
[![StyleCI](https://github.styleci.io/repos/711867018/shield?branch=main)](https://github.styleci.io/repos/711867018?branch=main)

## Our social networks

[![X](https://img.shields.io/badge/follow-@terabytesoftw-1DA1F2?logo=x&logoColor=1DA1F2&labelColor=555555&style=flat)](https://x.com/Terabytesoftw)

## License

[![License](https://img.shields.io/github/license/yii2-extensions/localeurls)](LICENSE.md)

## Fork 

This package is a fork of [https://github.com/codemix/yii2-localeurls](https://github.com/codemix/yii2-localeurls) with some corrections.
