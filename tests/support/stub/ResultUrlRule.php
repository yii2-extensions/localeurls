<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests\support\stub;

use yii\base\BaseObject;
use yii\web\{Request, UrlManager, UrlRuleInterface};

/**
 * URL rule that returns a configured parse result for a configured path info.
 */
final class ResultUrlRule extends BaseObject implements UrlRuleInterface
{
    /**
     * Path info that the rule parses.
     */
    public string $pathInfo = '';
    /**
     * Route and parameters returned when the path info matches {@see $pathInfo}.
     *
     * @phpstan-var array{string, array<array-key, mixed>}
     */
    public array $result = ['', []];

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
     * @return array|false Configured route and parameters, or `false` for any other path info.
     *
     * @phpstan-return array{string, array<array-key, mixed>}|false
     */
    public function parseRequest($manager, $request)
    {
        return $request->getPathInfo() === $this->pathInfo ? $this->result : false;
    }
}
