<?php

declare(strict_types=1);

namespace yii\tests;

final class UrlLanguageManagerWithScriptNameAndBaseUrlTest extends UrlLanguageManagerTest
{
    protected $showScriptName = true;
    protected $baseUrl = '/base';
}
