<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests\support\stub;

use yii\web\Request;

/**
 * Request that exposes server parameters through `getServerParams()`, like the `psr-bridge` request.
 */
final class ServerParamsRequest extends Request
{
    /**
     * Server parameters returned by {@see getServerParams()}.
     *
     * @phpstan-var array<string, mixed>
     */
    public array $serverParams = [];

    /**
     * @phpstan-return array<string, mixed>
     */
    public function getServerParams(): array
    {
        return $this->serverParams;
    }
}
