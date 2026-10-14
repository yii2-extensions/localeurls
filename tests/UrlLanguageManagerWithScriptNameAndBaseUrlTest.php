<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests;

use PHPUnit\Framework\Attributes\Group;
use yii2\extensions\localeurls\tests\base\AbstractUrlLanguageManager;
use yii2\extensions\localeurls\UrlLanguageManager;

/**
 * Unit tests for {@see UrlLanguageManager} language handling with the `/base` base URL and the `index.php` script name.
 */
#[Group('locale-urls')]
final class UrlLanguageManagerWithScriptNameAndBaseUrlTest extends AbstractUrlLanguageManager
{
    protected string $baseUrl = '/base';
    protected bool $showScriptName = true;
}
