<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests;

use PHPUnit\Framework\Attributes\Group;
use yii2\extensions\localeurls\tests\base\AbstractRedirect;

/**
 * Test suite for URL redirection functionality with script name configuration enabled.
 *
 * Extends the base redirection test suite to verify that URL redirection logic correctly handles language detection and
 * redirect rules when the application is configured to show script names in URLs (for example, index.php).
 *
 * Test coverage.
 * - Cookie-based language detection and redirection behavior with script names.
 * - Custom URL rule processing and redirection logic with script name visibility.
 * - Default language handling in URLs containing script names.
 * - GeoIP-based language detection and automatic redirection with script names.
 * - Language code case conversion with script name configuration.
 * - Language persistence configuration effects on redirection with script names.
 * - Parameter preservation during URL redirection with script name visibility.
 * - Session-based language detection and redirection behavior with script names.
 * - Suffix handling in URL normalization and redirection with script names.
 * - URL normalization with different language code configurations and script names.
 * - UrlNormalizerRedirectException handling with script name visibility.
 * - Wildcard language pattern matching and redirection with script names.
 *
 * @copyright Copyright (C) 2023 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
#[Group('locale-urls')]
final class RedirectWithScriptNameTest extends AbstractRedirect
{
    /**
     * Whether to show the script name in generated URL.
     */
    protected bool $showScriptName = true;
}
