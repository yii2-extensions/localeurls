<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests;

use PHPUnit\Framework\Attributes\Group;
use yii2\extensions\localeurls\tests\base\AbstractSlugRedirect;
use yii2\extensions\localeurls\tests\provider\SlugRedirectProvider;
use yii2\extensions\localeurls\UrlLanguageManager;

/**
 * Unit tests for {@see UrlLanguageManager} slug redirects with the `/base` base URL and the `index.php` script name.
 *
 * {@see SlugRedirectProvider} for test case data providers.
 */
#[Group('locale-urls')]
final class SlugRedirectWithScriptNameAndBaseUrlTest extends AbstractSlugRedirect
{
    protected string $baseUrl = '/base';
    protected bool $showScriptName = true;
}
