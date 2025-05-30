<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests;

use PHPUnit\Framework\Attributes\Group;

/**
 * Test suite for URL creation functionality with base URL configuration.
 *
 * Extends the base URL creation test suite to verify URL generation behavior when a base URL is configured in the URL
 * manager, testing scenarios where applications are deployed in subdirectories or with URL prefixes.
 *
 * Test coverage.
 * - Base URL prefix handling during URL creation.
 * - Language code placement in generated URLs with base URL configuration.
 * - Parameter and query string preservation in subdirectory deployment scenarios.
 * - SEO-friendly URL structure maintenance with base URL and language codes.
 * - URL accuracy and normalization with base URL settings.
 * - URL formation for content slugs in subdirectory deployment scenarios.
 * - Wildcard pattern matching with base URL prefixes.
 *
 * @copyright Copyright (C) 2023 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
#[Group('locale-urls')]
final class UrlCreationWithBaseUrlTest extends UrlCreationTest
{
    /**
     * @var string Base URL prefix for test scenarios.
     */
    protected string $baseUrl = '/base';
}
