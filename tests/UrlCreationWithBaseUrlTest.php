<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests;

use PHPUnit\Framework\Attributes\Group;
use yii2\extensions\localeurls\tests\base\AbstractUrlCreation;
use yii2\extensions\localeurls\tests\provider\UrlCreationProvider;
use yii2\extensions\localeurls\UrlLanguageManager;

/**
 * Unit tests for {@see UrlLanguageManager} URL creation with the `/base` base URL.
 *
 * {@see UrlCreationProvider} for test case data providers.
 */
#[Group('locale-urls')]
final class UrlCreationWithBaseUrlTest extends AbstractUrlCreation
{
    protected string $baseUrl = '/base';
}
