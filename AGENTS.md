# AGENTS.md

Rules for AI coding agents (and humans) working on this repository. Read [CONTRIBUTING.md](CONTRIBUTING.md) first, this file adds the project habits that keep a widely deployed product stable.

## Principles

1. **Never change the default behaviour for existing users.** New behaviour is an option, off by default, copying the pattern of an existing one (for example `disable_location`: schema column in the module's `resources/db/schema/*.php` with default `0`, a form field, a save handler using `filter_var`, the consumer reading the flag, and a module `package.json` version bump so the schema is picked up). A real bug fix may change the output only where it was broken.
2. **One fix or feature per pull request**, as small as possible.
3. **Every fix carries a test** that passes with the change and fails on `trunk` without it (see [Tests](#tests)).
4. **Verify before you state.** Check facts on the default branch (`git show origin/trunk:<path>`) before writing them in an issue, a PR comment or a release note, never from memory or a bot diff. If you were wrong, correct it publicly at once.
5. **Fragile code is a defect.** If something works only because an unguaranteed thing happens to be true (a line of a file you do not own is still there, a fixed sleep is long enough, output wording stays the same, a failure is swallowed), say so and use a sturdier way: anchor on a structure that must exist, parse instead of matching text, poll instead of sleeping, fail loudly. If it cannot be avoided, mark it `FRAGILE-OK: why` and add a test that fails when the assumption breaks. Example: add a permission before the closing `</manifest>` tag, never by replacing a neighbouring permission line.
6. **Other editions override some files, so a fix is not done until they are checked.** MAE and PE are private overlays that replace SAE files of the same path (a design file by its design key), they never merge. Read [docs/editions.md](docs/editions.md). Keep changes additive and local, and for a change to a backoffice, Admin, System, Core, Application or Template file, say in the pull request that the overlays need the same change and ask a maintainer with access to check it against their override map. Never name an overlay-only file in a public issue, pull request or release note.

## Repository map

- `siberian/app/sae/modules/<Module>`: PHP modules (controllers, models, `resources/db/schema` for tables, `resources/db/data` for data files, `package.json` for the module version).
- `siberian/app/sae/design/desktop/backoffice`: back-office AngularJS (controllers, factories, templates).
- `siberian/languages`: translation catalogs.
- `tests/php-upgrade`: regression tests and the CI image.

## Tests

- Runner: `php tests/php-upgrade/run.php [--fail-fast] [filter]` from the repository root; `tests/php-upgrade/ci.sh` and `Dockerfile.ci` show the supported PHP versions. Run the tests of what you changed and the close neighbours, on every supported PHP version.
- A regression file is `tests/php-upgrade/regression/<Name>Test.php` returning `['test name' => callable]`, with the helpers from `bootstrap.php` (`t_equal`, `t_true`).
- When a controller cannot be loaded without the full application, put the rule in a small pure helper class and test that, plus source-level checks that the wiring is in place and that nothing runs without the option.
- Name the test after the issue (for example `Issue524ReinstallTest.php`).

## Module installs

Schema sync and data files are upserts by nature, so running them again on the same version is safe. Going forward every new schema and data file must stay idempotent. An installed module is only updated when its `package.json` version is newer than the database; reinstalling the same version is an explicit opt-in in the back-office.

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

It reads git only and checks the added lines against this file: it fails on CRLF line endings and on new `__()` / `p__()` texts that are not in a `.po` translation catalog, and warns when source changed without a test, or when fragile smells (`|| true`, `sleep`, `@` suppression) were added. `--strict` turns the warnings into failures.
