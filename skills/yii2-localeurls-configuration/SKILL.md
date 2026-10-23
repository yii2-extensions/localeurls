---
name: yii2-localeurls-configuration
description: Configure or troubleshoot yii2-extensions/localeurls in Yii applications, including language URL aliases, default language, detection, persistence, canonical redirects, and isolation between worker requests. Use for this UrlLanguageManager; exclude translation writing, Laravel routing, and unrelated locale libraries.
license: BSD-3-Clause
metadata:
  source: https://github.com/yii2-extensions/localeurls
---

# Configure language URLs in Yii

Inspect the installed package revision, Composer constraints, merged `urlManager`/`request` configuration, URL rules,
and worker entry script when present. This package's 0.2 line requires PHP >=8.3 and Yii `~2.0.55 || ^22.0@dev`.
Preserve existing routes, validation, trusted proxy addresses, session ownership, and the application's chosen
languages. Installing this skill does not install the extension or authorize deployment.

## Read the relevant contract

In a root package checkout, use `composer show --self --format=json` for metadata. For installed dependencies, resolve
the real path and read the configured vendor directory's `composer/installed.php` (`versions[<package>]`):
`install_path`, `pretty_version` and Composer's selected `reference`. Do not blindly prefer `source.reference` over
`dist.reference`. For any live package checkout, including a symlinked path install, inspect its current `HEAD` and
files even when clean; report metadata mismatches and let the actual files govern. Uncommitted changes have no matching
SHA. Otherwise use the selected reference or a verified matching tag only after establishing its source provenance.
Compare `composer.lock`; it may differ from the inspected installation. Identify a lock-only state when no package files
are installed, and resolve dependencies separately. Use hosted pages, catalogs or snapshots only when their source
revision matches.

Prefer local package `docs/` and `src/UrlLanguageManager.php`. For a copied skill or an archive without docs, read these
files at the resolved reference in <https://github.com/yii2-extensions/localeurls>:

- `installation.md`: dependency requirements and development-line installation.
- `configuration.md`: aliases, default language, detection/persistence, ignore patterns, redirects, and worker mode.
- `examples.md`: application recipes.
- `testing.md`: package checks.
- Root `UPGRADE.md`: compatibility changes when upgrading an existing installation.

If matching documentation is unavailable or offline, state that gap. Inspect installed source rather than inventing an
API route or copying another extension's methods. The component class is
`yii2\extensions\localeurls\UrlLanguageManager`; it replaces `components.urlManager` and has no bootstrap class.

## Configure language URLs

Use pretty URLs while locale URLs are enabled. Entries in `languages` may be language codes, region wildcards such as
`es-*`, or aliases with the URL segment as key and the application language as value: `'deutsch' => 'de'`. Aliases
change URL spelling; they are not language values for `Yii::$app->language`. Preserve the configured language spelling
and check `keepUppercaseLanguageCode` before changing canonical URLs.

Set `defaultLanguage` explicitly when a worker's application language may outlive a request. With `null`, initialization
captures the current application language; that is not a reset policy. An explicit value resets the language at the
start of `parseRequest()` for each new request object, including ignored URLs. The URL, persisted choice, or detection
can then select another language. Do not change the default later by assigning only the application language.

The default language normally has no URL code; `enableDefaultLanguageUrlCode` changes that. Generate links through Yii's
URL manager/helpers and its configurable `languageParam`, using the actual language code, not string-prefixing URLs or
passing an alias as the application language. With persistence or detection enabled, an explicit default language
parameter can intentionally generate a reset URL with its code.

Detection follows URL, session, cookie, browser header, then GeoIP. `enableLanguageDetection = false` disables browser
header detection only; GeoIP mappings can still apply. Match ignore-pattern keys to routes for creation and their values
to the URLs those rules generate for parsing. Ignored paths resolve in the explicit default language.

## Keep request boundaries and redirects intact

Supply a fresh `yii\web\Request` for each request. Mutating and reusing a processed object skips locale processing on
later parses. Verify both route parsing and URL creation after at least two requests with different languages. The
manager raises `yii\web\UrlNormalizerRedirectException`; let the application/runner handle it instead of calling
`exit()` or treating redirects as parse failures. Check target, status, remaining path, and preserved query parameters.
The query parameter named by `languageParam` does not choose a redirect language and is removed from redirect URLs.

Persistence starts a session whenever `languageSessionKey` is a string and an uncoded URL reads it. Set that key to
`false` for cookie-only persistence; disable persistence altogether only when the application is intentionally
stateless. Retain cookie validation with a stable private key and do not disable CSRF or validation to fix redirects.

For `psr-bridge`, follow the installed bridge's session lifecycle and the guide's worker section. Keep `useSession` when
sessions are used, attach language handlers in component configuration, and do not add `urlManager` to
`persistentComponents`: the bridge rebuilds this core component per request. This skill does not replace the worker
runner's isolation of response, session, identity, and other application state.

## Verify behavior

Exercise an alias URL, a URL without a code after a different language, the default-language URL policy, a canonical
case redirect, and relevant ignored paths/detection/persistence. Assert actual route, language, URL, redirect and
request isolation rather than matching configuration text. Use the application's existing tests and the package checks
in `docs/testing.md`. A local parse test proves neither browser cookie exchange nor a live worker. Record exact
client/model, skill discovery/loading, changed files, executed checks and gaps separately.
