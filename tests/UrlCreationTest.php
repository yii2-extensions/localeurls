<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests;

use PHPUnit\Framework\Attributes\Group;
use Yii;
use yii\base\{Exception, InvalidConfigException};
use yii\web\NotFoundHttpException;
use yii2\extensions\localeurls\tests\base\AbstractUrlCreation;
use yii2\extensions\localeurls\tests\provider\UrlCreationProvider;
use yii2\extensions\localeurls\UrlLanguageManager;

/**
 * Unit tests for {@see UrlLanguageManager} URL creation with no base URL and no script name.
 *
 * {@see UrlCreationProvider} for test case data providers.
 */
#[Group('locale-urls')]
final class UrlCreationTest extends AbstractUrlCreation
{
    /**
     * Only this layout keeps a longer scheme intact: Yii inserts the base URL of absolute URLs at offset `8`.
     *
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testCreateUrlInsertsLanguageCodeAfterHostOfSixLetterScheme(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'rules' => [
                    [
                        'host' => 'custom://www.example.com',
                        'pattern' => 'scheme',
                        'route' => 'site/scheme',
                    ],
                ],
            ],
        );
        $this->mockRequest('/de/site/page');

        self::assertSame(
            'custom://www.example.com/de/scheme',
            Yii::$app->urlManager->createUrl(['site/scheme']),
            'Code must follow the host, not the scheme delimiter.',
        );
    }
}
