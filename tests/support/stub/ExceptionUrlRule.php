<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests\support\stub;

use Throwable;
use yii\base\BaseObject;
use yii\web\{Request, UrlManager, UrlRuleInterface};

/**
 * URL rule that throws a configured exception while parsing a configured path info.
 */
final class ExceptionUrlRule extends BaseObject implements UrlRuleInterface
{
    /**
     * Exception thrown when the path info matches {@see $pathInfo}.
     */
    public Throwable|null $exception = null;
    /**
     * Path info that triggers the exception.
     */
    public string $pathInfo = '';

    /**
     * @param UrlManager $manager URL manager that creates the URL.
     * @param string $route Route to create the URL for.
     * @param array<array-key, mixed> $params Route parameters.
     *
     * @return false Never creates URLs.
     */
    public function createUrl($manager, $route, $params)
    {
        return false;
    }

    /**
     * @param UrlManager $manager URL manager that parses the request.
     * @param Request $request Request to parse.
     *
     * @throws Throwable if the path info matches {@see $pathInfo}.
     *
     * @return false Never parses requests.
     */
    public function parseRequest($manager, $request)
    {
        if ($this->exception !== null && $request->getPathInfo() === $this->pathInfo) {
            throw $this->exception;
        }

        return false;
    }
}
