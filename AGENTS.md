# AGENTS.md

Rules for AI coding agents (and humans) working on this repository. Read [CONTRIBUTING.md](CONTRIBUTING.md) first, this file adds the project habits that keep a widely deployed product stable.

## Principles

1. **Never change the default behaviour for existing users.** New behaviour is an option, off by default, copying the pattern of an existing one (for example `disable_location`: schema column in the module's `resources/db/schema/*.php` with default `0`, a form field, a save handler using `filter_var`, the consumer reading the flag, and a module `package.json` version bump so the schema is picked up). A real bug fix may change the output only where it was broken.
2. **One fix or feature per pull request**, as small as possible.
3. **Every fix carries a test** that passes with the change and fails on `trunk` without it (see [Tests](#tests)).
4. **Verify before you state.** Check facts on the default branch (`git show origin/trunk:<path>`) before writing them in an issue, a PR comment or a release note, never from memory or a bot diff. If you were wrong, correct it publicly at once.
5. **Fragile code is a defect.** If something works only because an unguaranteed thing happens to be true (a line of a file you do not own is still there, a fixed sleep is long enough, output wording stays the same, a failure is swallowed), say so and use a sturdier way: anchor on a structure that must exist, parse instead of matching text, poll instead of sleeping, fail loudly. If it cannot be avoided, mark it `FRAGILE-OK: why` and add a test that fails when the assumption breaks. Example: add a permission before the closing `</manifest>` tag, never by replacing a neighbouring permission line.
6. **Other editions override some files, so a fix is not done until they are checked.** MAE and PE are private overlays that replace SAE files of the same path (a design file by its design key), they never merge. Read [docs/editions.md](docs/editions.md). Keep changes additive and local, and for a change to a backoffice, Admin, System, Core, Application or Template file, say in the pull request that the overlays need the same change and ask a maintainer with access to check it against their override map. Never name an overlay-only file in a public issue, pull request or release note.
7. **Keep Android and iOS at parity, as far as possible.** The app code is shared, the native side is not: a change to a native plugin, a permission, a minimum OS version or a shell setting on one platform is made on the other in the same pull request, or the pull request says why it cannot be and the difference goes in [docs/mobile-parity.md](docs/mobile-parity.md). Shared JavaScript must work with the plugin versions of both shells (accept the old and the new format of what a plugin returns). Build both shells before merging a change to a shell: Actions > android-build and ios-build.
8. **Do not add Zend Framework 1 (`Zend_*`) to the code.** ZF1 is being removed ([docs/decisions/0001-remove-zf1.md](docs/decisions/0001-remove-zf1.md)). A file may keep the references it has (the files that have some are in `tests/php-upgrade/zf1-allowlist.txt`) and may lose them, never gain one; a new file starts without. Use the classes of the `Siberian\` namespace or plain PHP. A line that has to name `Zend_` (a facade that wraps it) says `ZF1-OK: why`. `tests/php-upgrade/check-pr.sh` fails on a new reference.
9. **A change to a module's schema or data files raises that module's `package.json` version, in the same pull request.** The installer plays `resources/db/schema` and `resources/db/data` of an installation that updates only when the module version in `package.json` is newer than the one recorded in its database (`Installer_Model_Installer_Module::canUpdate()`). Without the bump, fresh installs get the change and every updated installation silently does not: 5.1.14 added `application.android_edge_to_edge` and Application stayed at 5.0.15, so the option saved and its value was dropped (fixed by raising Application to 5.0.16). Bump the version of the module the file is in (a patch bump is enough), and keep the files idempotent. `tests/php-upgrade/check-pr.sh` fails on a changed schema or data file whose module version did not change.

## Repository map

- `siberian/app/sae/modules/<Module>`: PHP modules (controllers, models, `resources/db/schema` for tables, `resources/db/data` for data files, `package.json` for the module version).
- `siberian/app/sae/design/desktop/backoffice`: back-office AngularJS (controllers, factories, templates).
- `siberian/languages`: translation catalogs.
- `tests/php-upgrade`: regression tests and the CI image.

## Developer documentation

The public developer documentation is <https://developers.siberiancms.com/>, its source is the Markdown of [Xtraball/SiberianCMS-Doc](https://github.com/Xtraball/SiberianCMS-Doc) (`docs/`, built with MkDocs 1.0.4). Read it before writing or changing a module, a layout or a template, and use the raw Markdown when you work offline:

| To work on | Read |
|---|---|
| A module (structure, `package.json`, `resources/db/schema` and `data`, translations, `init.php`, design files, packaging) | `docs/module.md`, `docs/module/basic.md` |
| How a file of a lower edition is replaced (`local > pe > mae > sae`, design cache) | `docs/module/core-inheritance.md`, and [docs/editions.md](docs/editions.md) here |
| A homepage layout (`hooks.js`, `view.html`, layout options) | `docs/layout.md` |
| A template pack | `docs/customization/template.md` |
| Hooks, forms, push, offline mode, the CLI, the API | `docs/module/hooks.md`, `docs/module/forms.md`, `docs/module/push.md`, `docs/module/offline-mode.md`, `docs/cli/siberian.md`, `docs/api.md` |

The documentation lags the code, and it describes only SAE-visible behaviour. **When the code and the documentation disagree, the code on `trunk` is right**: check it (`git show origin/trunk:<path>`), then fix the documentation in the same piece of work with a pull request to `SiberianCMS-Doc` and link it from your pull request here. Examples of what was wrong and is now corrected: data files whose name ends in a digit (`feature1.php`) are never run, `Siberian_Assets::registerAssets()` needs its second argument, only `<table>.php` files of `resources/db/schema` are tables. Do not put anything about MAE or PE overlay files in the documentation, it is public.

## Tests

- Runner: `php tests/php-upgrade/run.php [--fail-fast] [filter]` from the repository root; `tests/php-upgrade/ci.sh` and `Dockerfile.ci` show the supported PHP versions. Run the tests of what you changed and the close neighbours, on every supported PHP version.
- A regression file is `tests/php-upgrade/regression/<Name>Test.php` returning `['test name' => callable]`, with the helpers from `bootstrap.php` (`t_equal`, `t_true`).
- Tier 1 (real database): `tests/php-upgrade/db/<Name>Test.php`, same format, run with `php tests/php-upgrade/run.php --suite=db`. `db/bootstrap.php` gives `t_db_fresh()` (an empty database per test, set as the Zend default adapter), `t_db_sync()` and `t_schema_file()`. `tests/php-upgrade/ci.sh <php>` starts a throwaway MariaDB and removes it; with `SIB_TEST_DB_HOST` unset the suite skips. Use tier 1 for code whose behaviour is the SQL it runs (schema sync, installer, model queries); see `docs/testing-plan.md`.
- Access matrix: `tests/php-upgrade/regression/ControllerAccessMatrixTest.php` reads every controller and fails when a family guard (Backoffice, Admin, Application, Api) loses its check, when a controller overrides `init()`/`preDispatch()` without calling the parent, or when a controller or action that answers without a login, a guest route or an open action appears. A new unauthenticated door needs a reviewed reason in the pull request, then `SIB_UPDATE_ACCESS_BASELINE=1 php tests/php-upgrade/run.php ControllerAccessMatrix` updates `tests/php-upgrade/access/baseline.json`.
- When a controller cannot be loaded without the full application, put the rule in a small pure helper class and test that, plus source-level checks that the wiring is in place and that nothing runs without the option.
- Name the test after the issue (for example `Issue524ReinstallTest.php`).
- Design caches: `siberian/app/<sae|mae|pe>/design/design-cache.json` list every design file of an edition (js, css, layout, template, image). A file that is not in the list is never sent to the browser, so a directive or stylesheet added after the last rebuild has no effect and the page renders flat or half styled. When you add, rename or delete a design file, rebuild (`php cli cache:design <sae|mae|pe>` on an assembled tree) and commit the caches; `php tests/php-upgrade/design-cache-check.php` fails when one is stale. When testing or debugging an install (missing css/js, a new file with no effect, after every update), rebuild the cache first and move `var/cache/zend_cache---default_js_desktop` / `..._css_desktop` and `app/local/design/design-cache.json` aside, before debugging the code.

## Module installs

Schema sync and data files are upserts by nature, so running them again on the same version is safe. Going forward every new schema and data file must stay idempotent. An installed module is only updated when its `package.json` version is newer than the database; reinstalling the same version is an explicit opt-in in the back-office. So every change under `resources/db/schema` or `resources/db/data` comes with a version bump of that module (rule 9), or updated installations never play it.

## Translations

English is the default language. A text missing from every catalog is shown in English, but translators never see it, so add every new user-visible text in the same pull request, **always in a `.po` catalog**:

- Ask for it with `p__('<context>', '...')` and add it to `siberian/languages/base/c_<context>.po`: `msgctxt "<context>"`, `msgid` and an English `msgstr` equal to it, a `# GMT <epoch>.<n>` comment above (copy the entries already there).
- Do not add new entries to `siberian/languages/default/*.csv` (nor to the module `.csv` files): they are the legacy format, kept for the texts that already live there. Reusing a text that is already in one of them is fine.
- `Siberian\Translation::extractAll($module, $context)` prints the texts that are missing from the catalogs (it writes nothing).
- Assert the texts in the pull request test, so a missing entry fails; `tests/php-upgrade/check-pr.sh` also fails on a new text that is not in a `.po`.

## Files and style

- LF line endings only. Do not commit CRLF.
- Follow the style of the surrounding code (naming, comment density, idiom). Prefer the project's existing helpers over new ones.
- Do not remove vendored code, intentional duplicates or committed `node_modules` unless a maintainer asked for it.

## Pull requests

- Link the issue (`Fixes #n` only when the PR really resolves it; use `Refs #n` otherwise).
- Describe what changes for users who do nothing (it should be nothing), how the option is turned on, and how it was tested, including what was **not** tested.
- Do not publish releases, change the update channels or touch credentials from an automated session.

## Check your branch before opening the pull request

```sh
tests/php-upgrade/check-pr.sh [--strict] [base-ref]
```

It reads git only and checks the added lines against this file: it fails on CRLF line endings, on new `__()` / `p__()` texts that are not in a `.po` translation catalog, on new `Zend_` references (rule 8; `check-pr.sh --zf1-list` remakes the allowlist), and on a changed module schema or data file without a version bump of its module (rule 9), and warns when source changed without a test, or when fragile smells (`|| true`, `sleep`, `@` suppression) were added. `--strict` turns the warnings into failures.

## Issue triage

New issues are checked and answered with the flow in [docs/triage.md](docs/triage.md): verify on `origin/trunk`, pick one verdict, write a draft for a maintainer to review. Update that file when a triage teaches us something.
