<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests;

use yii2\extensions\localeurls\UrlLanguageManager;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\di\Container;
use yii\helpers\ArrayHelper;
use yii\web\Application;
use yii\web\NotFoundHttpException;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var mixed Variable is used to keep the initial `$_SERVER` content to restore it after each test in `tearDown()`.
     */
    protected mixed $_server = null;

    /**
     * @var string Base URL prefix for test scenarios.
     */
    protected string $baseUrl = '';

    /**
     * @var array UrlManager component configuration for test scenarios.
     */
    protected array $urlManager = [];

    /**
     * @var bool Whether to show the script name in generated URLs.
     */
    protected bool $showScriptName = false;

    protected function setUp(): void
    {
        if ($this->_server === null) {
            $this->_server = $_SERVER;
        }
    }

    protected function tearDown(): void
    {
        $this->resetEnvironment();

        parent::tearDown();
    }

    /**
     * Sets up an expectation for a redirect exception with URL validation.
     *
     * Configures PHPUnit to expect a specific exception that indicates a redirect operation, validating that the
     * redirect URL matches the expected pattern after URL preprocessing.
     *
     * This method is essential for testing redirect scenarios in URL routing and language switching, ensuring that
     * redirects are triggered correctly and point to the expected destinations.
     *
     * @param string $url Expected redirect URL to validate against.
     */
    protected function expectRedirect(string $url): void
    {
        $url = $this->prepareUrl($url);

        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches("#^{$url}$#");
    }

    /**
     * Mocks an HTTP request with URL preparation and web application initialization.
     *
     * Configures the PHP environment to simulate an HTTP request by setting required `$_SERVER` variables and
     * initializing a Yii web application instance for request processing.
     *
     * This method handles URL preprocessing, query string parsing, and request resolution, making it suitable for
     * testing URL routing, language detection, and request handling scenarios.
     *
     * The request simulation includes proper setup of script names, document root, and query parameters to ensure
     * realistic testing conditions that match production environments.
     *
     * @param string $url Relative request URL to simulate.
     * @param array $config Optional configuration for the request application component.
     *
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     *
     * @phpstan-param array<string, mixed> $config
     */
    protected function mockRequest(string $url, array $config = []): void
    {
        $url = $this->prepareUrl($url);

        $_SERVER['REQUEST_URI'] = $url;
        $_SERVER['SCRIPT_NAME'] = $this->baseUrl . '/index.php';
        $_SERVER['SCRIPT_FILENAME'] = __DIR__ . $this->baseUrl . '/index.php';
        $_SERVER['DOCUMENT_ROOT'] = __DIR__;

        $parts = explode('?', $url);

        if (isset($parts[1])) {
            $_SERVER['QUERY_STRING'] = $parts[1];

            parse_str($parts[1], $_GET);
        } else {
            $_GET = [];
        }

        if ($config !== []) {
            $config = [
                'components' => [
                    'request' => $config,
                ],
            ];
        }

        $this->mockWebApplication($config);

        Yii::$app->request->resolve();
    }

    /**
     * Configures the URL language manager component for testing scenarios.
     *
     * Sets up the configuration for the {@see UrlLanguageManager} component that will be used during test execution.
     *
     * This method prepares the language-aware URL management configuration without initializing the actual web
     * application, allowing tests to define specific language settings before making mock requests.
     *
     * @param array $config Configuration array for the UrlLanguageManager component.
     *
     * @phpstan-param array<string, mixed> $config
     */
    protected function mockUrlLanguageManager(array $config = []): void
    {
        $this->urlManager = $config;
    }

    /**
     * Creates a mock Yii web application instance with pre-configured test settings.
     *
     * Initializes a complete Yii web application for testing purposes configured with essential parts and default
     * settings suitable for unit and integration testing scenarios.
     *
     * The application is configured with a locale-aware URL manager using the {@see UrlLanguageManager} class, which
     * enables testing of internationalized routing and URL generation features.
     *
     * @param array $config Optional application configuration that overrides default settings.
     *
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     *
     * @phpstan-param array<string, mixed> $config
     */
    protected function mockWebApplication(array $config = []): void
    {
        new Application(
            ArrayHelper::merge(
                [
                    'id' => 'testapp',
                    'language' => 'en',
                    'basePath' => __DIR__,
                    'vendorPath' => __DIR__ . '/../vendor/',
                    'components' => [
                        'request' => [
                            'enableCookieValidation' => false,
                            'isConsoleRequest' => false,
                            'hostInfo' => 'http://localhost',
                        ],
                        'urlManager' => ArrayHelper::merge(
                            [
                                'class' => UrlLanguageManager::class,
                                'showScriptName' => $this->showScriptName,
                            ],
                            $this->urlManager
                        ),
                    ],
                ],
                $config,
            ),
        );
    }

    /**
     * Prepares a URL by applying script name and base URL configuration for testing scenarios.
     *
     * Transforms relative URLs into complete test URLs by conditionally prepending the script name and base URL based
     * on the current test configuration settings.
     *
     * @param string $url Relative URL to transform into a complete test URL.
     *
     * @return string Complete URL with script name and base URL applied according to test configuration.
     */
    protected function prepareUrl(string $url): string
    {
        if ($this->showScriptName) {
            $url = '/index.php' . $url;
        }

        return $this->baseUrl . $url;
    }

    /**
     * Resets the PHP and Yii environment to a clean state after each test.
     *
     * Restores global variables and application state to ensure test isolation and prevent side effects between test
     * cases.
     */
    protected function resetEnvironment(): void
    {
        $_COOKIE = [];
        $_SESSION = [];
        $_SERVER = $this->_server;

        if (isset(Yii::$app)) {
            Yii::$app->session->destroy();
            Yii::$app = null;
            Yii::$container = new Container();
        }

        $this->urlManager = [];
    }
}
