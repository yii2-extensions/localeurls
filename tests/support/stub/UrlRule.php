<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests\support\stub;

use Yii;
use yii\web\{Request, UrlManager, UrlNormalizerRedirectException, UrlRuleInterface};

/**
 * URL rule that maps language-specific slugs to the `ruleclass/test` route.
 *
 * A slug that does not match the application language raises {@see UrlNormalizerRedirectException} to the slug of that
 * language, like a custom rule redirecting during routing.
 */
final class UrlRule implements UrlRuleInterface
{
    /**
     * @param UrlManager $manager URL manager that creates the URL.
     * @param string $route Route to create the URL for.
     * @param array<array-key, mixed> $params Route parameters; `slugLanguage` selects the slug language.
     *
     * @return false|string Slug of the language, or `false` for other routes.
     */
    public function createUrl($manager, $route, $params): false|string
    {
        if ($route !== 'ruleclass/test') {
            return false;
        }

        return match ($params['slugLanguage'] ?? Yii::$app->language) {
            'de' => 'ruleclass-deutsch',
            'fr' => 'ruleclass-francais',
            default => 'ruleclass-english',
        };
    }

    /**
     * @param UrlManager $manager URL manager that parses the request.
     * @param Request $request Request to parse.
     *
     * @throws UrlNormalizerRedirectException if the slug does not match the application language.
     *
     * @return array|false Route and parameters, or `false` if the path info is not a known slug.
     *
     * @phpstan-return array{string, array<array-key, mixed>}|false
     */
    public function parseRequest($manager, $request): array|false
    {
        $slugLanguage = match ($request->getPathInfo()) {
            'ruleclass-deutsch' => 'de',
            'ruleclass-francais' => 'fr',
            'ruleclass-english' => 'en',
            default => null,
        };

        if ($slugLanguage === null) {
            return false;
        }

        $language = Yii::$app->language;

        if ($language === $slugLanguage) {
            return ['ruleclass/test', []];
        }

        throw new UrlNormalizerRedirectException(['/ruleclass/test', 'slugLanguage' => $language]);
    }
}
