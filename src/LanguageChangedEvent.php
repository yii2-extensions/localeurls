<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls;

use yii\base\Event;

/**
 * Event triggered when the application's language changes.
 *
 * This event is dispatched whenever the application's language is switched, providing both the new and previous
 * language codes.
 *
 * It enables components and listeners to react to language changes, such as updating UI elements, reloading resources,
 * or logging language transitions.
 *
 * Key features.
 * - Exposes the new language code via {@see LanguageChangedEvent::language}.
 * - Integrates with Yii's event system for seamless notification.
 * - Provides the previous language code via {@see LanguageChangedEvent::oldLanguage}.
 * - Supports null for the old language if not previously set.
 *
 * Usage example:
 * ```php
 * Event::on(
 *     SomeComponent::class,
 *     SomeComponent::EVENT_LANGUAGE_CHANGED,
 *     static function (LanguageChangedEvent $event) {
 *         $new = $event->language;
 *         $old = $event->oldLanguage;
 *     }
 * );
 * ```
 *
 * @see Event for base event.
 *
 * @copyright Copyright (C) 2023 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
class LanguageChangedEvent extends Event
{
    /**
     * @var string New language.
     */
    public string $language = '';

    /**
     * @var string|null Old language.
     */
    public string|null $oldLanguage = null;
}
