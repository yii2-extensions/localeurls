<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests\support\stub;

use yii\web\Request;

/**
 * Request whose URI comes from an external adapter, independently of Yii's URL setter.
 */
final class FixedUrlRequest extends Request
{
    public string $requestUrl = '';

    public function getUrl(): string
    {
        return $this->requestUrl;
    }
}
