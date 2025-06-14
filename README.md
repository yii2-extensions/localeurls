<p align="center">
    <a href="https://github.com/yii2-extensions/localeurls" target="_blank">
        <img src="https://www.yiiframework.com/image/yii_logo_light.svg" height="100px;">
    </a>
    <h1 align="center">Locale URLs</h1>
    <br>
</p>

<p align="center">
    <a href="https://www.php.net/releases/8.1/en.php" target="_blank">
        <img src="https://img.shields.io/badge/PHP-%3E%3D8.1-787CB5" alt="PHP-Version">
    </a>
    <a href="https://github.com/yiisoft/yii2/tree/2.0.52" target="_blank">
        <img src="https://img.shields.io/badge/Yii2%20-2.0.52-blue" alt="Yii-22.0.52">
    </a>
    <a href="https://github.com/yiisoft/yii2/tree/22.0" target="_blank">
        <img src="https://img.shields.io/badge/Yii2%20-22-blue" alt="Yii2-22">
    </a>
    <a href="https://github.com/yii2-extensions/localeurls/actions/workflows/build.yml" target="_blank">
        <img src="https://github.com/yii2-extensions/localeurls/actions/workflows/build.yml/badge.svg" alt="PHPUnit">
    </a>
    <a href="https://dashboard.stryker-mutator.io/reports/github.com/yii2-extensions/localeurls/main" target="_blank">
        <img src="https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyii2-extensions%2Flocaleurls%2Fmain" alt="Mutation-Testing">
    </a>    
    <a href="https://github.com/yii2-extensions/localeurls/actions/workflows/static.yml" target="_blank">        
        <img src="https://github.com/yii2-extensions/localeurls/actions/workflows/static.yml/badge.svg" alt="Static-Analysis">
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

## Configuration

To use this extension, you need to configure the `urlManager` component in your application configuration file. 

```php
'components' => [
    'urlManager' => [
        'class' => yii2\extensions\localeurls\UrlLanguageManager::class,
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

## Quality code

[![phpstan-level](https://img.shields.io/badge/PHPStan%20level-max-blue)](https://github.com/yii2-extensions/localeurls/actions/workflows/static.yml)
[![StyleCI](https://github.styleci.io/repos/711867018/shield?branch=main)](https://github.styleci.io/repos/711867018?branch=main)

## Testing

[Check the documentation testing](/docs/testing.md) to learn about testing.

## Our social networks

[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/Terabytesoftw)

## License

BSD-3-Clause license. Please see [License File](LICENSE.md) for more information.

## Fork 

This package is a fork of [https://github.com/codemix/yii2-localeurls](https://github.com/codemix/yii2-localeurls) with some corrections.
