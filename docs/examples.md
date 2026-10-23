# Usage examples

The examples assume this configuration: English is the application language and therefore the default language, which
has no code in the URL, and `de` is reachable through the `deutsch` alias.

```php
// config/web.php
use yii2\extensions\localeurls\UrlLanguageManager;

return [
    'language' => 'en',
    'components' => [
        'urlManager' => [
            'class' => UrlLanguageManager::class,
            'languages' => ['en', 'es', 'fr', 'deutsch' => 'de'],
            'showScriptName' => false,
            'rules' => [
                '' => 'site/index',
                'post/<slug>' => 'post/view',
            ],
        ],
    ],
];
```

## Creating URLs

`Url::to()`, `Url::toRoute()`, and `Html::a()` with a route go through the manager, so URLs created in a view or a
controller carry the code of the current language, except for the default language unless `enableDefaultLanguageUrlCode`
is `true`. The `language` parameter selects another language, and an alias replaces its language in the URL.

```php
use yii\helpers\Url;

// Current language `en`, the default language: no code.
echo Url::to(['/site/index']); // /
echo Url::to(['/post/view', 'slug' => 'hello']); // /post/hello

// Another language.
echo Url::to(['/post/view', 'slug' => 'hello', 'language' => 'es']); // /es/post/hello
echo Url::to(['/post/view', 'slug' => 'hello', 'language' => 'de']); // /deutsch/post/hello

// After the request /es/post/hello, the current language is `es`.
echo Url::to(['/site/index']); // /es
echo Url::to(['/post/view', 'slug' => 'bye']); // /es/post/bye

// An empty language creates a URL without a code, for example for a route excluded by
// `ignoreLanguageUrlPatterns`.
echo Url::to(['/site/index', 'language' => '']); // /
```

Passing the default language explicitly creates a URL with its code (`/en`). While persistence or detection is enabled,
that URL redirects to the URL without the code and stores the default language, so it is the way back to the default
language from another one.

## Language switcher

A switcher links the current page in every language. `Url::current()` keeps the route and the query parameters of the
request and replaces only the language:

```php
use yii\helpers\{Html, Url};

$links = [];

foreach (Yii::$app->urlManager->languages as $alias => $language) {
    // A wildcard (`es-*`) accepts regional variants; it is not a language to link.
    if (str_contains($language, '*')) {
        continue;
    }

    $links[] = Html::a(
        Html::encode(strtoupper(is_string($alias) ? $alias : $language)),
        Url::current(['language' => $language]),
        [
            'class' => $language === Yii::$app->language ? 'active' : null,
            'hreflang' => $language,
        ],
    );
}

echo Html::tag('nav', implode(' | ', $links), ['class' => 'language-switcher']);
```

On `/es/post/hello`, the links are `/en/post/hello`, `/es/post/hello`, `/fr/post/hello`, and `/deutsch/post/hello`.
Following one of them stores the choice in the session and the cookie and raises the `languageChanged` event.

## Alternate links for search engines

Declare the translations of a page in the layout with absolute URLs. The default language links the URL without a code,
its canonical form, instead of the `/en` URL that redirects:

```php
use yii\helpers\Url;

$default = Yii::$app->urlManager->getDefaultLanguage();

foreach (['en', 'es', 'fr', 'de'] as $language) {
    $this->registerLinkTag(
        [
            'rel' => 'alternate',
            'hreflang' => $language,
            'href' => Url::current(['language' => $language === $default ? '' : $language], true),
        ],
    );
}
```

On `/es/post/hello`, the `href` values are `http://example.com/post/hello`, `http://example.com/es/post/hello`,
`http://example.com/fr/post/hello`, and `http://example.com/deutsch/post/hello`.

## Current and default language

The manager sets `Yii::$app->language` from the URL before the request is routed, so controllers, views, and `Yii::t()`
use it without further configuration:

```php
$current = Yii::$app->language; // `es` on /es/post/hello
$default = Yii::$app->urlManager->getDefaultLanguage(); // `en`, the configured application language
```

`getDefaultLanguage()` returns `defaultLanguage` when the manager sets it, otherwise the application language at the
time the manager is created.

[Back to documentation](../README.md#documentation)
