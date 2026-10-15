# Installation guide

## Requirements

- [PHP](https://www.php.net/downloads) 8.3 or later.
- [Yii2](https://github.com/yiisoft/yii2) `2.0.55` or a later `2.0.x` release, or the Yii2 `22.x` development line.
- [Composer](https://getcomposer.org/download/) for dependency management.

## Install the extension

```bash
composer require yii2-extensions/localeurls:^0.2
```

Until `0.2.0` is tagged, require `^0.2@dev` instead.

The package has no frontend assets and no bootstrap class: replacing the `urlManager` component, as shown in the
[configuration guide](configuration.md), enables it.
