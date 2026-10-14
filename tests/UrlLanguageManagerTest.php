<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests;

use PHPUnit\Framework\Attributes\Group;
use yii2\extensions\localeurls\tests\base\AbstractUrlLanguageManager;
use yii2\extensions\localeurls\UrlLanguageManager;

/**
 * Unit tests for {@see UrlLanguageManager} language handling with no base URL and no script name.
 */
#[Group('locale-urls')]
final class UrlLanguageManagerTest extends AbstractUrlLanguageManager {}
