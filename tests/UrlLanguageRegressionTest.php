<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests;

use PHPUnit\Framework\Attributes\{DataProviderExternal, Group, TestWith};
use Yii;
use yii2\extensions\localeurls\tests\provider\UrlLanguageRegressionProvider;
use yii2\extensions\localeurls\tests\support\TestCase;

/**
 * Regression tests for inherited host rules and case-insensitive regional-language fallback.
 *
 * {@see UrlLanguageRegressionProvider} for host-rule test cases.
 */
#[Group('locale-urls')]
final class UrlLanguageRegressionTest extends TestCase
{
    #[DataProviderExternal(UrlLanguageRegressionProvider::class, 'hostRuleLayouts')]
    public function testLanguageFollowsAuthorityAndApplicationPrefix(string $host, string $base, bool $script): void
    {
        $this->baseUrl = $base;
        $this->showScriptName = $script;

        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'defaultLanguage' => 'en',
                'enableLanguagePersistence' => false,
                'rules' => [
                    ['host' => $host, 'pattern' => 'page', 'route' => 'site/page'],
                    ['host' => $host, 'pattern' => '', 'route' => 'site/index'],
                ],
            ],
        );

        $this->mockRequestApplication('/de/page');

        Yii::$app->language = 'de';

        self::assertSame(
            $host . $this->prepareUrl('/de/page?q=x#top'),
            Yii::$app->urlManager->createUrl(['site/page', 'q' => 'x', '#' => 'top']),
            'Language insertion must preserve the authority, query and fragment.',
        );
        self::assertSame(
            $host . $this->prepareUrl('/de'),
            Yii::$app->urlManager->createUrl(['site/index']),
            'A host rule for the root must keep its path after the language.',
        );
    }

    #[TestWith(['de-CH', 'de'])]
    #[TestWith(['DE-CH', 'de'])]
    #[TestWith(['De-CH', 'de'])]
    #[TestWith(['de-CH', 'DE'])]
    public function testRegionalBrowserFallbackKeepsConfiguredSpelling(string $header, string $configured): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', $configured],
                'defaultLanguage' => 'en',
                'enableLanguagePersistence' => false,
                'keepUppercaseLanguageCode' => true,
            ],
        );

        $this->withServer(['HTTP_ACCEPT_LANGUAGE' => $header]);
        $this->assertRedirect("/{$configured}/page", '/page');
    }
}
