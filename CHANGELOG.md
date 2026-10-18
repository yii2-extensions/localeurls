# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 0.2.1 Under development

## 0.2.0 October 18, 2026

- fix: order class elements in methods and properties (#26).
- fix: add `case` to the ordered class elements in ECS configuration (#27).
- fix: correct badge URL formatting in `README.md` (#28).
- fix: add missing `Composer` requirement in the installation guide (#29).
- fix: update license badge URL in `README.md` and add missing header in `LICENSE.md` (#30).
- fix: update branch alias version in `composer.json` from `0.1.x-dev` to `0.2.x-dev` (#31).
- fix: update `.gitattributes` to exclude additional files from the package, update `LICENSE.md`, and add stable version workflow actions (#32).
- fix: remove unused command coverage options from mutation workflow configuration (#33).
- chore(deps): bump `php-forge/actions` from 1 to 2 (#34).
- feat!: raise language redirects as `UrlNormalizerRedirectException` for worker mode, fix URL parsing and redirects, and require PHP `8.3`.
- fix: require Yii2 `2.0.55` or later to exclude `CVE-2026-39850` while retaining Yii `22.0` support; see `UPGRADE.md`.
- chore: add a configuration skill, exclude private files, streamline docs, fix URL language handling, and clarify GeoIP guidance.

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
