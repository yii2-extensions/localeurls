<?php

declare(strict_types=1);

namespace Yii2\Extensions\LocaleUrls;

use yii\base\Event;

class LanguageChangedEvent extends Event
{
    /**
     * @var string the new language
     */
    public string $language = '';

    /**
     * @var string|null the old language
     */
    public string|null $oldLanguage = null;
}
