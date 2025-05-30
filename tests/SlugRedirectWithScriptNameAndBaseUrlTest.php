<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests;

use PHPUnit\Framework\Attributes\Group;

/**
 * Test suite for slug-based URL redirection functionality with both script name and base URL configuration.
 *
 * Extends the slug redirect test suite to verify redirection behavior, when both script name visibility and base URL
 * are configured in the URL manager, testing scenarios where applications with slug-based routing are deployed in
 * subdirectories with visible script names (for example, `index.php`).
 *
 * Test coverage.
 * - Base URL prefix handling with script name in slug-based language redirection.
 * - Combined script name and base URL formation for slug URLs with language codes.
 * - Content slug preservation during complex URL redirection scenarios.
 * - Language code placement in slug URLs with both script name and base URL.
 * - Parameter and query string preservation in complex deployment configurations.
 * - Redirection accuracy for slug-based routes with subdirectory and script name visibility.
 * - Script name visibility in slug URLs with base URL prefix configuration.
 * - SEO-friendly URL structure maintenance with combined configuration options.
 * - Slug path normalization with script name and base URL settings.
 * - URL formation for content slugs in complex deployment scenarios.
 * - Wildcard pattern matching with slug URLs, script names, and base URL prefixes.
 *
 * @copyright Copyright (C) 2023 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
#[Group('locale-urls')]
final class SlugRedirectWithScriptNameAndBaseUrlTest extends SlugRedirectTest
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
