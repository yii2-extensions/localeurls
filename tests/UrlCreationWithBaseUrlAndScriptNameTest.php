<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests;

use PHPUnit\Framework\Attributes\Group;
use yii2\extensions\localeurls\tests\base\AbstractUrlCreation;

/**
 * Test suite for URL creation functionality with both base URL and script name configuration.
 *
 * Extends the base URL creation test suite to verify URL generation behavior when both a base URL and script name are
 * configured in the URL manager, testing scenarios where applications are deployed in subdirectories with visible
 * script names in generated URLs.
 *
 * Test coverage.
 * - Base URL prefix handling during URL creation.
 * - Combined base URL and script name in generated URLs.
 * - Language code placement with base URL and script name.
 * - Parameter and query string preservation in complex URL structures.
 * - Script name visibility in generated URL.
 * - URL generation accuracy with subdirectory deployment and script name visibility.
 * - URL normalization with base URL and script name configuration.
 *
 * @copyright Copyright (C) 2023 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
#[Group('locale-urls')]
final class UrlCreationWithBaseUrlAndScriptNameTest extends AbstractUrlCreation
{
    /**
     * Base URL prefix for test scenarios.
     */
    protected string $baseUrl = '/base';

    /**
     * Whether to show the script name in generated URL.
     */
    protected bool $showScriptName = true;
}
