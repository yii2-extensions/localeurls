<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests;

use PHPUnit\Framework\Attributes\Group;

/**
 * Test suite for slug-based URL redirection functionality with script name visibility configuration.
 *
 * Extends the slug redirect test suite, to verify redirection behavior when script name visibility is enabled in the
 * URL manager, testing scenarios where applications with slug-based routing show the script name (for example,
 * `index.php`) in generated URLs.
 *
 * Test coverage.
 * - Language code placement in slug URLs with script name visibility.
 * - Parameter and query string preservation in slug URLs with script names.
 * - Redirection accuracy for slug-based routes with script name configuration.
 * - Script name inclusion in slug-based language redirection URLs.
 * - SEO-friendly URL structure maintenance with script name visibility.
 * - Slug path normalization with script name settings.
 * - URL formation for content slugs with script name visibility.
 * - Wildcard pattern matching with slug URLs and script names.
 *
 * @copyright Copyright (C) 2023 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
#[Group('locale-urls')]
final class SlugRedirectWithScriptNameTest extends SlugRedirectTest
{
    /**
     * @var bool Whether to show the script name in generated URLs.
     */
    protected bool $showScriptName = true;
}
