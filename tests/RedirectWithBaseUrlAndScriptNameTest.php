<?php

declare(strict_types=1);

namespace yii\tests;

final class RedirectWithBaseUrlAndScriptNameTest extends RedirectTest
{
    protected $baseUrl = '/base';
    protected $showScriptName = true;
}
