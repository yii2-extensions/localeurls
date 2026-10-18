<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests;

use PHPUnit\Framework\Attributes\{Group, TestWith};
use Yii;
use yii\web\{NotFoundHttpException, UrlNormalizer};
use yii2\extensions\localeurls\tests\support\stub\FixedUrlRequest;
use yii2\extensions\localeurls\tests\support\TestCase;

/**
 * Regression tests for manager and rule normalizers after a language prefix is removed.
 */
#[Group('locale-urls')]
final class UrlNormalizationRegressionTest extends TestCase
{
    public function testDisabledSlashCollapseKeepsRawPath(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'enableLanguagePersistence' => false,
                'normalizer' => [
                    'class' => UrlNormalizer::class,
                    'action' => UrlNormalizer::ACTION_NOT_FOUND,
                    'collapseSlashes' => false,
                    'normalizeTrailingSlash' => false,
                ],
            ],
        );

        $this->assertNoRedirect(
            '/de//page',
            route: ['/page', []],
        );
    }

    #[TestWith(['', false, 301])]
    #[TestWith(['/base', false, 301])]
    #[TestWith(['', true, 301])]
    #[TestWith(['/base', true, 301])]
    #[TestWith(['', false, 302])]
    #[TestWith(['/base', false, 302])]
    #[TestWith(['', true, 302])]
    #[TestWith(['/base', true, 302])]
    public function testDuplicateSlashesKeepNormalizerRedirect(string $base, bool $script, int $action): void
    {
        $this->baseUrl = $base;
        $this->showScriptName = $script;

        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'enableLanguagePersistence' => false,
                'normalizer' => ['class' => UrlNormalizer::class, 'action' => $action],
            ],
        );

        $urls = [
            '/de//page?q=x',
            '/de///page?q=x',
        ];

        foreach ($urls as $url) {
            $this->assertRedirect(
                '/de/page?q=x',
                $url,
                expectedStatusCode: $action,
            );

            self::assertSame(
                $base,
                Yii::$app->request->getBaseUrl(),
                'The request base URL must be restored.',
            );
            self::assertSame(
                "{$base}/index.php",
                Yii::$app->request->getScriptUrl(),
                'The script URL must be restored.',
            );
            self::assertSame(
                $this->prepareUrl($url),
                Yii::$app->request->getUrl(),
                'The original URI must be retained.',
            );
        }
    }

    #[TestWith([false, null])]
    #[TestWith([true, null])]
    #[TestWith([false, 301])]
    #[TestWith([true, 301])]
    public function testExplicitPathInfoRemainsTheRoutingSource(bool $fixedUri, int|null $action): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'enableLanguagePersistence' => false,
                'normalizer' => ['class' => UrlNormalizer::class, 'action' => $action],
            ],
        );

        $request = ['pathInfo' => 'de//rewritten'];

        if ($fixedUri) {
            $request['class'] = FixedUrlRequest::class;
            $request['requestUrl'] = '/de/original';
        }

        if ($action === null) {
            $this->assertNoRedirect(
                '/de/original',
                $request,
                ['rewritten', []],
            );
        } else {
            $this->assertRedirect(
                '/de/rewritten',
                '/de/original',
                $request,
                $action,
            );
        }
    }

    #[TestWith(['/DE//page', '/de/page'])]
    #[TestWith(['/en//page', '/page'])]
    public function testLanguageRedirectKeepsItsStatusAfterSlashNormalization(string $url, string $target): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'enableLanguagePersistence' => false,
                'normalizer' => ['class' => UrlNormalizer::class, 'action' => 301],
            ],
        );

        $this->assertRedirect(
            $target,
            $url,
        );
    }

    #[TestWith(['/de//page', '/de/page'])]
    #[TestWith(['/de///page', '/de/page'])]
    #[TestWith(['/de//', '/de/'])]
    public function testRequestWithAdapterOwnedUriKeepsNormalizer(string $url, string $target): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'enableLanguagePersistence' => false,
                'normalizer' => ['class' => UrlNormalizer::class],
            ],
        );

        $this->assertRedirect(
            $target,
            $url,
            [
                'class' => FixedUrlRequest::class,
                'requestUrl' => $url,
            ],
            301,
        );
    }

    public function testRuleCanDisableManagerNormalizerAndMatchRawPath(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'enableLanguagePersistence' => false,
                'normalizer' => ['class' => UrlNormalizer::class, 'action' => UrlNormalizer::ACTION_NOT_FOUND],
                'rules' => [['pattern' => '<path:.*>', 'route' => 'site/page', 'normalizer' => false]],
            ],
        );

        $this->assertNoRedirect(
            '/de//page',
            route: ['site/page', ['path' => '/page']],
        );
    }

    #[TestWith([301])]
    #[TestWith([302])]
    public function testRuleNormalizerHandlesSlashesWithManagerNormalizerDisabled(int $action): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'enableLanguagePersistence' => false,
                'normalizer' => false,
                'rules' => [
                    [
                        'pattern' => 'page',
                        'route' => 'site/page',
                        'normalizer' => ['class' => UrlNormalizer::class, 'action' => $action],
                    ],
                ],
            ],
        );

        $this->assertRedirect(
            '/de/page',
            '/de//page',
            expectedStatusCode: $action,
        );
    }

    #[TestWith(['/de//a%252Fb', 'a%2Fb'])]
    #[TestWith(['/de//a%3Fb', 'a?b'])]
    #[TestWith(['/de//caf%C3%A9', 'café'])]
    public function testSilentNormalizerKeepsDecodedPath(string $url, string $route): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'enableLanguagePersistence' => false,
                'normalizer' => ['class' => UrlNormalizer::class, 'action' => null],
            ],
        );

        $this->assertNoRedirect(
            $url,
            route: [$route, []],
        );
    }

    #[TestWith(['/de//page'])]
    #[TestWith(['/de///page'])]
    public function testThrowsNotFoundWhenDuplicateSlashesViolateNormalizer(string $url): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'enableLanguagePersistence' => false,
                'normalizer' => ['class' => UrlNormalizer::class, 'action' => UrlNormalizer::ACTION_NOT_FOUND],
            ],
        );

        $this->expectException(NotFoundHttpException::class);

        $this->mockRequest($url);
    }

    public function testThrowsNotFoundWhenRuleNormalizerRejectsDuplicateSlashes(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'enableLanguagePersistence' => false,
                'normalizer' => false,
                'rules' => [[
                    'pattern' => 'page',
                    'route' => 'site/page',
                    'normalizer' => ['class' => UrlNormalizer::class, 'action' => UrlNormalizer::ACTION_NOT_FOUND],
                ]],
            ],
        );

        $this->expectException(NotFoundHttpException::class);

        $this->mockRequest('/de//page');
    }
}
