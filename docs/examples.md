# Usage examples

This document provides comprehensive examples of how to use the Yii LocaleUrls extension in real-world multilingual 
applications.

## Basic URL generation

### Standard URL creation

```php
<?php

declare(strict_types=1);

use yii\helpers\Url;
use yii\web\Controller;

final class SiteController extends Controller
{
    // URLs are automatically localized based on current language
    public function actionIndex(): string
    {
        // /en/ (if current language is 'en')
        $homeUrl = Url::to(['site/index']); 
        // /en/site/about
        $aboutUrl = Url::to(['site/about']); 
        // /en/site/contact
        $contactUrl = Url::to(['site/contact']);
        
        return $this->render(
            'index', 
            [
                'homeUrl' => $homeUrl,
                'aboutUrl' => $aboutUrl,
                'contactUrl' => $contactUrl,
            ],
        );
    }
    
    // Force specific language in URL    
    public function actionDemoUrls(): array
    {
        // /de/site/about
        $germanAbout = Url::to(['site/about', 'language' => 'de']); 
        // /fr/site/contact
        $frenchContact = Url::to(['site/contact', 'language' => 'fr']);
        // /es/
        $spanishHome = Url::to(['/', 'language' => 'es']); 
        // Remove language (empty string) /site/page
        $noLanguage = Url::to(['site/page', 'language' => '']);
        
        return [
            'german_about' => $germanAbout,
            'french_contact' => $frenchContact,
            'spanish_home' => $spanishHome,
            'no_language' => $noLanguage,
        ];
    }
}
```

### URL with parameters

```php
<?php

declare(strict_types=1);

use yii\helpers\Url;

use yii\web\Controller;

class BlogController extends Controller
{
    public function actionPost(string $slug): string
    {
        // Current language URLs with parameters
        // /en/blog/edit?slug=my-post
        $editUrl = Url::to(['blog/edit', 'slug' => $slug]);
        // /en/blog/delete?slug=my-post&confirm=1
        $deleteUrl = Url::to(['blog/delete', 'slug' => $slug, 'confirm' => 1]);
        
        // Different language versions of the same post
        // /de/blog/post?slug=my-post
        $germanPost = Url::to(['blog/post', 'slug' => $slug, 'language' => 'de']);
        // /fr/blog/post?slug=my-post
        $frenchPost = Url::to(['blog/post', 'slug' => $slug, 'language' => 'fr']);
        
        return $this->render('post', [
            'editUrl' => $editUrl,
            'deleteUrl' => $deleteUrl,
            'germanPost' => $germanPost,
            'frenchPost' => $frenchPost,
        ]);
    }
}
```

## Language switching

### Basic language switcher

```php
<?php

declare(strict_types=1);

use Yii;
use yii\base\Widget;
use yii\helpers\{Html, Url};

class LanguageSwitcher extends Widget
{
    public function run(): string
    {
        $currentLanguage = Yii::$app->language;
        $languages = Yii::$app->urlManager->languages;
        
        $links = [];
        
        foreach ($languages as $code => $language) {
            // Handle language aliases (for example, 'deutsch' => 'de')
            $languageCode = is_string($code) ? $language : $code;
            $displayCode = is_string($code) ? $code : $language;
            
            $isActive = $currentLanguage === $languageCode;
            $class = $isActive ? 'active' : '';
            
            // Generate URL for current page in different language
            $url = Url::current(['language' => $languageCode]);
            
            $links[] = Html::a(
                strtoupper($displayCode),
                $url,
                ['class' => $class]
            );
        }
        
        return Html::tag('div', implode(' | ', $links), ['class' => 'language-switcher']);
    }
}
```

This comprehensive examples guide demonstrates practical usage patterns for the Yii LocaleUrls extension across 
different multilingual application scenarios, from basic URL generation to advanced language detection and event
handling.

## Next steps

- 📚 [Installation Guide](installation.md)
- ⚙️ [Configuration Guide](configuration.md)
- 🧪 [Testing Guide](testing.md)
