<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests;

use PHPUnit\Framework\Attributes\Group;
use yii2\extensions\localeurls\tests\base\AbstractRedirect;
use yii2\extensions\localeurls\tests\provider\RedirectProvider;
use yii2\extensions\localeurls\UrlLanguageManager;

/**
 * Unit tests for {@see UrlLanguageManager} redirects with the `/base` base URL.
 *
 * {@see RedirectProvider} for test case data providers.
 */
#[Group('locale-urls')]
final class RedirectWithBaseUrlTest extends AbstractRedirect
{
    protected string $baseUrl = '/base';
}
