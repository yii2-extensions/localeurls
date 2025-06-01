<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests;

use PHPUnit\Framework\Attributes\Group;
use yii2\extensions\localeurls\tests\base\AbstractUrlCreation;

/**
 * Test suite for URL creation functionality with script name configuration.
 *
 * Extends the base URL creation test suite to verify URL generation behavior when the script name is visible in the
 * URL manager configuration.
 *
 * This covers scenarios where applications are deployed with visible entry scripts (for example, `index.php`), ensuring
 * that generated URLs include the script name as expected.
 *
 * Test coverage.
 * - Compatibility with subdirectory and root deployments.
 * - Consistency of URL generation across different configurations.
 * - Language code placement with script name configuration.
 * - Parameter and query string handling with visible script name.
 * - Script name visibility in generated URLs.
 * - SEO-friendly URL structure with script name.
 * - URL normalization and structure with script name present.
 *
 * @copyright Copyright (C) 2023 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
#[Group('locale-urls')]
final class UrlCreationWithScriptNameTest extends AbstractUrlCreation
{
    /**
     * @var bool Whether to show the script name in generated URLs.
     */
    protected bool $showScriptName = true;
}
