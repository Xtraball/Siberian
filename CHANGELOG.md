# Changelog

Notable changes to Siberian, newest first. Dates are `YYYY-MM-DD`. Longer notes live in `docs/`.

## [5.1.4] 2026-10-26

Bug fixes and small options on top of 5.1.3. The update package is built against 5.1.3 (`lastversion`), the last published version. Modules: Application 5.0.15, Push2 5.0.11, Radio 4.14.11.

### Added

- 2026-10-26 Android: option to remove the photos and videos permissions from the app.
- 2026-10-26 Android: apps with the Push feature declare the POST_NOTIFICATIONS permission (only when the manifest does not declare it already), and Waze and Google Maps are declared in the manifest `<queries>` so the navigation buttons find them.
- 2026-10-26 Android: the splash icon is sharp by default.
- 2026-10-26 Radio: option to keep the stream link as typed.
- 2026-10-26 Settings, custom SMTP: the password is hidden by default, with an eye button to show it (#864).
- 2026-10-26 Modules: a module can be reinstalled at the same version (#524), and an expired or refused licence key can be replaced from the update screen (#859).
- 2026-10-26 `bin/build-app-sources.sh <version>` builds the archives that "Restore Apps sources" downloads (browser, android, ios, ios-noads). The release workflow attaches them to the GitHub release.
- 2026-10-26 `AGENTS.md` and `check-pr.sh`: rules and a checker for contributors and AI agents (new translations go in the `.po` catalogs).

### Fixed

- 2026-10-26 Calendar: the iCal reader keeps folded descriptions, parameters and locations (#296).
- 2026-10-26 Source Code: saving no longer reads or resets the offline flag.
- 2026-10-26 Promote: the smart banner values are escaped in the generated snippet.
- 2026-10-26 Short URLs: TinyURL is called over https, with http as a fallback.
- 2026-10-26 Formatted numbers: negative values are formatted too.
- 2026-10-26 Layout 3 options: removed a stray minus sign.
- 2026-10-26 Publication form: translated with the right function, no more warnings.
- 2026-10-26 Push: scheduled pushes show their send date, and titles are sent without HTML entities.
- 2026-10-26 Android: navigation fix in the shell (`Navigator.java`).

## [5.1.3] 2026-10-25

First release since 5.0.17 (2025-10-04): the update package is built against v5.0.17 and customers on 5.0.17 jump straight to 5.1.3. 5.0.18 and 5.1.0 to 5.1.2 were never published, their changes are all in this release. `lastversion` is 5.0.17 from here on.

### Changed

- 2026-10-25 PHP 8.2 to 8.5 for the three editions. MAE and PE (`siberian-mae`, `siberian-pe`, tags `v5.0.17` and `v5.1.3`) lint clean on 8.2 and 8.5 and carry the SAE changes made since 5.0.17 in the files they override. PE and MAE installs were smoke tested on PHP 8.5 with the contract specs (370 passed, 0 failed on both).
- 2026-10-25 The three editions are built and published from the private `siberian-release` repository (GitHub Actions), no longer by Jenkins.

## [5.1.2] 2026-10-25

Version bump to 5.1.2 (`package.json` `version`; `lastversion` stays 5.0.18), module `Installer` 4.9.2. No update package is published by this tag.

### Fixed

- 2026-10-25 Updater: the Installer backoffice controller imported `Siberian\Exception`, so its `catch (Exception)` blocks missed every other exception. A bad answer from the update server (HTTP 500, not JSON) ended in a redirect to the dashboard instead of an error message; it is now a clean error, and the answer is decoded after its HTTP status is looked at.
- 2026-10-25 Dashboard, Cron scheduler: "Remove locks" is only offered when there are lock files; otherwise the last run is shown with "all good". The action also checks before it removes anything.
- 2026-10-25 Editor: an ajax action called without a `value_id`, or throwing, answers a JSON error (400) instead of an empty HTTP 500, and an action that sends nothing answers an error instead of a slow "page not found". Same for the API controllers.
- 2026-10-25 Backoffice: actions that read a JSON body answer an error for an empty or broken body instead of a 404 (`account_view/save` and 12 others); an action that ends without a response answers a JSON error.
- 2026-10-25 Job: `Job/controllers/Api/CompanyController.php` declared `Job_Api_PlaceController`, so every `/job/api_company/*` endpoint failed with a 500.
- 2026-10-25 `api/service_image/thumbnail` read an undefined variable and failed with a 500.

### Added

- 2026-10-25 `specs/`: controller contract specs for the 903 endpoints (no session, invalid input, valid input) run in parallel against an isolated rig, and `specs/updater/updater.spec.sh`, the critical-path spec of the backoffice updater (drag and drop and remote). See `specs/README.md`.

$1 2026-10-25

Version bump to 5.1.1 (`package.json` `version`; `lastversion` stays 5.0.18 because 5.1.0 was never tagged, so the update package is built against v5.0.18), module `Installer` 4.9.1. Includes 5.1.0 and everything under the `feat-module-updates` heading below. No update package is published by this tag.

### Changed

- 2026-10-25 Backoffice: new light-only default theme (`css/theme-default/styles.css`, CSS variables at the top). Pure CSS, no markup change; the old grey look is gone, Elegance and Modern are still selectable under Appearance > General. Hard-refresh the browser after updating (stylesheet is cached per release).

### Fixed

- 2026-10-25 Module updates: a module installed from an old package has no `code` in the `module` table (InAppLink 2.0.0 was one) and never showed up. It is now matched to the downloader list by name, only when exactly one entry carries that name. The installed module name is read from the column: `Installer_Model_Installer_Module::getName()` returns an unset property.
- 2026-10-25 The downloader's production signing key (`k1`) is pinned in `ModuleUpdates::PINNED_KEYS`; with an empty list every download was refused with `no_signing_key`.
- 2026-10-25 With the store ingress fix on the downloader side (licence checks are cached for an hour; the downloader now refreshes the entry after activating), a freshly activated module licence no longer fails the install-time check with "not correctly activated". Nothing changed in `Siberian\License`.

## [5.1.0] on branch `feature/module-license-key` (not released)

Version bump to 5.1.0 (`package.json`, `lastversion` 5.0.18, sample SAE version 5.0.18), module `Installer` 4.9.0. Includes everything under the next heading.

### Added

- 2026-10-25 Module updates: ask for the licence key of a module that has none saved. A module installed before keys were kept used to be left out of the update list; it is now listed (flagged `license_missing`, "Licence key needed" hint) and, when the update is started, the backoffice prompts for the key. The key travels in the POST body, is checked for shape, and is saved as `<code>_key` only after the downloader accepted it and the package verified; a refused key is never saved and is asked again. New error code `license_required`. Tests: unit and end to end (`tests/e2e-module-updates/flow.sh`).

### Changed

- 2026-10-25 The empty-list message reads "All your modules are up to date." (it no longer implies a licence filter).

## [Unreleased, folded into 5.1.0] on branch `feat-module-updates` (not released)

### Added

- 2026-10-25 The production downloader signing key (k1) is pinned in ModuleUpdates::PINNED_KEYS, so module updates can download and install.

- 2026-10-25 Module updates: a module installed before modules carried a code (no code in the module table, e.g. an old InAppLink) is now matched to the list by its name when the name is unique, so its update shows up. A module without a saved licence key is listed too, and Update asks for the key (sent in the POST body, saved only after the downloader accepted it and the package verified).

- 2026-10-25 Module updates in the backoffice (Settings > Updates & Modules). Siberian checks the downloader's single cached module list, compares versions on its own server, and lists licensed modules that have a newer version. Design and decisions: `docs/module-updates.md`.
  - `Siberian\ModuleUpdates`: conditional fetch of the list (ETag), daily refresh, last good list served for up to 7 days when the downloader is down, version comparison, live terms and their acceptance, verified download.
  - The administrator must accept the terms of download inside Siberian. The terms are served live by the downloader; when their hash changes, Siberian asks again.
  - Updates need both licence keys (Siberian and module). Packages come back signed (Ed25519) with a sha256; both are verified against a pinned public key before anything is written. A package that is not newer than the installed module is refused.
  - New backoffice actions `checkupdates`, `terms`, `acceptterms` (POST), `updatemodule` (POST). `updatemodule` hands the verified package to the existing installer steps (details, permissions, copy, install).
  - Daily cron job "Module updates check" (time differs per installation), module `Installer` 4.8.8.
  - Kill switch: set `module_updates_enabled` to `0` in the settings.
- 2026-10-25 Tests in `tests/php-upgrade/regression/ModuleUpdatesTest.php` (24 checks, PHP 8.5.12).

### Fixed (found by the end-to-end run)

- 2026-10-25 The terms acceptance recorded the literal `backoffice` instead of the administrator's id; it now stores the backoffice user id.
- 2026-10-25 After accepting changed terms, the next update asked for the terms again because the cached list still carried the old hash. The list is now refreshed once when the terms look unaccepted.

### Tests

- 2026-10-25 `tests/e2e-module-updates/`: a Docker rig that runs the whole flow against the downloader branch (real TLS with a private CA, faked licence services). 40 checks, all passing on 2026-10-25. See its README.
- 2026-10-25 The downloader URL can be changed for tests only through the configuration file (`$_config['module_updates_base_url']` in `config.user.php`), never the database.

### Security

- 2026-10-25 The update client always verifies TLS certificates (TLS 1.2 minimum, https only, no redirects, response size cap). `Siberian\License` is unchanged and still has verification off, see `docs/module-updates.md`.
- 2026-10-25 Downloads are refused until a signing key is pinned (`ModuleUpdates::PINNED_KEYS` or the `module_updates_public_keys` setting), so licence keys never leave for an unverifiable service.
