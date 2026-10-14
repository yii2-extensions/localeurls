<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests\support\stub;

use yii\web\Session;

/**
 * Session that stores its data in `$_SESSION` without starting a native PHP session.
 *
 * Keeps session fixtures independent of `session.save_path`, output state, and the operating system of the runner.
 */
final class ArraySession extends Session
{
    public function close(): void {}

    public function destroy(): void
    {
        $_SESSION = [];
    }

    /**
     * @return bool Always `false`, so the base class never calls the native session API.
     */
    public function getIsActive(): bool
    {
        return false;
    }

    public function open(): void {}
}
