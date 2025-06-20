<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests;

use PHPUnit\Framework\Attributes\Group;
use yii2\extensions\localeurls\tests\base\AbstractUrlLanguageManager;

/**
 * Test suite for language detection, persistence, and URL parsing with base URL configuration.
 *
 * Extends the base URL language manager test suite to verify language detection, URL parsing, and generation behavior
 * when a base URL is configured in the URL manager.
 *
 * Test coverage.
 * - Base URL prefix handling in language detection and URL parsing.
 * - Compatibility with multi-language and SEO-friendly URL structures.
 * - Language code extraction and placement with base URL configuration.
 * - Parameter and query string preservation in subdirectory deployment scenarios.
 * - Redirection and canonicalization with base URL settings.
 * - URL generation and normalization with base URL and language codes.
 *
 * @copyright Copyright (C) 2023 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
#[Group('locale-urls')]
final class UrlLanguageManagerWithBaseUrlTest extends AbstractUrlLanguageManager
{
    /**
     * Base URL prefix for test scenarios.
     */
    protected string $baseUrl = '/base';
}
