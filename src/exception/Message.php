<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\exception;

use function sprintf;

/**
 * Defines locale URL exception message templates.
 *
 * Use {@see Message::getMessage()} to format the template with `sprintf()` arguments.
 */
enum Message: string
{
    /**
     * Error when locale URLs are enabled while pretty URLs are disabled.
     */
    case LOCALE_URLS_REQUIRE_PRETTY_URL = 'Locale URL support requires enablePrettyUrl to be set to true.';

    /**
     * Returns the formatted message string for the error case.
     *
     * @param int|string ...$argument Values to insert into the message template.
     *
     * @return string Formatted error message with interpolated arguments.
     */
    public function getMessage(int|string ...$argument): string
    {
        return sprintf($this->value, ...$argument);
    }
}
