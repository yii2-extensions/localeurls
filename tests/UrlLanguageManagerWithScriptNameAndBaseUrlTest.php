<?php

declare(strict_types=1);

namespace Yii2\Extensions\LocaleUrls\Test;

use PHPUnit\Framework\Attributes\Group;

#[Group('locale-urls')]
final class UrlLanguageManagerWithScriptNameAndBaseUrlTest extends UrlLanguageManagerTest
{
    protected string $baseUrl = '/base';

    protected bool $showScriptName = true;
}
