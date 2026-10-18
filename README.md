<!-- markdownlint-disable MD041 -->
<p align="center">
    <picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://www.yiiframework.com/image/design/logo/yii3_full_for_dark.svg">
        <source media="(prefers-color-scheme: light)" srcset="https://www.yiiframework.com/image/design/logo/yii3_full_for_light.svg">
        <img src="https://www.yiiframework.com/image/design/logo/yii3_full_for_dark.svg" alt="Yii Framework" width="80%">
    </picture>
    <h1 align="center">Locale URLs</h1>
    <br>
</p>
<!-- markdownlint-enable MD041 -->

<p align="center">
    <a href="https://github.com/yii2-extensions/localeurls/actions/workflows/build.yml" target="_blank">
        <img src="https://img.shields.io/github/actions/workflow/status/yii2-extensions/localeurls/build.yml?style=for-the-badge&label=PHPUnit&logo=github" alt="PHPUnit">
    </a>
    <a href="https://github.com/yii2-extensions/localeurls/actions/workflows/static.yml" target="_blank">
        <img src="https://img.shields.io/github/actions/workflow/status/yii2-extensions/localeurls/static.yml?style=for-the-badge&label=PHPStan&logo=github" alt="PHPStan">
    </a>
    <a href="https://github.com/yii2-extensions/localeurls/actions/workflows/security.yml" target="_blank">
        <img src="https://img.shields.io/github/actions/workflow/status/yii2-extensions/localeurls/security.yml?style=for-the-badge&label=Security&logo=github" alt="Security">
    </a>
</p>

<p align="center">
    <strong>Language detection, persistence, and locale-aware URLs for Yii2 applications</strong>
</p>

## Features

<picture>
    <source media="(min-width: 768px)" srcset="./docs/svgs/features.svg">
    <img src="./docs/svgs/features-mobile.svg" alt="Feature Overview" style="width: 100%;">
</picture>

Worker-mode requirements (sessions, cookies, event handlers) are listed in the
[configuration guide](docs/configuration.md#worker-mode).

### Installation

```bash
composer require yii2-extensions/localeurls:^0.2
```

To use the development line, require `^0.2@dev`.

### Quick start

#### How it works

1. Detects the language from the URL path (`/es/about` sets Spanish).
2. Falls back to the persisted language, then to browser headers or GeoIP, then to the default language.
3. Adds the language code to generated URLs, except for the default language (see `enableDefaultLanguageUrlCode`).
4. Remembers the choice in session and cookie.

#### Basic configuration

Replace the `urlManager` component in `config/web.php`. The application language is the default language, which has
no code in the URL; the `defaultLanguage` property of the manager sets it explicitly.

```php
<?php

declare(strict_types=1);

use yii2\extensions\localeurls\UrlLanguageManager;

return [
    'language' => 'en',
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

#### Automatic URL generation

```php
<?php

declare(strict_types=1);

use yii\helpers\Url;

// Current language `en`, the default language.
Url::to(['site/about']); // /site/about
// Current language `es`, set by a request such as /es/site/index.
Url::to(['site/about']); // /es/site/about
// Another language.
Url::to(['site/contact', 'language' => 'fr']); // /fr/site/contact
```

## Documentation

Practical guides and examples for language-aware URLs and worker isolation.

- 📚 [Installation Guide](docs/installation.md)
- ⚙️ [Configuration Guide](docs/configuration.md), including
  [worker mode](docs/configuration.md#worker-mode)
- 💡 [Usage Examples](docs/examples.md)
- 🔄 [Upgrade Guide](UPGRADE.md)
- 🧪 [Testing Guide](docs/testing.md)

## AI assistance

AI agents: use the repository guides and source for the installed revision.
For a [documentation deployment](https://yii2-framework.github.io/docs/) with
`llms.txt`, read that index first. When Markdown export is enabled, append
`index.md` to a page URL. For Yii conventions, use the `yii2-framework` skill
from a local framework documentation checkout.

- 🤖 [yii2-localeurls-configuration skill](docs/agents.md): installation for
  Claude Code, Codex, and OpenCode.

If the hosted sources are unavailable, use the repository guides and locally available skills.

## Package information

[![PHP](https://img.shields.io/badge/%3E%3D8.3-777BB4.svg?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/releases/8.3/en.php)
[![Yii 2.0.x](https://img.shields.io/badge/2.0.55+-0073AA.svg?style=for-the-badge&logo=yii&logoColor=white)](https://packagist.org/packages/yiisoft/yii2)
[![Yii 22.0.x](https://img.shields.io/badge/22.0.x-0073AA.svg?style=for-the-badge&logo=yii&logoColor=white)](https://github.com/yiisoft/yii2/tree/22.0)
[![Latest Stable Version](https://img.shields.io/packagist/v/yii2-extensions/localeurls.svg?style=for-the-badge&logo=packagist&logoColor=white&label=Stable)](https://packagist.org/packages/yii2-extensions/localeurls)
[![Total Downloads](https://img.shields.io/packagist/dt/yii2-extensions/localeurls.svg?style=for-the-badge&logo=composer&logoColor=white&label=Downloads)](https://packagist.org/packages/yii2-extensions/localeurls)

## Project status

[![Codecov](https://img.shields.io/codecov/c/github/yii2-extensions/localeurls.svg?style=for-the-badge&logo=codecov&logoColor=white&label=Coverage)](https://codecov.io/github/yii2-extensions/localeurls)
[![PHPStan Level Max](https://img.shields.io/badge/PHPStan-Level%20Max-4F5D95.svg?style=for-the-badge&logo=github&logoColor=white)](https://github.com/yii2-extensions/localeurls/actions/workflows/static.yml)
[![Quality](https://img.shields.io/github/actions/workflow/status/yii2-extensions/localeurls/quality.yml?style=for-the-badge&label=Quality&logo=github)](https://github.com/yii2-extensions/localeurls/actions/workflows/quality.yml)
[![StyleCI](https://img.shields.io/badge/StyleCI-Passed-44CC11.svg?style=for-the-badge&logo=github&logoColor=white)](https://github.styleci.io/repos/711867018?branch=main)

## Our social networks

[![Follow on X](https://img.shields.io/badge/-Follow%20on%20X-1DA1F2.svg?style=for-the-badge&logo=x&logoColor=white&labelColor=000000)](https://x.com/Terabytesoftw)
[![Follow on Facebook](https://img.shields.io/badge/-Follow%20on%20Facebook-1877F2.svg?style=for-the-badge&logo=facebook&logoColor=white&labelColor=000000)](https://www.facebook.com/wilmer.arambula.9)
[![Join our Subreddit](https://img.shields.io/badge/-Join%20our%20Subreddit-FF4500.svg?style=for-the-badge&logo=reddit&logoColor=white&labelColor=000000)](https://www.reddit.com/r/Yii2/)
[![Join on Telegram](https://img.shields.io/badge/-Join%20on%20Telegram-26A5E4.svg?style=for-the-badge&logo=telegram&logoColor=white&labelColor=000000)](https://t.me/yii_framework_in_english)

## Credits

This package started as a fork of [codemix/yii2-localeurls](https://github.com/codemix/yii2-localeurls).

## License

[![License](https://img.shields.io/badge/License-BSD--3--Clause-brightgreen.svg?style=for-the-badge&logo=opensourceinitiative&logoColor=white&labelColor=555555)](LICENSE)
