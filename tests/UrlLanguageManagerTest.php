<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests;

use PHPUnit\Framework\Attributes\Group;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

/**
 * Test suite for language detection, persistence, and URL parsing.
 *
 * Validates the behavior of the language manager in scenarios involving language code extraction from URLs, cookies,
 * sessions, and HTTP headers, as well as configuration options for disabling detection, persistence, or storage
 * mechanisms.
 *
 * These tests ensure that language selection, alias resolution, wildcard and script code handling, and language code
 * normalization are performed correctly and that disabling or customizing persistence (via session or cookie) works as
 * expected.
 *
 * The test class covers edge cases such as invalid language codes in cookies or sessions disabled locale URLs, ignored
 * URL patterns, and the absence of configured languages, ensuring robust and predictable language management in all
 * supported configurations.
 *
 * Test coverage.
 * - Consistency of language state across request, session, and cookie.
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
#[Group('locale-urls')]
class UrlLanguageManagerTest extends TestCase
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
        $this->assertEquals('en-US', Yii::$app->language);
        $this->assertEquals('en-US', Yii::$app->session->get('_language'));
        $this->assertNull(Yii::$app->response->cookies->get('_language'));

        $request = Yii::$app->request;

        $this->assertEquals('site/page', $request->pathInfo);
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
        $this->assertEquals('en', Yii::$app->language);

        $request = Yii::$app->request;

        $this->assertEquals('site/page', $request->pathInfo);
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
        $this->assertEquals('en-US', Yii::$app->language);
        $this->assertNull(Yii::$app->session->get('_language'));
        $this->assertNull(Yii::$app->response->cookies->get('_language'));

        $request = Yii::$app->request;

        $this->assertEquals('site/page', $request->pathInfo);
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
        $this->assertEquals('en-US', Yii::$app->language);
        $this->assertNull(Yii::$app->session->get('_language'));
        $this->assertEquals('en-US', Yii::$app->response->cookies->get('_language'));

        $request = Yii::$app->request;

        $this->assertEquals('site/page', $request->pathInfo);
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
        $this->assertEquals('de', Yii::$app->language);
        $this->assertEquals('de', Yii::$app->session->get('_language'));

        $cookie = Yii::$app->response->cookies->get('_language');

        $this->assertNotNull($cookie);
        $this->assertEquals('de', $cookie->value);

        $request = Yii::$app->request;

        $this->assertEquals('site/page', $request->pathInfo);
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
        $this->assertEquals('sr-Latn', Yii::$app->language);
        $this->assertEquals('sr-Latn', Yii::$app->session->get('_language'));

        $cookie = Yii::$app->response->cookies->get('_language');

        $this->assertNotNull($cookie);
        $this->assertEquals('sr-Latn', $cookie->value);

        $request = Yii::$app->request;

        $this->assertEquals('site/page', $request->pathInfo);
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
        $this->assertEquals('es-BO', Yii::$app->language);
        $this->assertEquals('es-BO', Yii::$app->session->get('_language'));

        $cookie = Yii::$app->response->cookies->get('_language');

        $this->assertNotNull($cookie);
        $this->assertEquals('es-BO', $cookie->value);

        $request = Yii::$app->request;

        $this->assertEquals('site/page', $request->pathInfo);
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
        $this->mockRequest('/site/page');
        $this->expectNotToPerformAssertions();
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
        $this->mockRequest('/site/page');
        $this->expectNotToPerformAssertions();
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
        $this->assertEquals('en', Yii::$app->language);

        $request = Yii::$app->request;

        $this->assertEquals('site/page', $request->pathInfo);

        // If a URL rule is configured for the home URL, it will always have a trailing slash
        $this->assertEquals($this->prepareUrl('/'), Url::to(['/site/index']));
        $this->assertEquals($this->prepareUrl('/?x=y'), Url::to(['/site/index', 'x' => 'y']));
        // Other URLs have no trailing slash
        $this->assertEquals($this->prepareUrl('/site/test'), Url::to(['/site/test']));
        $this->assertEquals($this->prepareUrl('/site/test?x=y'), Url::to(['/site/test', 'x' => 'y']));
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
        $this->assertEquals('en', Yii::$app->language);

        $request = Yii::$app->request;

        $this->assertEquals('site/page', $request->pathInfo);
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testDoesNothingIfUrlMatchesIgnoresUrls(): void
    {
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
        $this->assertEquals('en', Yii::$app->language);

        $request = Yii::$app->request;

        $this->assertEquals('site/page', $request->pathInfo);
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
        $this->assertEquals('en', Yii::$app->language);

        $request = Yii::$app->request;

        $this->assertEquals('', $request->pathInfo);
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
        $this->assertEquals('en-US', Yii::$app->language);
        $this->assertEquals('en-US', Yii::$app->session->get('_language'));

        $cookie = Yii::$app->response->cookies->get('_language');

        $this->assertNotNull($cookie);
        $this->assertEquals('en-US', $cookie->value);

        $request = Yii::$app->request;

        $this->assertEquals('site/page', $request->pathInfo);
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
        $this->assertEquals('de', Yii::$app->language);
        $this->assertEquals('de', Yii::$app->session->get('_language'));

        $cookie = Yii::$app->response->cookies->get('_language');

        $this->assertNotNull($cookie);
        $this->assertEquals('de', $cookie->value);

        $request = Yii::$app->request;

        $this->assertEquals('site/page', $request->pathInfo);
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
        $this->assertEquals('en-US', Yii::$app->language);
        $this->assertEquals('en-US', Yii::$app->session->get('_language'));

        $cookie = Yii::$app->response->cookies->get('_language');

        $this->assertNotNull($cookie);
        $this->assertEquals('en-US', $cookie->value);

        $request = Yii::$app->request;

        $this->assertEquals('site/page', $request->pathInfo);
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
        $this->assertEquals('en-US', Yii::$app->language);
        $this->assertEquals('en-US', Yii::$app->session->get('_language'));

        $cookie = Yii::$app->response->cookies->get('_language');

        $this->assertNotNull($cookie);
        $this->assertEquals('en-US', $cookie->value);

        $request = Yii::$app->request;

        $this->assertEquals('site/page', $request->pathInfo);
    }
}
