<?php

declare(strict_types=1);

namespace Yii2\Extensions\LocaleUrls\Test;

final class RedirectWithBaseUrlAndScriptNameTest extends RedirectTest
{
    protected $baseUrl = '/base';
    protected $showScriptName = true;
}
