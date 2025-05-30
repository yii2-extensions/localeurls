<p align="center">
    <a href="https://github.com/yii2-extensions/localeurls" target="_blank">
        <img src="https://www.yiiframework.com/image/yii_logo_light.svg" height="100px;">
    </a>
    <h1 align="center">Locale URLs</h1>
    <br>
</p>

<p align="center">
    <a href="https://www.php.net/releases/8.1/en.php" target="_blank">
        <img src="https://img.shields.io/badge/PHP-%3E%3D8.1-787CB5" alt="php-version">
    </a>
    <a href="https://github.com/yii2-extensions/localeurls/actions/workflows/build.yml" target="_blank">
        <img src="https://github.com/yii2-extensions/localeurls/actions/workflows/build.yml/badge.svg" alt="PHPUnit">
    </a>
    <a href="https://github.com/yii2-extensions/localeurls/actions/workflows/compatibility.yml" target="_blank">
        <img src="https://github.com/yii2-extensions/localeurls/actions/workflows/compatibility.yml/badge.svg" alt="PHPUnit">
    </a>    
    <a href="https://codecov.io/gh/yii2-extensions/localeurls" target="_blank">
        <img src="https://codecov.io/gh/yii2-extensions/localeurls/graph/badge.svg?token=hLDHtLBgqV" alt="Codecov">
    </a>      
</p>

## Installation

The preferred way to install this extension is through [composer](https://getcomposer.org/download/).

Either run

```
composer require --dev --prefer-dist yii2-extensions/localeurls
```

or add

```
"yii2-extensions/localeurls": "dev-main"
```

to the require-dev section of your `composer.json` file.  

## Quality code

[![static-analysis](https://github.com/yii2-extensions/localeurls/actions/workflows/static.yml/badge.svg)](https://github.com/yii2-extensions/localeurls/actions/workflows/static.yml)
[![phpstan-level](https://img.shields.io/badge/PHPStan%20level-5-blue)](https://github.com/yii2-extensions/localeurls/actions/workflows/static.yml)
[![style-ci](https://github.styleci.io/repos/711867018/shield?branch=main)](https://github.styleci.io/repos/711867018?branch=main)

# Configuration

To use this extension, you need to configure the `localeUrls` component in your application configuration file. 

```php
'components' => [
    'urlManager' => [
        'class' => Yii2\Extensions\LocaleUrls\UrlLanguageManager::class,
        'languages' => [
            'en' => 'en-US',
            'es' => 'es-ES',
            'ru' => 'ru-RU',
        ],
        'enablePrettyUrl' => true,
        'showScriptName' => false,
    ],    
],
```

## Support versions Yii2

[![Yii22](https://img.shields.io/badge/Yii2%20version-22-blue)](https://github.com/yiisoft/yii2/tree/22.0)

## Testing

[Check the documentation testing](/docs/testing.md) to learn about testing.

## Our social networks

[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/Terabytesoftw)

## License

The MIT License. Please see [License File](LICENSE.md) for more information.

## Fork 

This package is a fork of [https://github.com/codemix/yii2-localeurls](https://github.com/codemix/yii2-localeurls) with some corrections.
