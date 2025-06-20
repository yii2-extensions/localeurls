<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests;

use PHPUnit\Framework\Attributes\Group;
use yii2\extensions\localeurls\tests\base\AbstractRedirect;

/**
 * Test suite for URL redirection functionality with both base URL and script name configuration.
 *
 * Extends the base redirect test suite to verify redirection behavior when both base URL and script name are configured
 * in the URL manager, testing scenarios where applications are deployed in subdirectories with visible script names.
 *
 * Test coverage.
 * - Base URL prefix handling during language redirection.
 * - Combined base URL and script name URL formation.
 * - Language code placement with base URL and script name.
 * - Parameter preservation in complex URL structures.
 * - Redirection accuracy with subdirectory deployment.
 * - Script name visibility in redirected URLs.
 * - URL normalization with base URL and script name configuration.
 *
 * @copyright Copyright (C) 2023 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
#[Group('locale-urls')]
final class RedirectWithBaseUrlAndScriptNameTest extends AbstractRedirect
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
