# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 0.2.0 Under development

- build!: require PHP 8.3 or later, move the tooling to the PHP 8.3 presets (`ecs-83`, `rector-83`, StyleCI `8.3`), and update Infection to `^0.35` and PHPUnit to `^12.5`; see `UPGRADE.md`.
- fix!: raise language redirects as `UrlNormalizerRedirectException` from `UrlLanguageManager::parseRequest()` instead of calling `Yii::$app->end()`, so redirects no longer terminate long-running workers (`psr-bridge`, FrankenPHP, RoadRunner); see `UPGRADE.md`.
- fix!: pass the request explicitly to the protected helpers of `UrlLanguageManager`, track processed requests in a `WeakMap`, and remove `$_request`, `$_processed`, `ensureRequest()` and `redirectToLanguage()`; see `UPGRADE.md`.
- fix!: remove the `YII_ENV === 'test'` seam from `UrlLanguageManager`, so test environments receive `UrlNormalizerRedirectException` like production instead of a `yii\base\Exception` carrying the redirect URL; see `UPGRADE.md`.
- fix!: keep the status code of the URL normalizer (`301` by default) for redirects required only by the normalizer instead of using `languageRedirectCode`; see `UPGRADE.md`.
- fix!: drop the request query parameter named by `languageParam` from redirect URLs, so it no longer selects the redirect language and `?language=//host` no longer produces an open redirect; see `UPGRADE.md`.
- fix: read the GeoIP country from the request headers for `HTTP_*` names and from the request server parameters otherwise, instead of `$_SERVER`; see `UPGRADE.md`.
- fix: parse each request once with the parent `UrlManager`, and merge an array redirect thrown by a custom URL rule with a pending language redirect into one redirect that keeps the scheme of the rule redirect.
- fix: rethrow a `UrlNormalizerRedirectException` with a string URL thrown by a custom URL rule unchanged instead of rebuilding it as a route (`https://example.com/x` became `/https://example.com/x`); see `UPGRADE.md`.
- fix: require a `/` or the end of the path after the URL language code, match aliases case-insensitively, accept only a 2-3 letter region in `matchCode()` wildcard matches (`-419` and `-Latn` are not regions), and quote language codes in the URL pattern.
- fix: apply languages configured with a lowercase region (`de-at`) and alias spellings on their URLs without a redirect, match the language part of wildcards case-insensitively, redirect a code or alias spelled in another case (`/DE/page`, `/Deutsch/page`) to the lowercase form of the spelling used in the URL (`/de/page`, `/deutsch/page`) while `keepUppercaseLanguageCode` is `false`, and never match a literal `*` as a wildcard.
- fix: apply the language of a URL code or alias spelled in another case (`/DE/page`) before the URL rules parse the request, so language-dependent rules resolve and rebuild the route for that language instead of the default one.
- fix: redirect a persisted or detected language to its configured form, so a stale `es-MX` cookie with `['en', 'es']` redirects to `/es/...` instead of looping on a growing URL; see `UPGRADE.md`.
- fix: preserve numeric query keys in redirect URLs, as in upstream `codemix/yii2-localeurls` 1.8.1, and append a query parameter with key `0`, which `createUrl()` reserves for the route, before the URL fragment instead of dropping it.
- fix: detect absolute URLs in `createUrl()` by a leading scheme only and end the host at the first `/` or `?` after the matched scheme, so a relative URL whose path contains `://` gets its language code and a scheme longer than `https` (`custom://`) keeps its host.
- fix: add the language code in `createUrl()` when the route is missing and `ignoreLanguageUrlPatterns` is configured, instead of returning the URL of the parent `UrlManager`; see `UPGRADE.md`.
- feat: add `UrlLanguageManager::$defaultLanguage`, applied to `Yii::$app->language` at the start of each request, so long-running workers never carry the language of one request into the next.
- test: assert redirects on `UrlNormalizerRedirectException` with status codes, add the plain URL layout, worker-mode and parser tests, and make the suite order-independent.
- docs: add `UPGRADE.md` and a worker-mode section to `docs/configuration.md`; fix the URL normalizer example and the cookie disable value.
- docs: document the worker-mode contract (`defaultLanguage`, `useSession`, `languageSessionKey`, `session.use_cookies`, `cookieValidationKey`, `trustedHosts`, configuration-level `languageChanged` handlers), fix the `ignoreLanguageUrlPatterns` example, and state the GeoIP lookup order.
- docs: keep prose guides in `docs/`, correct the URL examples and the language switcher, link the docs site, `llms.txt` and agent skill.
- refactor: simplify request processing in `UrlLanguageManager`: normalize language-only URLs on the parsed route, resolve URL codes and aliases in one place, and build the redirect with a single exception site.
- chore: rewrite the PHPDoc of `UrlLanguageManager` and `LanguageChangedEvent`, empty the `composer-require-checker.json` whitelist, analyse the whole `tests/` directory with PHPStan, and drop the unused `ext-mbstring` requirement.
- chore: narrow the Yii PHPDoc of `UrlManager::$suffix` (`string|null`) and `UrlNormalizer::normalizeRoute()` (`[$route, $params]`) with the PHPStan stub `tests/support/yii-web.stub`, so static analysis passes on Yii `2.0.x` and `22.0` alike.
- ci: migrate workflows to pinned `yii2-framework/actions` reusable workflows and add `quality` and `security` workflows.
- ci: run the compatibility job against Yii `~2.0.54` on PHP 8.3 to 8.5 and the mutation job on the default PHP version.
- chore: adopt `php-forge/coding-standard` (`ecs-83`, `rector-83`), `php-forge/baseline` metadata files and the organization `.github/linters` configuration.
- chore: exclude `codecov.yml` from the distribution archive.
- build: require `yiisoft/yii2` `~2.0.54 || ^22.0@dev`, add `authors` to `composer.json`, update `yii2-extensions/phpstan` to `^0.5@dev` and add `phpstan/phpstan-phpunit`.
- chore: remove `.github` community files provided by the organization `.github` repository.
- docs: restructure `README.md`, `docs/installation.md`, `docs/testing.md` and `docs/examples.md` to the organization layout; rename `LICENSE.md` to `LICENSE`, which now carries the standard BSD-3-Clause text instead of the customized clause 3 of the previous `LICENSE.md`.
- docs: add the features overview SVGs (`docs/svgs/features.svg`, `features-mobile.svg`) to the README.
- docs: state that generated URLs of the default language carry no code unless `enableDefaultLanguageUrlCode` is `true` (README quick start, `docs/examples.md`).
- fix: order class elements in methods and properties (#26).
- fix: add `case` to the ordered class elements in ECS configuration (#27).
- fix: correct badge URL formatting in `README.md` (#28).
- fix: add missing `Composer` requirement in the installation guide (#29).
- fix: update license badge URL in `README.md` and add missing header in `LICENSE.md` (#30).
- fix: update branch alias version in `composer.json` from `0.1.x-dev` to `0.2.x-dev` (#31).
- fix: update `.gitattributes` to exclude additional files from the package, update `LICENSE.md`, and add stable version workflow actions (#32).
- fix: remove unused command coverage options from mutation workflow configuration (#33).
- chore(deps): bump `php-forge/actions` from 1 to 2 (#34).

## 0.1.2 July 4, 2025

- fix: update mutation workflow configuration and remove obsolete `infection.json.dist` file (#24).
- fix: update `README.md` and installation documentation for clarity and consistency (#25).

## 0.1.1 June 27, 2025

- fix: improve language in comments for clarity and consistency in `LanguageChangedEvent.php` and `UrlLanguageManager.php` (#19).
- fix: clean up code in tests for better readability and maintainability (#20).
- fix: simplify use statements and clean up whitespace in documentation files (#21).
- fix: update `PHPStan` `tmpDir` config; move `runtime` directory to `root` (#22).

## 0.1.0 June 20, 2025

- feat: initial release.
