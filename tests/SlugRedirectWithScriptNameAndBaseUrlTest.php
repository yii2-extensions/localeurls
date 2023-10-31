<?php

declare(strict_types=1);

namespace yii\tests;

final class SlugRedirectWithScriptNameAndBaseUrlTest extends SlugRedirectTest
{
    protected $showScriptName = true;
    protected $baseUrl = '/base';
}
