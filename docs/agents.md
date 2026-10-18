# AI agent workflow

Use the package-owned `yii2-localeurls-configuration` skill for language URL configuration, canonical redirects,
persistence, and request isolation. Use the guides directly for ordinary reading; translation writing and unrelated
routers do not need this skill.

The canonical source is [`skills/yii2-localeurls-configuration/SKILL.md`](../skills/yii2-localeurls-configuration/SKILL.md).
The `.agents/skills/` and `.claude/skills/` entries point to that source for local discovery. For an installed copy:

```bash
npx skills add yii2-extensions/localeurls --skill yii2-localeurls-configuration
```

Select Codex, Claude Code, or OpenCode in the installer as appropriate. Installation, discovery, native loading, and
successful configuration are different outcomes: verify each one before reporting it. An installed skill does not
install Composer dependencies.

Prefer this checkout's [configuration guide](configuration.md) and installed source at the same revision. When a
hosted guide is unavailable, say so and use the local guide; do not guess an API endpoint. Run the relevant
[package checks](testing.md) after changes. Keep session/CSRF/cookie validation and application routes
intact, and distinguish direct manager tests from an actual worker or browser run.
