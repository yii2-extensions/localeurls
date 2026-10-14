<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests\base;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use Yii;
use yii\base\{Exception, InvalidConfigException};
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii2\extensions\localeurls\tests\provider\UrlCreationProvider;
use yii2\extensions\localeurls\tests\support\TestCase;
use yii2\extensions\localeurls\UrlLanguageManager;

use function preg_match;

/**
 * Unit tests for {@see UrlLanguageManager} URL creation, replayed under the URL layout of each concrete subclass.
 *
 * {@see UrlCreationProvider} for test case data providers.
 */
abstract class AbstractUrlCreation extends TestCase
{
    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     *
     * @phpstan-param array<string, mixed> $urlManager
     * @phpstan-param array<array-key, string> $route
     * @phpstan-param 'absolute'|'forcedRelative'|'relative' $mode
     */
    #[DataProviderExternal(UrlCreationProvider::class, 'urls')]
    public function testCreateExpectedUrl(
        array $urlManager,
        string $requestUrl,
        string $expectedUrl,
        array $route,
        string $mode,
    ): void {
        $this->mockUrlLanguageManager($urlManager);
        $this->mockRequest($requestUrl);

        if ($mode === 'relative') {
            self::assertSame(
                $this->prepareUrl($expectedUrl),
                Url::to($route),
                'Relative URL must match.',
            );

            return;
        }

        self::assertSame(
            1,
            preg_match('#^(https?)://([^/]*)(.*)#', $expectedUrl, $matches),
            'Expected URL must be absolute.',
        );

        [, $scheme, $host, $relativeUrl] = $matches;

        self::assertSame(
            "{$scheme}://{$host}" . $this->prepareUrl($relativeUrl),
            $mode === 'absolute' ? Url::to($route, $scheme) : Url::to($route),
            'Absolute URL must match.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testCreateUrlAcceptsStringRouteWithIgnorePatterns(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'ignoreLanguageUrlPatterns' => [
                    '#^site/login#' => '#^login#',
                ],
            ],
        );
        $this->mockRequest('/de/site/page');

        $urlManager = Yii::$app->urlManager;

        self::assertSame(
            $this->prepareUrl('/site/login'),
            $urlManager->createUrl('site/login'),
            'Ignored route must not get a language code.',
        );
        self::assertSame(
            $this->prepareUrl('/site/login'),
            $urlManager->createUrl(['/site/login']),
            'Leading slash must not bypass the ignore pattern.',
        );
        self::assertSame(
            $this->prepareUrl('/de/site/page'),
            $urlManager->createUrl('site/page'),
            'Other routes must get the language code.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testCreateUrlInsertsLanguageCodeAfterHostOfUppercaseScheme(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'rules' => [
                    [
                        'host' => 'HTTP://www.example.com',
                        'pattern' => 'upper',
                        'route' => 'site/upper',
                    ],
                ],
            ],
        );
        $this->mockRequest('/de/site/page');

        self::assertSame(
            'HTTP://www.example.com' . $this->prepareUrl('/de/upper'),
            Yii::$app->urlManager->createUrl(['site/upper']),
            'Scheme must be detected case-insensitively.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testCreateUrlInsertsLanguageCodeBeforePathContainingScheme(): void
    {
        $this->mockUrlLanguageManager(['languages' => ['en', 'de']]);
        $this->mockRequest('/de/site/page');

        self::assertSame(
            $this->prepareUrl('/de/go/http://example.com/x'),
            Yii::$app->urlManager->createUrl(['go/http://example.com/x']),
            'Code must follow the base URL, not the embedded scheme.',
        );
    }
}
