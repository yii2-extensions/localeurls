<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests\support\stub;

use yii\web\Request;
use yii2\extensions\localeurls\UrlLanguageManager;

/**
 * URL language manager that overrides every protected helper and records the calls, as a subclass author would.
 */
final class ExtendedUrlLanguageManager extends UrlLanguageManager
{
    /**
     * Names of the protected helpers called, in call order.
     *
     * @phpstan-var list<string>
     */
    public array $calls = [];

    protected function detectLanguage(Request $request): string|null
    {
        $this->calls[] = __FUNCTION__;

        return parent::detectLanguage($request);
    }

    protected function loadPersistedLanguage(Request $request): string|null
    {
        $this->calls[] = __FUNCTION__;

        return parent::loadPersistedLanguage($request);
    }

    protected function matchCode(string $code): array
    {
        $this->calls[] = __FUNCTION__;

        return parent::matchCode($code);
    }

    protected function persistLanguage(Request $request, string $language): void
    {
        $this->calls[] = __FUNCTION__;

        parent::persistLanguage($request, $language);
    }

    protected function processLocaleUrl(Request $request): string|null
    {
        $this->calls[] = __FUNCTION__;

        return parent::processLocaleUrl($request);
    }
}
