<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests\base;

use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii2\extensions\localeurls\tests\TestCase;
use yii2\extensions\localeurls\UrlLanguageManager;

use function array_column;

/**
 * Base class for language-aware URL manager and language detection tests in the Yii2 LocaleUrls extension.
 *
 * Provides comprehensive tests for language code extraction, persistence, and normalization, ensuring correct handling
 * of language selection, alias mapping, disabling of detection or persistence, and robust fallback logic across
 * multiple configuration scenarios.
 *
 * This class validates the LocaleUrls language manager by simulating language detection and persistence using cookies,
 * sessions, and HTTP headers, as well as configuration options for disabling or customizing these mechanisms.
 *
 * It covers normalization, alias and wildcard code handling, script code and case normalization, and the consistent
 * extraction and fallback of language codes for both relative and absolute URLs.
 *
 * Test coverage.
 * - Consistent language state across request, session, and cookie.
 * - Default language selection and fallback logic.
 * - Disabling language cookie, session, or both independently.
 * - Disabling language detection and persistence.
 * - Handling of invalid or missing language codes in cookies and sessions.
 * - Ignored URL patterns and locale URLs disabled scenarios.
 * - Language alias and wildcard code handling in URLs.
 * - Script code and case normalization in language codes.
 * - URL parsing and path extraction with and without language codes.
 *
 * @copyright Copyright (C) 2023 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
abstract class AbstractUrlLanguageManager extends TestCase
{
    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testCanDisableCookieOnly(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de'],
                'languageCookieDuration' => false,
            ],
        );

        $this->mockRequest('/en-us/site/page');

        $this->assertSame(
            'en-US',
            Yii::$app->language,
            'Application language should be set to \'en-US\' when language is present in the URL and cookie is ' .
            'disabled.',
        );
        $this->assertSame(
            'en-US',
            Yii::$app->session->get('_language'),
            'Session \'_language\' should be set to \'en-US\' when language is present in the URL and cookie is ' .
            'disabled.',
        );
        $this->assertNull(
            Yii::$app->response->cookies->get('_language'),
            'Language cookie should not be set when \'languageCookieDuration\' is false.',
        );

        $request = Yii::$app->request;

        $this->assertSame(
            'site/page',
            $request->pathInfo,
            'Request pathInfo should be \'site/page\' after language code is removed from the URL.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testCanDisableLanguageDetection(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de'],
                'enableLanguageDetection' => false,
            ],
        );

        $this->mockRequest(
            '/site/page',
            [
                'acceptableLanguages' => ['de'],
            ],
        );

        $this->assertSame(
            'en',
            Yii::$app->language,
            'Application language should default to \'en\' when language detection is disabled, regardless of ' .
            'acceptable languages.',
        );

        $request = Yii::$app->request;

        $this->assertSame(
            'site/page',
            $request->pathInfo,
            'Request pathInfo should be \'site/page\' when no language code is present in the URL.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testCanDisablePersistence(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de'],
                'enableLanguagePersistence' => false,
            ],
        );

        $this->mockRequest('/en-us/site/page');

        $this->assertSame(
            'en-US',
            Yii::$app->language,
            'Application language should be set to \'en-US\' from the URL even when language persistence is disabled.',
        );
        $this->assertNull(
            Yii::$app->session->get('_language'),
            'Session \'_language\' should not be set when \'enableLanguagePersistence\' is false.',
        );
        $this->assertNull(
            Yii::$app->response->cookies->get('_language'),
            'Language cookie should not be set when \'enableLanguagePersistence\' is false.',
        );

        $request = Yii::$app->request;

        $this->assertSame(
            'site/page',
            $request->pathInfo,
            'Request pathInfo should be \'site/page\' after language code is removed from the URL.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testCanDisableSessionOnly(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de'],
                'languageSessionKey' => false,
            ],
        );

        $this->mockRequest('/en-us/site/page');

        $this->assertSame(
            'en-US',
            Yii::$app->language,
            'Application language should be set to \'en-US\' from the URL when session persistence is disabled.',
        );

        $cookie = Yii::$app->response->cookies->get('_language');

        $this->assertNotNull(
            $cookie,
            "Language cookie should be set when 'languageSessionKey' is false and language is present in the URL.",
        );
        $this->assertSame(
            'en-US',
            $cookie->value,
            'Language cookie value should be \'en-US\' when session persistence is disabled and language is present ' .
            'in the URL.',
        );

        $request = Yii::$app->request;

        $this->assertSame(
            'site/page',
            $request->pathInfo,
            'Request pathInfo should be \'site/page\' after language code is removed from the URL.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testCanUseLanguageAliasInUrl(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'deutsch' => 'de'],
            ],
        );

        $this->mockRequest('/deutsch/site/page');

        $this->assertSame(
            'de',
            Yii::$app->language,
            'Application language should be set to \'de\' when using a language alias in the URL.',
        );
        $this->assertSame(
            'de',
            Yii::$app->session->get('_language'),
            'Session \'_language\' should be set to \'de\' when using a language alias in the URL.',
        );

        $cookie = Yii::$app->response->cookies->get('_language');


        $this->assertNotNull(
            $cookie,
            'Language cookie should be set when using a language alias in the URL.',
        );
        $this->assertSame(
            'de',
            $cookie->value,
            'Language cookie value should be \'de\' when using a language alias in the URL.',
        );

        $request = Yii::$app->request;

        $this->assertSame(
            'site/page',
            $request->pathInfo,
            'Request pathInfo should be \'site/page\' after language alias is removed from the URL.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testCanUseLanguageWithScriptCode(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'deutsch' => 'de', 'sr-Latn'],
            ],
        );

        $this->mockRequest('/sr-latn/site/page');

        $this->assertSame(
            'sr-Latn',
            Yii::$app->language,
            'Application language should be set to \'sr-Latn\' when using a language with script code in the URL.',
        );
        $this->assertSame(
            'sr-Latn',
            Yii::$app->session->get('_language'),
            'Session \'_language\' should be set to \'sr-Latn\' when using a language with script code in the URL.',
        );

        $cookie = Yii::$app->response->cookies->get('_language');

        $this->assertNotNull(
            $cookie,
            'Language cookie should be set when using a language with script code in the URL.',
        );
        $this->assertSame(
            'sr-Latn',
            $cookie->value,
            'Language cookie value should be \'sr-Latn\' when using a language with script code in the URL.',
        );

        $request = Yii::$app->request;

        $this->assertSame(
            'site/page',
            $request->pathInfo,
            'Request pathInfo should be \'site/page\' after language with script code is removed from the URL.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testCanUseLanguageWithWildcardCountryInUrl(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'deutsch' => 'de', 'es-*'],
            ],
        );

        $this->mockRequest('/es-bo/site/page');

        $this->assertSame(
            'es-BO',
            Yii::$app->language,
            'Application language should be set to \'es-BO\' when using a wildcard country language in the URL.',
        );
        $this->assertSame(
            'es-BO',
            Yii::$app->session->get('_language'),
            'Session \'_language\' should be set to \'es-BO\' when using a wildcard country language in the URL.',
        );

        $cookie = Yii::$app->response->cookies->get('_language');

        $this->assertNotNull(
            $cookie,
            'Language cookie should be set when using a wildcard country language in the URL.',
        );
        $this->assertSame(
            'es-BO',
            $cookie->value,
            'Language cookie value should be \'es-BO\' when using a wildcard country language in the URL.',
        );

        $request = Yii::$app->request;

        $this->assertSame(
            'site/page',
            $request->pathInfo,
            'Request pathInfo should be \'site/page\' after wildcard country language is removed from the URL.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testDoesNothingIfInvalidLanguageInCookie(): void
    {
        $_COOKIE['_language'] = 'fr';

        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de'],
            ],
        );

        $this->expectNotToPerformAssertions();

        $this->mockRequest('/site/page');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testDoesNothingIfInvalidLanguageInSession(): void
    {
        @session_start();

        $_SESSION['_language'] = 'fr';

        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de'],
            ],
        );

        $this->expectNotToPerformAssertions();

        $this->mockRequest('/site/page');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testDoesNothingIfLocaleUrlsDisabled(): void
    {
        $this->mockUrlLanguageManager(
            [
                'enableLocaleUrls' => false,
                'languages' => ['en-US', 'en', 'de'],
                'rules' => [
                    '' => 'site/index',
                ],
            ],
        );

        $this->mockRequest(
            '/site/page',
            [
                'acceptableLanguages' => ['de'],
            ],
        );

        $this->assertSame(
            'en',
            Yii::$app->language,
            'Application language should default to \'en\' when locale URLs are disabled, regardless of acceptable ' .
            'languages.',
        );

        $request = Yii::$app->request;

        $this->assertSame(
            'site/page',
            $request->pathInfo,
            'Request pathInfo should be \'site/page\' when locale URLs are disabled and no language code is present ' .
            'in the URL.',
        );

        // If a URL rule is configured for the home URL, it will always have a trailing slash
        $this->assertSame(
            $this->prepareUrl('/'),
            Url::to(['/site/index']),
            'Home URL should always have a trailing slash when a URL rule is configured for it.',
        );
        $this->assertSame(
            $this->prepareUrl('/?x=y'),
            Url::to(['/site/index', 'x' => 'y']),
            'Home URL with query parameters should have a trailing slash when a URL rule is configured for it.',
        );

        // Other URLs have no trailing slash
        $this->assertSame(
            $this->prepareUrl('/site/test'),
            Url::to(['/site/test']),
            'Other URLs should not have a trailing slash when locale URLs are disabled.',
        );
        $this->assertSame(
            $this->prepareUrl('/site/test?x=y'),
            Url::to(['/site/test', 'x' => 'y']),
            'Other URLs with query parameters should not have a trailing slash when locale URLs are disabled.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testDoesNothingIfNoLanguagesConfigured(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => [],
            ],
        );

        $this->mockRequest(
            '/site/page',
            [
                'acceptableLanguages' => ['de'],
            ],
        );

        $this->assertSame(
            'en',
            Yii::$app->language,
            'Application language should default to \'en\' when no languages are configured, regardless of ' .
            'acceptable languages.',
        );

        $request = Yii::$app->request;

        $this->assertSame(
            'site/page',
            $request->pathInfo,
            'Request pathInfo should be \'site/page\' when no languages are configured and no language code is ' .
            'present in the URL.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testDoesNothingIfUrlMatchesIgnoresUrls(): void
    {
        Yii::getLogger()->flush(true);

        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de'],
                'ignoreLanguageUrlPatterns' => [
                    '#not/used#' => '#^site/page#',
                ],
            ],
        );

        $this->mockRequest(
            '/site/page',
            [
                'acceptableLanguages' => ['de'],
            ],
        );

        $this->assertSame(
            'en',
            Yii::$app->language,
            'Application language should remain \'en\' when the URL matches an ignored pattern, regardless of ' .
            'acceptable languages.',
        );

        $request = Yii::$app->request;

        $this->assertSame(
            'site/page',
            $request->pathInfo,
            'Request pathInfo should remain \'site/page\' when the URL matches an ignored pattern and no language ' .
            'code is present.',
        );

        $loggerMessages = Yii::getLogger()->messages;
        $expectedMessages = array_column($loggerMessages, 0);

        $this->assertContains(
            'Ignore pattern \'#^site/page#\' matches \'site/page.\' Skipping language processing.',
            $expectedMessages,
            'First log message should indicate that the URL is ignored due to matching an ignored pattern.',
        );
    }

    public function testHandlesLanguageCodesWithMultipleDashes(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-*', 'es', 'de'],
                'enableLanguageDetection' => true,
                'keepUppercaseLanguageCode' => false,
            ],
        );

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US-variant;q=0.9,en;q=0.8,es;q=0.7';

        try {
            $this->mockRequest('/site/page');

            $this->fail('Expected redirection exception was not thrown.');
        } catch (Exception $e) {
            $this->assertStringContainsString(
                '/en-us-variant/site/page',
                $e->getMessage(),
                'Redirect URL should preserve all parts after first dash when explode limit is \'2\'.',
            );
        }

        $this->mockUrlLanguageManager(
            [
                'languages' => ['es-*', 'en', 'de'],
                'enableLanguageDetection' => true,
                'keepUppercaseLanguageCode' => false,
            ],
        );

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'es-MX-variant-extended;q=0.9,es;q=0.8,en;q=0.7';

        try {
            $this->mockRequest('/site/page');

            $this->fail('Expected redirection exception was not thrown for second test case.');
        } catch (Exception $e) {
            $this->assertStringContainsString(
                '/es-mx-variant-extended/site/page',
                $e->getMessage(),
                'Redirect URL should handle multiple dashes correctly with explode limit of \'2\'.',
            );
        }
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testSetsDefaultLanguageIfNoLanguageSpecified(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de'],
            ],
        );

        $this->mockRequest('/');

        $this->assertSame(
            'en',
            Yii::$app->language,
            'Application language should default to \'en\' when no language code is specified in the URL.',
        );

        $request = Yii::$app->request;

        $this->assertSame(
            '',
            $request->pathInfo,
            'Request pathInfo should be empty when no language code is specified and the URL is root (\'/\').',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testSetsLanguageFromUrl(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de'],
            ],
        );

        $this->mockRequest('/en-us/site/page');

        $this->assertSame(
            'en-US',
            Yii::$app->language,
            'Application language should be set to \'en-US\' when the language code is present in the URL.',
        );
        $this->assertSame(
            'en-US',
            Yii::$app->session->get('_language'),
            'Session \'_language\' should be set to \'en-US\' when the language code is present in the URL.',
        );

        $cookie = Yii::$app->response->cookies->get('_language');

        $this->assertNotNull(
            $cookie,
            'Language cookie should be set when the language code is present in the URL.',
        );
        $this->assertSame(
            'en-US',
            $cookie->value,
            'Language cookie value should be \'en-US\' when the language code is present in the URL.',
        );

        $request = Yii::$app->request;

        $this->assertSame(
            'site/page',
            $request->pathInfo,
            'Request pathInfo should be \'site/page\' after the language code is removed from the URL.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testSetsLanguageFromUrlIfItMatchesWildcard(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'de-*'],
            ],
        );

        $this->mockRequest('/de/site/page');

        $this->assertSame(
            'de',
            Yii::$app->language,
            'Application language should be set to \'de\' when the language code matches a wildcard in the ' .
            'configuration.',
        );
        $this->assertSame(
            'de',
            Yii::$app->session->get('_language'),
            'Session \'_language\' should be set to \'de\' when the language code matches a wildcard in the ' .
            'configuration.',
        );

        $cookie = Yii::$app->response->cookies->get('_language');

        $this->assertNotNull(
            $cookie,
            'Language cookie should be set when the language code matches a wildcard in the configuration.',
        );
        $this->assertSame(
            'de',
            $cookie->value,
            'Language cookie value should be \'de\' when the language code matches a wildcard in the configuration.',
        );

        $request = Yii::$app->request;

        $this->assertSame(
            'site/page',
            $request->pathInfo,
            'Request pathInfo should be \'site/page\' after the language code is removed from the URL when matching ' .
            'a wildcard.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testSetsLanguageFromUrlIfUppercaseEnabled(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de'],
                'keepUppercaseLanguageCode' => true,
            ],
        );

        $this->mockRequest('/en-US/site/page');

        $this->assertSame(
            'en-US',
            Yii::$app->language,
            'Application language should be set to \'en-US\' when the language code is present in the URL and ' .
            'uppercase is enabled.',
        );
        $this->assertSame(
            'en-US',
            Yii::$app->session->get('_language'),
            'Session \'_language\' should be set to \'en-US\' when the language code is present in the URL and ' .
            'uppercase is enabled.',
        );

        $cookie = Yii::$app->response->cookies->get('_language');

        $this->assertNotNull(
            $cookie,
            'Language cookie should be set when the language code is present in the URL and uppercase is enabled.',
        );
        $this->assertSame(
            'en-US',
            $cookie->value,
            'Language cookie value should be \'en-US\' when the language code is present in the URL and uppercase is ' .
            'enabled.',
        );

        $request = Yii::$app->request;

        $this->assertSame(
            'site/page',
            $request->pathInfo,
            'Request pathInfo should be \'site/page\' after the language code is removed from the URL and uppercase ' .
            'is enabled.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testSetsLanguageFromUrlOrder(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'en-US', 'de'],
            ],
        );

        $this->mockRequest('/en-us/site/page');

        $this->assertSame(
            'en-US',
            Yii::$app->language,
            'Application language should be set to \'en-US\' when the language code is present in the URL and ' .
            'matches the order in the configuration.',
        );
        $this->assertSame(
            'en-US',
            Yii::$app->session->get('_language'),
            'Session \'_language\' should be set to \'en-US\' when the language code is present in the URL and ' .
            'matches the order in the configuration.',
        );

        $cookie = Yii::$app->response->cookies->get('_language');

        $this->assertNotNull(
            $cookie,
            'Language cookie should be set when the language code is present in the URL and matches the order in the ' .
            'configuration.',
        );
        $this->assertSame(
            'en-US',
            $cookie->value,
            'Language cookie value should be \'en-US\' when the language code is present in the URL and matches the ' .
            'order in the configuration.',
        );

        $request = Yii::$app->request;

        $this->assertSame(
            'site/page',
            $request->pathInfo,
            'Request pathInfo should be \'site/page\' after the language code is removed from the URL and matches ' .
            'the order in the configuration.',
        );
    }

    public function testThrowInvalidConfigExceptionWhenEnablePrettyUrlIsFalse(): void
    {
        $this->mockWebApplication();

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Locale URL support requires enablePrettyUrl to be set to true.');

        new UrlLanguageManager(
            [
                'languages' => ['en'],
                'enablePrettyUrl' => false,
            ],
        );
    }

    public function testThrowNotFoundHttpExceptionWhenUrlIsInvalid(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de'],
                'enablePrettyUrl' => true,
                'enableStrictParsing' => true,
            ],
        );

        $this->mockWebApplication();

        $_COOKIE['_language'] = 'de';

        $urlManager = Yii::$app->urlManager;
        $request = Yii::$app->request;
        $request->setUrl('/de/invalid-url');

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Page not found.');

        $urlManager->parseRequest($request);
    }
}
