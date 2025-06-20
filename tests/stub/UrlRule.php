<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests\stub;

use Yii;
use yii\base\Exception;
use yii\helpers\Url;
use yii\web\UrlRuleInterface;

/**
 * Custom URL rule implementation for testing language-based routing and redirects.
 *
 * Provides a minimal UrlRuleInterface implementation for simulating language slug handling in URLs and request parsing.
 *
 * This class is used in test scenarios to verify correct URL creation and request parsing based on language slugs,
 * including redirect logic and exception handling for test environments.
 *
 * @copyright Copyright (C) 2023 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
final class UrlRule implements UrlRuleInterface
{
    public function createUrl($manager, $route, $params)
    {
        if ($route === 'ruleclass/test') {
            $language = $params['slugLanguage'] ?? Yii::$app->language;

            return match ($language) {
                'de' => 'ruleclass-deutsch',
                'fr' => 'ruleclass-francais',
                default => 'ruleclass-english',
            };
        }

        return false;
    }

    public function parseRequest($manager, $request)
    {
        $language = Yii::$app->language;
        $pathInfo = $request->pathInfo;

        if ($pathInfo === 'ruleclass-deutsch') {
            $slugLanguage = 'de';
        } elseif ($pathInfo === 'ruleclass-francais') {
            $slugLanguage = 'fr';
        } elseif ($pathInfo === 'ruleclass-english') {
            $slugLanguage = 'en';
        } else {
            return false;
        }

        if ($language === $slugLanguage) {
            return ['ruleclass/test', []];
        }

        // Redirect to correct slug language
        $url = ['/ruleclass/test', 'slugLanguage' => $language];

        Yii::$app->response->redirect($url);

        if (YII_ENV === 'test') {
            /**
             * Response::redirect($url) above will call `Url::to()` internally.
             * So to test for the same final redirect URL here, we need to call Url::to(), too.
             */
            throw new Exception(Url::to($url));
        }

        Yii::$app->end();

        return false;
    }
}
