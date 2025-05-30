<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests;

use PHPUnit\Framework\Attributes\Group;
use yii2\extensions\localeurls\tests\base\AbstractRedirect;

/**
 * Test suite for URL redirection functionality with base URL configuration.
 *
 * Extends the base redirect test suite to verify redirection behavior when a base URL is configured in the URL manager,
 * testing scenarios where applications are deployed in subdirectories or with URL prefixes.
 *
 * Test coverage.
 * - Base URL prefix handling during language redirection.
 * - Language code placement with base URL configuration.
 * - Parameter preservation in subdirectory deployment scenarios.
 * - Redirection accuracy with URL prefix configuration.
 * - URL normalization with base URL settings.
 * - Wildcard pattern matching with base URL prefixes.
 *
 * @copyright Copyright (C) 2023 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
#[Group('locale-urls')]
final class RedirectWithBaseUrlTest extends AbstractRedirect
{
    /**
     * @var string Base URL prefix for test scenarios.
     */
    protected string $baseUrl = '/base';
}
