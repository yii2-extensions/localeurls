<?php

declare(strict_types=1);

namespace Yii2\Extensions\LocaleUrls\Test;

final class UrlLanguageManagerWithScriptNameAndBaseUrlTest extends UrlLanguageManagerTest
{
    protected $showScriptName = true;
    protected $baseUrl = '/base';
}
