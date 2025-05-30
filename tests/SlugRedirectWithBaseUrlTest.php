<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests;

use PHPUnit\Framework\Attributes\Group;
use yii2\extensions\localeurls\tests\base\AbstractSlugRedirect;

/**
 * Test suite for slug-based URL redirection functionality with base URL configuration.
 *
 * Extends the slug redirect test suite to verify redirection behavior when a base URL is configured in the URL manager,
 * testing scenarios where applications with slug-based routing are deployed in subdirectories or with URL prefixes.
 *
 * Test coverage.
 * - Base URL prefix handling with slug-based language redirection.
 * - Content slug preservation during language redirection with base URL.
 * - Language code placement in slug URLs with base URL configuration.
 * - Parameter and query string preservation in slug URLs with subdirectory deployment.
 * - Redirection accuracy for slug-based routes with URL prefix configuration.
 * - SEO-friendly URL structure maintenance with base URL and language codes.
 * - Slug path normalization with base URL settings.
 * - URL formation for content slugs in subdirectory deployment scenarios.
 * - Wildcard pattern matching with slug URLs and base URL prefixes.
 *
 * @copyright Copyright (C) 2023 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
#[Group('locale-urls')]
final class SlugRedirectWithBaseUrlTest extends AbstractSlugRedirect
{
    /**
     * @var string Base URL prefix for test scenarios.
     */
    protected string $baseUrl = '/base';
}
