<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests;

use PHPUnit\Framework\Attributes\Group;
use yii2\extensions\localeurls\tests\base\AbstractUrlCreation;
use yii2\extensions\localeurls\tests\provider\UrlCreationProvider;
use yii2\extensions\localeurls\UrlLanguageManager;

/**
 * Unit tests for {@see UrlLanguageManager} URL creation with the `/base` base URL and the `index.php` script name.
 *
 * {@see UrlCreationProvider} for test case data providers.
 */
#[Group('locale-urls')]
final class UrlCreationWithBaseUrlAndScriptNameTest extends AbstractUrlCreation
{
    protected string $baseUrl = '/base';
    protected bool $showScriptName = true;
}
