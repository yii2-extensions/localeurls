<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests;

use PHPUnit\Framework\Attributes\Group;
use yii2\extensions\localeurls\tests\base\AbstractUrlLanguageManager;

/**
 * Test suite for language detection, persistence, and URL parsing with both script name and base URL configuration.
 *
 * Extends the base URL language manager test suite to verify language detection, URL parsing, and generation behavior
 * when both a base URL and script name are configured in the URL manager. This covers scenarios where applications are
 * deployed in subdirectories with visible script names in URLs, ensuring correct language handling and URL
 * normalization.
 *
 * Test coverage.
 * - Base URL prefix handling in language-aware URL management.
 * - Combined base URL and script name in language detection and URL generation.
 * - Language code placement with base URL and script name configuration.
 * - Parameter and query string preservation in complex URL structures.
 * - Script name visibility in generated and parsed URLs.
 * - Subdirectory deployment scenarios with language-aware routing.
 * - URL normalization and language switching with both base URL and script name.
 *
 * @copyright Copyright (C) 2023 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
#[Group('locale-urls')]
final class UrlLanguageManagerWithScriptNameAndBaseUrlTest extends AbstractUrlLanguageManager
{
    /**
     * @var string Base URL prefix for test scenarios.
     */
    protected string $baseUrl = '/base';

    /**
     * @var bool Whether to show the script name in generated URLs.
     */
    protected bool $showScriptName = true;
}
