<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests\base;

use Yii;
use yii\base\{Exception, InvalidConfigException};
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii2\extensions\localeurls\exception\Message;
use yii2\extensions\localeurls\tests\support\TestCase;
use yii2\extensions\localeurls\UrlLanguageManager;

use function time;

/**
 * Unit tests for {@see UrlLanguageManager} language parsing, detection and persistence, replayed under the URL layout
 * of each concrete subclass.
 */
abstract class AbstractUrlLanguageManager extends TestCase
{
    /**
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     */
    public function testAllowsDisabledPrettyUrlWhenLocaleUrlsOrLanguagesAreDisabled(): void
    {
        $this->mockWebApplication();

        $withoutLocaleUrls = new UrlLanguageManager(
            [
                'enableLocaleUrls' => false,
                'enablePrettyUrl' => false,
                'languages' => ['en'],
            ],
        );
        $withoutLanguages = new UrlLanguageManager(
            [
                'enablePrettyUrl' => false,
                'languages' => [],
            ],
        );

        self::assertFalse(
            $withoutLocaleUrls->enablePrettyUrl,
            'Disabled locale URLs must allow plain URLs.',
        );
        self::assertFalse(
            $withoutLanguages->enablePrettyUrl,
            'Empty languages must allow plain URLs.',
        );
    }

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
                'languageCookieDuration' => 0,
            ],
        );
        $this->mockRequest('/en-us/site/page');

        self::assertSame(
            'en-US',
            Yii::$app->language,
            'Language must be set from the URL.',
        );
        self::assertSame(
            'en-US',
            Yii::$app->session->get('_language'),
            'Session must store the language.',
        );
        self::assertNull(
            Yii::$app->response->cookies->get('_language'),
            'Duration `0` must disable the cookie.',
        );
        self::assertSame(
            'site/page',
            Yii::$app->request->pathInfo,
            'Language code must be removed from the path.',
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

        self::assertSame(
            'en',
            Yii::$app->language,
            'Language must stay the default.',
        );
        self::assertSame(
            'site/page',
            Yii::$app->request->pathInfo,
            'Path info must stay unchanged.',
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

        self::assertSame(
            'en-US',
            Yii::$app->language,
            'Language must be set from the URL.',
        );
        self::assertNull(
            Yii::$app->session->get('_language'),
            'Session must not store the language.',
        );
        self::assertNull(
            Yii::$app->response->cookies->get('_language'),
            'Cookie must not be set.',
        );

        self::assertSame(
            'site/page',
            Yii::$app->request->pathInfo,
            'Language code must be removed from the path.',
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

        self::assertSame(
            'en-US',
            Yii::$app->language,
            'Language must be set from the URL.',
        );

        $cookie = Yii::$app->response->cookies->get('_language');

        self::assertNotNull(
            $cookie,
            'Cookie must be set.',
        );
        self::assertSame(
            'en-US',
            $cookie->value,
            'Cookie must store the language.',
        );
        self::assertSame(
            'site/page',
            Yii::$app->request->pathInfo,
            'Language code must be removed from the path.',
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

        $this->assertLanguageApplied('de');
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

        $this->assertLanguageApplied('sr-Latn');
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

        $this->assertLanguageApplied('es-BO');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testDoesNothingForRootUrlIfNoLanguagesConfigured(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => [],
            ],
        );
        $this->mockRequest('/');

        self::assertSame(
            'en',
            Yii::$app->language,
            'Language must stay the default.',
        );
        self::assertNull(
            Yii::$app->response->cookies->get('_language'),
            'No language must be persisted.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testDoesNothingIfInvalidLanguageInCookie(): void
    {
        $this->withCookies(['_language' => 'fr']);

        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de'],
            ],
        );

        $this->mockRequest('/site/page');

        self::assertContains(
            'Found persisted language \'fr\' in cookie.',
            $this->loggedMessages(),
            'Cookie lookup must be logged.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testDoesNothingIfInvalidLanguageInSession(): void
    {
        $this->withSession(['_language' => 'fr']);

        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de'],
            ],
        );

        $this->mockRequest('/site/page');

        self::assertContains(
            'Found persisted language \'fr\' in session.',
            $this->loggedMessages(),
            'Session lookup must be logged.',
        );
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

        self::assertSame(
            'en',
            Yii::$app->language,
            'Language must stay the default.',
        );
        self::assertSame(
            'site/page',
            Yii::$app->request->pathInfo,
            'Path info must stay unchanged.',
        );
        // If a URL rule is configured for the home URL, it will always have a trailing slash
        self::assertSame(
            $this->prepareUrl('/'),
            Url::to(['/site/index']),
            'Home URL must keep its trailing slash.',
        );
        self::assertSame(
            $this->prepareUrl('/?x=y'),
            Url::to(['/site/index', 'x' => 'y']),
            'Home URL with a query must keep its trailing slash.',
        );
        // Other URLs have no trailing slash
        self::assertSame(
            $this->prepareUrl('/site/test'),
            Url::to(['/site/test']),
            'Other URLs must not get a trailing slash.',
        );
        self::assertSame(
            $this->prepareUrl('/site/test?x=y'),
            Url::to(['/site/test', 'x' => 'y']),
            'Other URLs with a query must not get a trailing slash.',
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

        self::assertSame(
            'en',
            Yii::$app->language,
            'Language must stay the default.',
        );
        self::assertSame(
            'site/page',
            Yii::$app->request->pathInfo,
            'Path info must stay unchanged.',
        );
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

        self::assertSame(
            'en',
            Yii::$app->language,
            'Language must stay the default.',
        );
        self::assertSame(
            'site/page',
            Yii::$app->request->pathInfo,
            'Path info must stay unchanged.',
        );
        self::assertContains(
            'Ignore pattern \'#^site/page#\' matches \'site/page.\' Skipping language processing.',
            $this->loggedMessages(),
            'Ignore pattern match must be logged.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testDoesNotTreatLongerPathSegmentAsLanguageCode(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-*', 'es', 'de'],
            ],
        );
        $this->mockRequest('/en-us-variant/site/page');

        self::assertSame(
            'en',
            Yii::$app->language,
            'Language must stay the default.',
        );
        self::assertSame(
            'en-us-variant/site/page',
            Yii::$app->request->pathInfo,
            'Segment longer than a language code must stay in the path.',
        );

        $this->mockRequest('/es-mx/site/page');

        self::assertSame(
            'es-mx/site/page',
            Yii::$app->request->pathInfo,
            'Code must end at a segment boundary.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testHandlesLanguageCodesWithMultipleDashes(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-*', 'es', 'de'],
                'enableLanguageDetection' => true,
                'keepUppercaseLanguageCode' => false,
            ],
        );

        $this->withServer(['HTTP_ACCEPT_LANGUAGE' => 'en-US-variant;q=0.9,en;q=0.8,es;q=0.7']);

        $this->assertRedirect('/en-us/site/page', '/site/page');
        $this->mockRequest('/en-us/site/page');

        self::assertSame(
            'en-US',
            Yii::$app->language,
            'Redirect target must round-trip to the region language.',
        );
        self::assertSame(
            'site/page',
            Yii::$app->request->pathInfo,
            'Language code must be removed from the path.',
        );

        $this->resetEnvironment();
        $this->mockUrlLanguageManager(
            [
                'languages' => ['es-*', 'en', 'de'],
                'enableLanguageDetection' => true,
                'keepUppercaseLanguageCode' => false,
            ],
        );
        $this->withServer(['HTTP_ACCEPT_LANGUAGE' => 'es-MX-variant-extended;q=0.9,es;q=0.8,en;q=0.7']);
        $this->assertRedirect('/es-mx/site/page', '/site/page');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testLanguageCookieIsHttpOnlyByDefault(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
            ],
        );
        $this->mockRequest('/de/site/page');

        $cookie = Yii::$app->response->cookies->get('_language');

        self::assertNotNull(
            $cookie,
            'Cookie must be set.',
        );
        self::assertTrue(
            $cookie->httpOnly,
            'Cookie must be `httpOnly` by default.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testLanguageCookieOptionsDoNotOverrideNameValueOrExpire(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'languageCookieDuration' => 1,
                'languageCookieOptions' => [
                    'expire' => 1,
                    'httpOnly' => false,
                    'name' => 'other',
                    'path' => '/x',
                    'secure' => true,
                    'value' => 'xx',
                ],
            ],
        );

        $before = time();

        $this->mockRequest('/de/site/page');

        $cookie = Yii::$app->response->cookies->get('_language');

        self::assertNotNull(
            $cookie,
            'Option `name` must not rename the cookie.',
        );
        self::assertNull(
            Yii::$app->response->cookies->get('other'),
            'Option `name` must be ignored.',
        );
        self::assertSame(
            'de',
            $cookie->value,
            'Value must be the language.',
        );
        self::assertGreaterThanOrEqual(
            $before + 1,
            $cookie->expire,
            'Expire must derive from the duration.',
        );
        self::assertFalse(
            $cookie->httpOnly,
            'Option `httpOnly` must override the default.',
        );
        self::assertTrue(
            $cookie->secure,
            'Option `secure` must be applied.',
        );
        self::assertSame(
            '/x',
            $cookie->path,
            'Option `path` must be applied.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testLogsLanguageFoundInUrl(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
            ],
        );
        $this->mockRequest('/de/site/page');

        self::assertContains(
            "Language code found in URL. Setting application language to 'de'.",
            $this->loggedMessages(),
            'Language from the URL must be logged.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testMatchesAliasContainingRegexMetacharacter(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'en.us' => 'en-US'],
                'enableLanguagePersistence' => false,
            ],
        );
        $this->mockRequest('/en.us/site/page');

        self::assertSame(
            'en-US',
            Yii::$app->language,
            'Alias must map to its language.',
        );
        self::assertSame(
            'site/page',
            Yii::$app->request->pathInfo,
            'Alias must be removed from the path.',
        );

        $this->mockRequest('/enxus/site/page');

        self::assertSame(
            'en',
            Yii::$app->language,
            'Metacharacter must match itself only.',
        );
        self::assertSame(
            'enxus/site/page',
            Yii::$app->request->pathInfo,
            'Path must stay unchanged.',
        );
    }

    /**
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testParseRequestReturnsFalseWhenNoRuleMatchesWithoutPendingRedirect(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
                'enableStrictParsing' => true,
            ],
        );
        $this->mockRequestApplication('/de/invalid-url');

        self::assertFalse(
            Yii::$app->urlManager->parseRequest(Yii::$app->request),
            'Unmatched request must yield `false`.',
        );
        self::assertSame(
            'de',
            Yii::$app->language,
            'Language must be set from the URL.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsAliasSpelledDifferentlyToConfiguredSpelling(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de', 'at' => 'de-AT'],
            ],
        );
        $this->assertRedirect('/at/site/page', '/AT/site/page');

        self::assertSame(
            'de-AT',
            Yii::$app->language,
            'Language of the URL must be active before the redirect.',
        );
        self::assertNull(
            Yii::$app->response->cookies->get('_language'),
            'Language must not be persisted yet.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsNonCanonicalDefaultCodeToUrlWithoutCodeAndPersistsLanguage(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de'],
            ],
        );
        $this->assertRedirect('/site/page', '/EN/site/page');

        self::assertSame(
            'en',
            Yii::$app->language,
            'Default language must be active.',
        );
        self::assertSame(
            'en',
            Yii::$app->session->get('_language'),
            'Session must store the default language.',
        );
        self::assertSame(
            'en',
            Yii::$app->response->cookies->getValue('_language'),
            'Cookie must store the default language.',
        );
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testRedirectsToLanguageOnlyWhenWildcardSubtagIsNotRegion(): void
    {
        $this->withServer(['HTTP_ACCEPT_LANGUAGE' => 'sr-Latn']);

        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'sr-*'],
            ],
        );
        $this->assertRedirect('/sr/site/page', '/site/page');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testSetsAliasLanguageFromDifferentlySpelledAliasWhenUppercaseIsKept(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'de', 'at' => 'de-AT'],
                'keepUppercaseLanguageCode' => true,
            ],
        );
        $this->mockRequest('/AT/site/page');

        self::assertSame(
            'de-AT',
            Yii::$app->language,
            'Alias must map to its language.',
        );
        self::assertSame(
            'site/page',
            Yii::$app->request->pathInfo,
            'Alias must be removed from the path.',
        );
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

        self::assertSame(
            'en',
            Yii::$app->language,
            'Language must stay the default.',
        );
        self::assertSame(
            '',
            Yii::$app->request->pathInfo,
            'Path info must be empty.',
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

        $this->assertLanguageApplied('en-US');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testSetsLanguageFromUrlIfItMatchesThreeLetterWildcard(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'ast-*'],
                'enableLanguagePersistence' => false,
            ],
        );
        $this->mockRequest('/ast/site/page');

        self::assertSame(
            'ast',
            Yii::$app->language,
            'Wildcard language must be set.',
        );
        self::assertSame(
            'site/page',
            Yii::$app->request->pathInfo,
            'Language code must be removed from the path.',
        );

        $this->mockRequest('/ast-es/site/page');

        self::assertSame(
            'ast-ES',
            Yii::$app->language,
            'Wildcard region must be uppercased.',
        );
        self::assertSame(
            'site/page',
            Yii::$app->request->pathInfo,
            'Language code must be removed from the path.',
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

        $this->assertLanguageApplied('de');
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

        $this->assertLanguageApplied('en-US');
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

        $this->assertLanguageApplied('en-US');
    }

    /**
     * @throws Exception if an unexpected error occurs during execution.
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     * @throws NotFoundHttpException if the requested resource can't be found.
     */
    public function testSetsLanguageWithScriptAndRegionFromUrl(): void
    {
        $this->mockUrlLanguageManager(
            [
                'languages' => ['en', 'zh-Hant-TW'],
            ],
        );
        $this->mockRequest('/zh-hant-tw/site/page');

        self::assertSame(
            'zh-Hant-TW',
            Yii::$app->language,
            'Configured spelling must be kept.',
        );
        self::assertSame(
            'site/page',
            Yii::$app->request->pathInfo,
            'Language code must be removed from the path.',
        );
    }

    /**
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     */
    public function testThrowInvalidConfigExceptionWhenEnablePrettyUrlIsFalse(): void
    {
        $this->mockWebApplication();

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(Message::LOCALE_URLS_REQUIRE_PRETTY_URL->getMessage());

        new UrlLanguageManager(
            [
                'languages' => ['en'],
                'enablePrettyUrl' => false,
            ],
        );
    }

    /**
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     */
    public function testThrowNotFoundHttpExceptionWhenUrlIsInvalid(): void
    {
        $this->withCookies(['_language' => 'de']);

        $this->mockUrlLanguageManager(
            [
                'languages' => ['en-US', 'en', 'de'],
                'enablePrettyUrl' => true,
                'enableStrictParsing' => true,
            ],
        );
        // The persisted language makes a redirect pending; an unmatched URL then has no route to redirect to.
        $this->mockRequestApplication('/invalid-url');

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Page not found.');

        Yii::$app->urlManager->parseRequest(Yii::$app->request);
    }

    /**
     * Asserts that the language from the URL is applied, persisted in session and cookie, and removed from the path.
     */
    private function assertLanguageApplied(string $language): void
    {
        self::assertSame(
            $language,
            Yii::$app->language,
            'Language must be set from the URL.',
        );
        self::assertSame(
            $language,
            Yii::$app->session->get('_language'),
            'Session must store the language.',
        );

        $cookie = Yii::$app->response->cookies->get('_language');

        self::assertNotNull(
            $cookie,
            'Cookie must be set.',
        );
        self::assertSame(
            $language,
            $cookie->value,
            'Cookie must store the language.',
        );
        self::assertSame(
            'site/page',
            Yii::$app->request->pathInfo,
            'Language code must be removed from the path.',
        );
    }
}
