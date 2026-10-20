# AI agent workflow

Use the package-owned `yii2-localeurls-configuration` skill for language URL configuration, canonical redirects,
persistence, and request isolation. Use the guides directly for ordinary reading; translation writing and unrelated
routers do not need this skill.

The canonical source is [`skills/yii2-localeurls-configuration/SKILL.md`](../skills/yii2-localeurls-configuration/SKILL.md).
The `.agents/skills/` and `.claude/skills/` entries point to that source for local discovery.

## Install in an application

### Automatic Composer installation

Complete the [one-time application setup](https://yii2-framework.github.io/docs/project/ai-agents/#composer-installation) to enable `llm/skills`
and set `auto-sync` to `true` in `skills.json`. Approve `yii2-extensions/localeurls` explicitly in
`dependencies.composer.trusted`.
The installed package must include its skill bundle; its `extra.skills.source` declares the directory.

```bash
composer install
composer skills:show
```

With `auto-sync` enabled, later dependency installs and updates synchronize the skill automatically; no per-skill
installer is required. If `auto-sync` is `false`, run `composer skills:update` after dependency installation or updates.

### Optional npx installation

From the application root, list the source before installing:

```bash
npx skills@1.7.2 add yii2-extensions/localeurls --list
npx skills@1.7.2 add yii2-extensions/localeurls --skill yii2-localeurls-configuration
```

Select Codex, Claude Code, or OpenCode in the installer as appropriate. Installation, discovery, native loading, and
successful configuration are different outcomes: verify each one before reporting it. An installed skill does not
install Composer dependencies.

Prefer this checkout's [configuration guide](configuration.md) and installed source at the same revision. When a
hosted guide is unavailable, say so and use the local guide; do not guess an API endpoint. Run the relevant
[package checks](testing.md) after changes. Keep session/CSRF/cookie validation and application routes
intact, and distinguish direct manager tests from an actual worker or browser run.

[Back to documentation](../README.md#documentation)
