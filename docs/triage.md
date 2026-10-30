# Issue triage flow

How we look at a new issue, in order. The aim is a first answer that is correct, polite and checkable, written
as a draft that a maintainer reads before it is posted. This file is meant to be changed: when a triage goes
wrong or a step was missing, fix the flow here in the same PR (see "Improving the flow").

## Verdicts

Every issue gets exactly one verdict.

| Verdict | Meaning | Label to add |
|---|---|---|
| **to-do** | Real, in scope, we will change something. | `bug` or `enhancement`, a `priority: high\|medium\|low`, an `area: ...`, and `has patch` when the report carries a usable fix |
| **comment** | We cannot decide yet: a reproduction, a log, a version or an answer is missing. | `needs-repro` (or `question`) |
| **already done** | Trunk or a tagged release already has it (an option, a fix, a test). | `fixed in trunk`; close after the reporter has had a chance to confirm |
| **dismiss** | Out of scope, not a defect, duplicate, or the change would break existing users. | `wontfix`, `duplicate` or `invalid`; close with the reason |

Add `security` for hardening reports, and `edition: MAE` / `edition: PE` when the change is not SAE only.
Use the labels that exist (`gh label list --repo Xtraball/Siberian`); do not invent new ones in a triage.

## Steps

1. **Read it whole.** What is asked, what is observed, what is the proposed fix, what version it was seen on.
   A report written against a fork or a private patch is evidence, not a spec: its file names may not exist here.
2. **Verify on the default branch, never from memory.** `git fetch origin`, then look at `origin/trunk`
   (`git show origin/trunk:path`, `git grep -n term origin/trunk -- dir`). If the report names a version, also
   check that tag (`git tag --contains <commit>`). Quote the commit or tag in the answer.
3. **Look for what already exists.** An option for it (`git grep -i option_name`), a regression test
   (`tests/php-upgrade/regression/Issue<N>*Test.php`), an older issue (`gh issue list --state all --search "..."`).
   Most "missing feature" reports for permissions, location, push or radio hit an existing option.
4. **Check the editions.** Anything under `siberian/app/sae` may be overridden in MAE or PE
   (`docs/editions.md`, `docs/editions/OVERRIDES.md` in the private repos). Say in the notes if a fix must be
   ported. Never name a MAE/PE file in a public comment.
5. **Classify the cost.** Template files in `siberian/var/apps/ionic/**` (Android/iOS sources) change what every
   exported app contains: they need a device build to verify, so say "not verified on device" when that is true.
6. **Decide the verdict** with the table above. When two fit, prefer the more cautious one
   (`comment` before `to-do`, `to-do` as an option before a default change).
7. **Write the draft** with the template below. Keep it short, specific, and kind.
8. **Decide what is next** for a `to-do`: size (one-line, small PR, project), whether it can be an option that is
   off by default, and which test will fail without the change.
9. **Park the draft for review.** Drafts live in the triage notes (not posted). A maintainer posts or edits them.
   Nothing public is posted by a tool on its own.

## Rules that come from experience

- **Option first.** A change to what existing apps do ships as an option, off by default, modelled on an
  existing one (`disable_location`, `disable_media_permissions`). A bug fix may change output only where it was broken.
- **Every fix PR carries a test** that fails on trunk and passes with the fix, on every supported PHP version.
  For native files, a source-level test is the floor, and the PR says what was not built or run.
- **Count before opening.** Keep the number of open PRs within the stated budget; list them first.
- **A reporter's fix is welcome, not copied.** Ask for a PR or a patch against `trunk`; credit them.
- **Do not promise dates or releases.** Say what trunk does today and what we will look at.
- **Security-sensitive reports** (credentials, injection, access) are not triaged in the open: ask the
  reporter to use the security advisory form and stop there.
- **Wide wildcards and platform minimums** (`allow-navigation *`, the minimum iOS/Android version) change what
  every app can do or run on: dismiss as a default, accept only as an option or in a major release.

## Draft comment template

```
Hi, and thanks for the detailed report.

<what trunk does today, with the evidence: file, tag or commit, option name>

<verdict-specific part>
- to-do:        what we will do, whether it is an option, what we need from you (patch against trunk, device/OS).
- comment:      the exact questions (version, device, logcat/console output, steps), at most three.
- already done: where it is (option, release) and how to use it; ask them to confirm with a current export.
- dismiss:      the reason in one or two sentences and the alternative if there is one.

Thanks again.
```

## Review notes (not posted)

Each triaged issue gets a short note for the maintainers:

```
#N  <verdict>   area: android|ios|backoffice|module|other   priority: low|normal|high
Checked: origin/trunk@<short sha> (+ tags)   Existing: <option/test/issue or none>
Editions: none | port to MAE | port to PE      Cost: <one line>
Draft: <the comment>
```

## When the triage becomes a task

- Open a PR per `AGENTS.md` (tests, translations, design caches). Reference the issue with `Fixes #N` only
  when the PR closes all of it; otherwise `Refs #N`.
- The first comment on the issue after the PR is opened links it and says what was and was not verified.

## Improving the flow

After each batch, add what surprised you: a check that would have saved time, a wrong verdict and why, a rule
the maintainers stated. Keep it to rules and checks, not per-issue history (that belongs in the issue).
Small edits go straight to a PR against this file.
