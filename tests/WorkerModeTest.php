<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests;

use PHPUnit\Framework\Attributes\{DataProvider, Group};
use RuntimeException;
use Yii;
use yii\base\{Exception, InvalidConfigException};
use yii\web\{NotFoundHttpException, Request, UrlNormalizer, UrlNormalizerRedirectException};
use yii2\extensions\localeurls\tests\support\stub\{
    ExceptionUrlRule,
    ExtendedUrlLanguageManager,
    ServerParamsRequest,
    UrlRule,
};
use yii2\extensions\localeurls\tests\support\TestCase;
use yii2\extensions\localeurls\UrlLanguageManager;

use function array_filter;
use function ltrim;

/**
 * Unit tests for {@see UrlLanguageManager} behavior in long-running workers: redirects raised as
 * {@see UrlNormalizerRedirectException}, per-request processing, request-scoped GeoIP lookup, and the protected helper
 * contract for subclasses.
 */
#[Group('locale-urls')]
final class WorkerModeTest extends TestCase
{
    /**
     * Provides custom rule redirects whose URL has no string route at index `0`.
     *
     * @phpstan-return array<string, array{array<array-key, mixed>}>
     */
    public static function redirectUrlsWithoutRoute(): array
    {
        return [
            'missing route' => [['page' => 'x']],
            'non-string route' => [[5, 'page' => 'x']],
        ];
    }

    /**
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testAppliesDefaultLanguageBeforeParsingNewRequest(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'defaultLanguage' => 'en',
            ],
        );
        $this->mockRequestApplication('/site/page');

        // Simulates the language left by the previous request of a long-running worker.
        Yii::$app->language = 'de';

        $urlManager = Yii::$app->urlManager;

        self::assertSame(
            'en',
            $urlManager->getDefaultLanguage(),
            'Configured value must win over the leaked one.',
        );
        self::assertSame(
            ['site/page', []],
            $urlManager->parseRequest(Yii::$app->request),
            'URL without a code must not redirect.',
        );
        self::assertSame('en', Yii::$app->language, 'Leaked language must be reset.');
        self::assertNull(
            Yii::$app->response->cookies->get('_language'),
            'No language must be persisted.',
        );
    }

    /**
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testAppliesDefaultLanguageToIgnoredUrl(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'defaultLanguage' => 'en',
                'ignoreLanguageUrlPatterns' => ['#^api/#' => '#^api/#'],
            ],
        );
        $this->mockRequestApplication('/api/items');

        Yii::$app->language = 'de';

        self::assertSame(
            ['api/items', []],
            Yii::$app->urlManager->parseRequest(Yii::$app->request),
            'Ignored URL must be parsed as is.',
        );
        self::assertSame(
            'en',
            Yii::$app->language,
            'Leaked language must be reset.',
        );
    }

    /**
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testCapturesCurrentApplicationLanguageWhenDefaultLanguageIsNull(): void
    {
        $this->mockUrlLanguageManager(['languages' => ['en', 'de']]);
        $this->mockRequestApplication('/site/page');

        Yii::$app->language = 'de';

        $urlManager = Yii::$app->urlManager;

        self::assertNull(
            $urlManager->defaultLanguage,
            'Default value must be `null`.',
        );
        self::assertSame(
            'de',
            $urlManager->getDefaultLanguage(),
            'Current language must be captured.',
        );
        self::assertSame(
            ['site/page', []],
            $urlManager->parseRequest(Yii::$app->request),
            'URL without a code must not redirect.',
        );
        self::assertSame(
            'de',
            Yii::$app->language,
            'Language must not be reset.',
        );
    }

    /**
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     */
    public function testCapturesDefaultLanguageFromApplicationConfiguration(): void
    {
        $this->mockUrlLanguageManager(['languages' => ['en', 'de']]);
        $this->mockWebApplication(['language' => 'de']);

        self::assertSame(
            'de',
            Yii::$app->urlManager->getDefaultLanguage(),
            'Default language must come from the application.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testDetectsGeoIpLanguageFromHeaderWhenDetectionIsDisabled(): void
    {
        $this->withServer(['HTTP_X_GEO_COUNTRY' => 'DEU']);

        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'enableLanguageDetection' => false,
                'geoIpLanguageCountries' => ['de' => ['DEU']],
            ],
        );
        $this->assertRedirect(
            '/de/site/page',
            '/site/page',
            ['acceptableLanguages' => ['en']],
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testDetectsGeoIpLanguageFromRequestHeaderCollection(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'geoIpLanguageCountries' => ['de' => ['DEU']],
            ],
        );
        $this->mockRequestApplication('/site/page');

        Yii::$app->request->getHeaders()->set('X-Geo-Country', 'DEU');

        try {
            Yii::$app->urlManager->parseRequest(Yii::$app->request);
        } catch (UrlNormalizerRedirectException $e) {
            self::assertSame(
                '/de/site/page',
                $e->url,
                'Header country must select the language.',
            );

            return;
        }

        self::fail(
            'Expected a language redirect.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testDetectsGeoIpLanguageFromRequestServerParams(): void
    {
        $this->withServer(['GEOIP_COUNTRY_CODE' => 'USA']);

        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'geoIpLanguageCountries' => ['de' => ['DEU'], 'en' => ['USA']],
                'geoIpServerVar' => 'GEOIP_COUNTRY_CODE',
            ],
        );
        $this->assertRedirect(
            '/de/site/page',
            '/site/page',
            [
                'class' => ServerParamsRequest::class,
                'serverParams' => ['GEOIP_COUNTRY_CODE' => 'DEU'],
            ],
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testDetectsGeoIpLanguageFromServerGlobalForNonHeaderVariable(): void
    {
        $this->withServer(['GEOIP_COUNTRY_CODE' => 'DEU']);

        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'geoIpLanguageCountries' => ['de' => ['DEU']],
                'geoIpServerVar' => 'GEOIP_COUNTRY_CODE',
            ],
        );
        $this->assertRedirect('/de/site/page', '/site/page');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testDoesNotDetectGeoIpLanguageWhenServerVariableIsMissing(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'geoIpLanguageCountries' => ['de' => ['DEU']],
                'geoIpServerVar' => 'GEOIP_COUNTRY_CODE',
            ],
        );
        $this->assertNoRedirect('/site/page', [], ['site/page', []]);

        self::assertSame(
            'en',
            Yii::$app->language,
            'Language must stay the default.',
        );
    }

    /**
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testDoesNotResetLanguageWhenProcessedRequestIsParsedAgain(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'defaultLanguage' => 'en',
            ],
        );
        $this->mockRequestApplication('/de/site/page');

        $urlManager = Yii::$app->urlManager;
        $request = Yii::$app->request;

        self::assertSame(
            ['site/page', []],
            $urlManager->parseRequest($request),
            'Code must be removed.',
        );
        self::assertSame(
            'de',
            Yii::$app->language,
            'Language must be set from the URL.',
        );
        self::assertSame(
            ['site/page', []],
            $urlManager->parseRequest($request),
            'Route must stay the same.',
        );
        self::assertSame(
            'de',
            Yii::$app->language,
            'Language of a processed request must be kept.',
        );
    }

    /**
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     */
    public function testHandleRequestReturnsLanguageRedirectResponseWithoutTerminating(): void
    {
        $this->withCookies(['_language' => 'de']);

        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'languageRedirectCode' => 301,
            ],
        );
        $this->mockRequestApplication('/site/page');

        $response = Yii::$app->handleRequest(Yii::$app->request);

        self::assertSame(
            301,
            $response->getStatusCode(),
            'Status code must be `languageRedirectCode`.',
        );
        self::assertSame(
            'http://localhost/de/site/page',
            $response->getHeaders()->get('Location'),
            'Location must point to the language URL.',
        );
        self::assertFalse(
            $response->isSent,
            'Sending must be left to the application.',
        );
    }

    /**
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testKeepsRequestsIndependentWhenOneManagerParsesSeveralRequests(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'defaultLanguage' => 'en',
                'geoIpLanguageCountries' => ['de' => ['DEU']],
            ],
        );
        $this->mockRequestApplication('/');

        $urlManager = Yii::$app->urlManager;

        $geoIp = $this->createWorkerRequest('/site/page');

        $geoIp->getHeaders()->set('X-Geo-Country', 'DEU');

        try {
            $urlManager->parseRequest($geoIp);

            self::fail(
                'Expected a GeoIP language redirect.',
            );
        } catch (UrlNormalizerRedirectException $e) {
            self::assertSame(
                '/de/site/page',
                $e->url,
                'GeoIP country must select the language.',
            );
            self::assertSame(
                302,
                $e->statusCode,
                'Status code must be `languageRedirectCode`.',
            );
        }

        self::assertSame(
            'en',
            Yii::$app->language,
            'Redirect must not apply the detected language.',
        );

        $plain = $this->createWorkerRequest('/site/page');

        self::assertSame(
            ['site/page', []],
            $urlManager->parseRequest($plain),
            'GeoIP must not carry over.',
        );
        self::assertSame(
            'en',
            Yii::$app->language,
            'Default language must apply.',
        );
        self::assertSame(
            'en',
            $urlManager->getDefaultLanguage(),
            'Default language must not drift.',
        );

        $german = $this->createWorkerRequest('/de/site/page');

        self::assertSame(
            ['site/page', []],
            $urlManager->parseRequest($german),
            'Code must be removed.',
        );
        self::assertSame(
            'de',
            Yii::$app->language,
            'Language must be set from the URL.',
        );
        self::assertSame(
            'de',
            Yii::$app->response->cookies->getValue('_language'),
            'Language from the URL must be persisted.',
        );

        // Ends the request as a worker does: the next visitor has no session data and gets a new response.
        Yii::$app->session->removeAll();
        Yii::$app->response->clear();

        $next = $this->createWorkerRequest('/site/page');

        self::assertSame(
            ['site/page', []],
            $urlManager->parseRequest($next),
            'Next visitor must not redirect.',
        );
        self::assertSame(
            'en',
            Yii::$app->language,
            'Language of the previous visitor must not leak.',
        );
        self::assertSame(
            'en',
            $urlManager->getDefaultLanguage(),
            'Default language must not drift.',
        );
        self::assertNull(
            Yii::$app->response->cookies->get('_language'),
            'No language must be persisted.',
        );

        Yii::$app->language = 'de';

        self::assertSame(
            ['site/page', []],
            $urlManager->parseRequest($geoIp),
            'Processed request must be parsed without a redirect.',
        );
        self::assertSame(
            'de',
            Yii::$app->language,
            'Processed request must not reset the language.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testKeepsSchemeOfCustomRuleRedirectMergedWithPendingLanguageRedirect(): void
    {
        $this->withCookies(['_language' => 'de']);

        $this->mockExceptionRuleManager(
            new UrlNormalizerRedirectException(['site/new', 'a' => 'b'], 301, 'https'),
            'site/old',
        );
        $this->assertRedirect('https://localhost/de/site/new?a=b', '/site/old');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testMergesCustomRuleRedirectWithPendingLanguageRedirect(): void
    {
        $this->withCookies(['_language' => 'de']);

        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'rules' => [
                    ['class' => UrlRule::class],
                ],
            ],
        );
        $this->assertRedirect('/de/ruleclass-english', '/ruleclass-deutsch');
    }

    /**
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testParseRequestProcessesEachRequestObjectOnce(): void
    {
        $this->mockUrlLanguageManager(['languages' => ['en', 'de']]);
        $this->mockRequestApplication('/de/site/page');

        $urlManager = Yii::$app->urlManager;
        $request = Yii::$app->request;

        $first = $urlManager->parseRequest($request);

        self::assertSame(
            ['site/page', []],
            $first,
            'Route must be parsed from the stripped path info.',
        );
        self::assertSame(
            $first,
            $urlManager->parseRequest($request),
            'Repeated parse must return the same route.',
        );
        self::assertSame(
            'site/page',
            $request->getPathInfo(),
            'Language code must be stripped once.',
        );

        $other = new Request();

        $other->setPathInfo('de/other/page');

        self::assertSame(
            ['other/page', []],
            $urlManager->parseRequest($other),
            'New request must be processed.',
        );
        self::assertSame(
            $first,
            $urlManager->parseRequest($request),
            'First request must stay processed.',
        );

        $messages = $this->loggedMessages();

        $persisted = array_filter(
            $messages,
            static fn(mixed $message): bool => $message === "Persisting language 'de' in session.",
        );

        self::assertCount(
            2,
            $persisted,
            'Language must be persisted once per request object.',
        );
        self::assertNotContains(
            "Found persisted language 'de' in session.",
            $messages,
            'Processed request must not resolve its language again.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testPreservesParameterKeysOfCustomRuleArrayRedirect(): void
    {
        $this->mockExceptionRuleManager(
            new UrlNormalizerRedirectException(['site/new', 5 => 'x', 'a' => 'b'], 301),
            'site/old',
        );
        $this->assertRedirect('/de/site/new?5=x&a=b', '/de/site/old', [], 301);
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsToRelativeStringUrlOfCustomRuleUnchanged(): void
    {
        $this->mockExceptionRuleManager(new UrlNormalizerRedirectException('/legacy.html'), 'old');
        $this->assertRedirect('/legacy.html', '/de/old');
    }

    /**
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     *
     * @phpstan-param array<array-key, mixed> $url
     */
    #[DataProvider('redirectUrlsWithoutRoute')]
    public function testRethrowsCustomRuleRedirectWithoutStringRoute(array $url): void
    {
        $exception = new UrlNormalizerRedirectException($url);

        $this->mockExceptionRuleManager($exception, 'old');
        $this->mockRequestApplication('/de/old');

        try {
            Yii::$app->urlManager->parseRequest(Yii::$app->request);
        } catch (UrlNormalizerRedirectException $e) {
            self::assertSame(
                $exception,
                $e,
                'Exception of the rule must be rethrown as is.',
            );

            return;
        }

        self::fail(
            'Expected the redirect of the rule.',
        );
    }

    /**
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRethrowsCustomRuleRedirectWithStringUrl(): void
    {
        $this->withCookies(['_language' => 'de']);

        $exception = new UrlNormalizerRedirectException('https://example.com/x?y=1', 301);

        $this->mockExceptionRuleManager($exception, 'legacy');
        $this->mockRequestApplication('/legacy');

        try {
            Yii::$app->urlManager->parseRequest(Yii::$app->request);
        } catch (UrlNormalizerRedirectException $e) {
            self::assertSame(
                $exception,
                $e,
                'Exception of the rule must be rethrown as is.',
            );

            return;
        }

        self::fail(
            'Expected the redirect of the rule.',
        );
    }

    /**
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     */
    public function testReturnsAbsoluteStringUrlOfCustomRuleAsLocationWhilePendingLanguageRedirect(): void
    {
        $this->withCookies(['_language' => 'de']);

        $this->mockExceptionRuleManager(
            new UrlNormalizerRedirectException('https://example.com/x?y=1', 301),
            'legacy',
        );
        $this->mockRequestApplication('/legacy');

        $response = Yii::$app->handleRequest(Yii::$app->request);

        self::assertSame(
            301,
            $response->getStatusCode(),
            'Status code of the rule must be kept.',
        );
        self::assertSame(
            'https://example.com/x?y=1',
            $response->getHeaders()->get('Location'),
            'Location must be the URL of the rule.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testSubclassOverridesProtectedHelpersWithRequestArgument(): void
    {
        $this->mockUrlLanguageManager(
            [
                'class' => ExtendedUrlLanguageManager::class,
                'languages' => ['en', 'de'],
            ],
        );
        $this->mockRequest('/de/site/page');

        $urlManager = Yii::$app->urlManager;

        self::assertInstanceOf(
            ExtendedUrlLanguageManager::class,
            $urlManager,
            'Subclass must be used.',
        );
        self::assertSame(
            ['processLocaleUrl', 'matchCode', 'persistLanguage'],
            $urlManager->calls,
            'Language code in the URL must reach the overrides.',
        );

        $this->resetEnvironment();
        $this->mockUrlLanguageManager(
            [
                'class' => ExtendedUrlLanguageManager::class,
                'languages' => ['en', 'de'],
            ],
        );
        $this->assertRedirect('/de/site/page', '/site/page', ['acceptableLanguages' => ['de']]);

        $urlManager = Yii::$app->urlManager;

        self::assertInstanceOf(
            ExtendedUrlLanguageManager::class,
            $urlManager,
            'Subclass must be used.',
        );
        self::assertSame(
            ['processLocaleUrl', 'loadPersistedLanguage', 'detectLanguage', 'matchCode', 'matchCode'],
            $urlManager->calls,
            'Detected language must reach the overrides.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the application configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testThrowRuntimeExceptionWhenCustomRuleFails(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'normalizer' => ['class' => UrlNormalizer::class],
                'rules' => [
                    new ExceptionUrlRule(
                        [
                            'exception' => new RuntimeException('Rule failure.'),
                            'pathInfo' => 'site/page',
                        ],
                    ),
                ],
            ],
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Rule failure.');

        $this->mockRequest('/de/site/page');
    }

    /**
     * Creates a request for a URL, as a worker does for each incoming request.
     *
     * @param string $url Request URL, without base URL and script name.
     */
    private function createWorkerRequest(string $url): Request
    {
        $request = new Request(['enableCookieValidation' => false]);

        $request->setUrl($url);
        $request->setPathInfo(ltrim($url, '/'));

        return $request;
    }

    /**
     * Configures the `en` and `de` languages with a rule that throws the given redirect for a path info.
     *
     * @param UrlNormalizerRedirectException $exception Redirect thrown by the rule.
     * @param string $pathInfo Path info that triggers the redirect.
     */
    private function mockExceptionRuleManager(UrlNormalizerRedirectException $exception, string $pathInfo): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'rules' => [
                    new ExceptionUrlRule(
                        [
                            'exception' => $exception,
                            'pathInfo' => $pathInfo,
                        ],
                    ),
                ],
            ],
        );
    }
}
