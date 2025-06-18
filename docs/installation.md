# Installation guide

## System requirements

- [`PHP`](https://www.php.net/downloads) 8.1 or higher.
- [`PHPStan`](https://github.com/phpstan/phpstan) 2.1 or higher.
- [`Yii2`](https://github.com/yiisoft/yii2) 2.0.52+ or 22.x.

## Installation

### Method 1: Using [Composer](https://getcomposer.org/download/) (recommended)

Install the extension.

```bash
composer require yii2-extensions/localeurls
```

### Method 2: Manual installation

Add to your `composer.json`.

```json
{
    "require": {
        "yii2-extensions/localeurls": "^0.1"
    }
}
```

Then run.

```bash
composer update
```

## Next steps

Once installation is complete:

- ⚙️ [Configuration Reference](configuration.md)
- 💡 [Usage Examples](examples.md)
- 🧪 [Testing Guide](testing.md)
