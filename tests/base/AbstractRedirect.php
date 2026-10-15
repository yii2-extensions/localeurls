<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests\base;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use Yii;
use yii\base\{Exception, InvalidConfigException};
use yii\web\{NotFoundHttpException, UrlNormalizer};
use yii2\extensions\localeurls\tests\provider\{RedirectProvider, RequestFixtures};
use yii2\extensions\localeurls\tests\support\TestCase;
use yii2\extensions\localeurls\UrlLanguageManager;

/**
 * Unit tests for {@see UrlLanguageManager} redirects, replayed under the URL layout of each concrete subclass.
 *
 * {@see RedirectProvider} for test case data providers.
 *
 * @phpstan-import-type Fixtures from RequestFixtures
 */
abstract class AbstractRedirect extends TestCase
{
    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testDoesNotRedirectLanguageRootWhenNormalizerActionIsNull(): void
    {
        $this->mockNormalizerManager(null);
        $this->assertNoRedirect('/de', [], ['', []]);

        self::assertSame(
            'de',
            Yii::$app->language,
            'Language must be set from the URL.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     *
     * @phpstan-param array<string, mixed> $urlManager
     * @phpstan-param Fixtures $fixtures
     * @phpstan-param array{string, array<array-key, string>} $route
     */
    #[DataProviderExternal(RedirectProvider::class, 'noRedirects')]
    public function testDoNotRedirect(
        array $urlManager,
        string $from,
        string $language,
        string $pathInfo,
        array $fixtures,
        array $route,
    ): void {
        $this->applyFixtures($fixtures);
        $this->mockUrlLanguageManager($urlManager);
        $this->assertNoRedirect($from, $fixtures['request'], $route);

        self::assertSame(
            $language,
            Yii::$app->language,
            'Application language must match.',
        );
        self::assertSame(
            $pathInfo,
            Yii::$app->request->pathInfo,
            'Path info must not contain the language code.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsDefaultLanguageRootWhenNormalizerRejectsNonCanonicalUrls(): void
    {
        $this->mockNormalizerManager(UrlNormalizer::ACTION_NOT_FOUND);
        $this->assertRedirect('/', '/en');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     *
     * @phpstan-param array<string, mixed> $urlManager
     * @phpstan-param Fixtures $fixtures
     */
    #[DataProviderExternal(RedirectProvider::class, 'redirects')]
    public function testRedirectToExpectedUrl(
        array $urlManager,
        string $from,
        string $to,
        int $statusCode,
        array $fixtures,
    ): void {
        $this->applyFixtures($fixtures);
        $this->mockUrlLanguageManager($urlManager);
        $this->assertRedirect($to, $from, $fixtures['request'], $statusCode);
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     */
    public function testThrowNotFoundHttpExceptionWhenNormalizerRejectsLanguageRoot(): void
    {
        $this->mockNormalizerManager(UrlNormalizer::ACTION_NOT_FOUND);

        $this->expectException(NotFoundHttpException::class);

        $this->mockRequest('/de');
    }

    /**
     * Configures the `en` and `de` languages with the `/` suffix and a URL normalizer using the given action.
     *
     * @param int|null $action Normalizer action for non-canonical URLs, or `null` to accept them.
     */
    private function mockNormalizerManager(int|null $action): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'normalizer' => [
                    'class' => UrlNormalizer::class,
                    'action' => $action,
                ],
                'suffix' => '/',
            ],
        );
    }
}
