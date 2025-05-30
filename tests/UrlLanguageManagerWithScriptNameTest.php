<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests;

use PHPUnit\Framework\Attributes\Group;

/**
 * Test suite for language detection, persistence, and URL parsing with script name configuration.
 *
 * Extends the base URL language manager test suite to verify language detection, URL parsing, and generation behavior
 * when the script name is visible in the URL manager configuration, testing scenarios where applications expose the
 * entry script in generated URLs.
 *
 * Test coverage.
 * - Compatibility with multi-language and SEO-friendly URL structures.
 * - Language code extraction and placement with script name configuration.
 * - Parameter and query string preservation in script name deployment scenarios.
 * - Redirection and canonicalization with script name settings.
 * - Script name visibility in language detection and URL parsing.
 * - URL generation and normalization with script name and language codes.
 *
 * @copyright Copyright (C) 2023 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
#[Group('locale-urls')]
final class UrlLanguageManagerWithScriptNameTest extends UrlLanguageManagerTest
{
    /**
     * @var bool Whether to show the script name in generated URLs.
     */
    protected bool $showScriptName = true;
}
