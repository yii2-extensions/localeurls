<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls;

use yii\base\Event;

/**
 * Represents the {@see UrlLanguageManager::EVENT_LANGUAGE_CHANGED} event, raised when a language from the URL differs
 * from the persisted one.
 */
class LanguageChangedEvent extends Event
{
    /**
     * Language code being persisted, for example `de-AT`.
     */
    public string $language = '';
    /**
     * Previously persisted language code, or `null` if no language was persisted.
     */
    public string|null $oldLanguage = null;
}
