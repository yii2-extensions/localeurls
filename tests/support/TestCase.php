<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests\support;

use ReflectionClass;
use Yii;
use yii\base\{Exception, InvalidConfigException};
use yii\helpers\{ArrayHelper, Url};
use yii\web\{Application, NotFoundHttpException, UrlNormalizerRedirectException};
use yii2\extensions\localeurls\tests\provider\RequestFixtures;
use yii2\extensions\localeurls\tests\support\stub\ArraySession;
use yii2\extensions\localeurls\UrlLanguageManager;

use function array_column;
use function array_merge;
use function dirname;
use function explode;
use function is_array;
use function is_string;
use function ltrim;
use function parse_str;

/**
 * Base class for the {@see UrlLanguageManager} test suite.
 *
 * Builds a web application with an {@see ArraySession} per simulated request, resolves redirects the way
 * {@see Application::handleRequest()} does, and restores the request globals, the session data, the logger messages and
 * `Yii::$app` around each test.
 *
 * @phpstan-import-type Fixtures from RequestFixtures
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Base URL prefix for test scenarios.
     */
    protected string $baseUrl = '';
    /**
     * `$_SERVER` content captured before each test and restored by {@see resetEnvironment()}.
     *
     * @phpstan-var array<array-key, mixed>
     */
    protected array $serverBackup = [];
    /**
     * Whether to show the script name in generated URLs.
     */
    protected bool $showScriptName = false;
    /**
     * UrlManager component configuration for test scenarios.
     *
     * @phpstan-var array<string, mixed>
     */
    protected array $urlManager = [];

    /**
     * Applies the session, cookie and server fixtures of a provider row.
     *
     * @param array $fixtures Request fixtures built by {@see RequestFixtures::fixtures()}.
     *
     * @phpstan-param Fixtures $fixtures
     */
    protected function applyFixtures(array $fixtures): void
    {
        $this->withSession($fixtures['session']);
        $this->withCookies($fixtures['cookie']);
        $this->withServer($fixtures['server']);
    }

    /**
     * Asserts that no redirect is raised while resolving the request, and that the resolved route matches.
     *
     * @param string $from Relative request URL, without base URL and script name.
     * @param array $request Optional configuration for the request application component.
     * @param array|null $route Expected route and parameters, or `null` to skip the check.
     *
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     *
     * @return array Resolved route and parameters.
     *
     * @phpstan-param array<string, mixed> $request
     * @phpstan-param array<array-key, mixed>|null $route
     * @phpstan-return array<array-key, mixed>
     */
    protected function assertNoRedirect(string $from, array $request = [], array|null $route = null): array
    {
        $resolved = $this->resolveRedirect($from, $request);

        if (isset($resolved['url'])) {
            self::fail("Unexpected redirect to '{$resolved['url']}' for '{$from}'.");
        }

        if ($route !== null) {
            self::assertSame($route, $resolved['route'], "Resolved route must match for '{$from}'.");
        }

        return $resolved['route'];
    }

    /**
     * Asserts that resolving the request raises a redirect with the expected URL and status code.
     *
     * @param string $expectedUrl Expected relative URL, without base URL and script name.
     * @param string $from Relative request URL, without base URL and script name.
     * @param array $request Optional configuration for the request application component.
     * @param int $expectedStatusCode Expected HTTP status code.
     *
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     *
     * @phpstan-param array<string, mixed> $request
     */
    protected function assertRedirect(
        string $expectedUrl,
        string $from,
        array $request = [],
        int $expectedStatusCode = 302,
    ): void {
        $redirect = $this->resolveRedirect($from, $request);

        if (isset($redirect['route'])) {
            self::fail("Expected a redirect for '{$from}'.");
        }

        self::assertSame($this->prepareUrl($expectedUrl), $redirect['url'], "Redirect URL must match for '{$from}'.");
        self::assertSame(
            $expectedStatusCode,
            $redirect['statusCode'],
            "Redirect status code must match for '{$from}'.",
        );
    }

    /**
     * Returns the text of the messages recorded by the logger since the start of the test.
     *
     * @return array Logged message texts, in recording order.
     *
     * @phpstan-return list<mixed>
     */
    protected function loggedMessages(): array
    {
        return array_column(Yii::getLogger()->messages, 0);
    }

    /**
     * Mocks an HTTP request and resolves it through a new web application.
     *
     * @param string $url Relative request URL, without base URL and script name.
     * @param array $config Optional configuration for the request application component.
     *
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     *
     * @return array Resolved route and parameters, query parameters included.
     *
     * @phpstan-param array<string, mixed> $config
     * @phpstan-return array<array-key, mixed>
     */
    protected function mockRequest(string $url, array $config = []): array
    {
        $this->mockRequestApplication($url, $config);

        return Yii::$app->request->resolve();
    }

    /**
     * Sets the request globals for a URL and creates a web application without resolving the request.
     *
     * @param string $url Relative request URL, without base URL and script name.
     * @param array $config Optional configuration for the request application component.
     *
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     *
     * @phpstan-param array<string, mixed> $config
     */
    protected function mockRequestApplication(string $url, array $config = []): void
    {
        $url = $this->prepareUrl($url);

        $_SERVER['REQUEST_URI'] = $url;
        $_SERVER['SCRIPT_NAME'] = $this->baseUrl . '/index.php';
        $_SERVER['SCRIPT_FILENAME'] = dirname(__DIR__) . $this->baseUrl . '/index.php';
        $_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);

        [, $query] = explode('?', $url, 2) + [1 => ''];

        $_SERVER['QUERY_STRING'] = $query;

        parse_str($query, $_GET);

        if ($config !== []) {
            $config = [
                'components' => [
                    'request' => $config,
                ],
            ];
        }

        $this->mockWebApplication($config);
    }

    /**
     * Stores the {@see UrlLanguageManager} configuration used by the next application.
     *
     * @param array $config Configuration for the UrlLanguageManager component.
     *
     * @phpstan-param array<string, mixed> $config
     */
    protected function mockUrlLanguageManager(array $config = []): void
    {
        $this->urlManager = $config;
    }

    /**
     * Creates a web application with a {@see UrlLanguageManager} built from {@see $urlManager}.
     *
     * @param array $config Optional application configuration that overrides default settings.
     *
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     *
     * @phpstan-param array<string, mixed> $config
     */
    protected function mockWebApplication(array $config = []): void
    {
        $urlManager = ArrayHelper::merge(
            [
                'class' => UrlLanguageManager::class,
                'showScriptName' => $this->showScriptName,
            ],
            $this->urlManager,
        );

        new Application(
            ArrayHelper::merge(
                [
                    'id' => 'app',
                    'basePath' => dirname(__DIR__),
                    'language' => 'en',
                    'runtimePath' => dirname(__DIR__, 2) . '/runtime/',
                    'vendorPath' => dirname(__DIR__, 2) . '/vendor/',
                    'components' => [
                        'request' => [
                            'enableCookieValidation' => false,
                            'isConsoleRequest' => false,
                            'hostInfo' => 'http://localhost',
                        ],
                        'session' => [
                            'class' => ArraySession::class,
                        ],
                        'urlManager' => $urlManager,
                    ],
                ],
                $config,
            ),
        );
    }

    /**
     * Prepends the script name and the base URL of the current layout to a relative URL.
     *
     * @param string $url Relative URL to transform.
     *
     * @return string URL with script name and base URL applied.
     */
    protected function prepareUrl(string $url): string
    {
        if ($this->showScriptName) {
            $url = '/index.php' . $url;
        }

        return $this->baseUrl . $url;
    }

    /**
     * Restores the request globals captured before the test and clears the session data.
     */
    protected function resetEnvironment(): void
    {
        $_COOKIE = [];
        $_GET = [];
        $_POST = [];
        $_SESSION = [];
        $_SERVER = $this->serverBackup;

        $this->urlManager = [];
    }

    /**
     * Resolves the request and returns the redirect it raises, converted as {@see Application::handleRequest()} does,
     * or the resolved route when it does not redirect.
     *
     * @param string $from Relative request URL, without base URL and script name.
     * @param array $request Optional configuration for the request application component.
     *
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     *
     * @return array Redirect URL and status code, or the resolved route and parameters if the request does not
     * redirect.
     *
     * @phpstan-param array<string, mixed> $request
     * @phpstan-return array{url: string, statusCode: int}|array{route: array<array-key, mixed>}
     */
    protected function resolveRedirect(string $from, array $request = []): array
    {
        try {
            $route = $this->mockRequest($from, $request);
        } catch (UrlNormalizerRedirectException $e) {
            $url = $e->url;

            if (is_array($url)) {
                if (isset($url[0]) && is_string($url[0])) {
                    // ensure the route is absolute
                    $url[0] = '/' . ltrim($url[0], '/');
                }

                $url += Yii::$app->request->getQueryParams();
            }

            return [
                'url' => Url::to($url, $e->scheme),
                'statusCode' => $e->statusCode,
            ];
        }

        return ['route' => $route];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->serverBackup = $_SERVER;

        $_SESSION = [];

        Yii::getLogger()->flush(true);
    }

    protected function tearDown(): void
    {
        $this->resetEnvironment();

        (new ReflectionClass(Yii::class))->setStaticPropertyValue('app', null);

        parent::tearDown();
    }

    /**
     * Adds request cookies for the next simulated request.
     *
     * @param array $cookies Cookie values indexed by cookie name.
     *
     * @phpstan-param array<string, string> $cookies
     */
    protected function withCookies(array $cookies): void
    {
        $_COOKIE = array_merge($_COOKIE, $cookies);
    }

    /**
     * Adds server variables (for example, `HTTP_*` headers) for the next simulated request.
     *
     * @param array $server Server variable values indexed by variable name.
     *
     * @phpstan-param array<string, string> $server
     */
    protected function withServer(array $server): void
    {
        $_SERVER = array_merge($_SERVER, $server);
    }

    /**
     * Adds session data read by the {@see ArraySession} component of the next application.
     *
     * @param array $values Session values indexed by session key.
     *
     * @phpstan-param array<string, string> $values
     */
    protected function withSession(array $values): void
    {
        $_SESSION = array_merge($_SESSION, $values);
    }
}
