# Changelog

Notable changes to Siberian, newest first. Dates are `YYYY-MM-DD`. Longer notes live in `docs/`.

## [Unreleased]

### Changed

- 2026-11-05 PHP version: PHP 8.2 or higher is now required, it carries the security fixes. The back office advises PHP 8.5 when you run an older supported version.

### Fixed

- 2026-11-05 Editor: the preview of the app keeps the same size when the page is scrolled, it used to grow.
- 2026-11-05 Advanced settings: the back office now has the option that removes the photos and videos permissions, like the editor. In the editor, the options of the Android advanced settings stay in line with their checkbox when the column is narrow.

## [5.1.13] 2026-11-03

Fixes for the fatal errors that installations report, a test suite without false failures, on top of 5.1.12. The update package is built against 5.1.12 (`lastversion`), the last published version. MAE and PE carry the same changes (tags `v5.1.13` on their `master`).

### Fixed

- 2026-11-03 Mobile apps: the analytics calls, the background images on a device of unknown size, the events list, the sample pages of an app without pages, the YouTube channel lookup and calls without an application no longer end in an error.
- 2026-11-03 Images and maps: a damaged image file and an address given as plain text no longer end in an error.
- 2026-11-03 Module updates: a server without the sodium extension now says the update is not verified, instead of an error.
- 2026-11-03 Catalog and analytics: a category without products and an unknown feature in the statistics no longer end in an error.
- 2026-11-03 Facebook: asking for the application token no longer ends in an error.

## [5.1.12] 2026-11-03

A responsive app preview in the editor, on top of 5.1.11. The update package is built against 5.1.11 (`lastversion`), the last published version. MAE and PE carry the same changes (tags `v5.1.12` on their `master`).

### Changed

- 2026-11-03 Editor: the preview of the app on the right now fits its column, keeps its proportions and no longer overlaps the content. Below 1200px wide it is replaced by a "Preview" button that opens it over the page.

## [5.1.11] 2026-11-03

A fix for the mobile login, on top of 5.1.10. The update package is built against 5.1.10 (`lastversion`), the last published version. MAE and PE carry the same change (tags `v5.1.11` on their `master`).

### Fixed

- 2026-11-03 Mobile login: a module listening to a hook (for example `mobile.login`) that fails with a PHP 8 error no longer makes every login answer a 500. Failing listeners are skipped and logged, like the ones that threw an exception already were, so the login, logout and registration go through (#1004).

## [5.1.10] 2026-11-03

Visible errors on the mobile and web apps, and translations that no longer break the backoffice pages, on top of 5.1.9. The update package is built against 5.1.9 (`lastversion`), the last published version. MAE and PE carry the same changes (tags `v5.1.10` on their `master`).

### Added

- 2026-11-03 Dashboard: the card "Errors on app calls" shows which modules fail on calls of the mobile and web apps in the last 24 hours, warns when one fails repeatedly, and offers "Quarantine now" to disable it by hand (#1001).
- 2026-11-03 Error reports: each group of errors now says where it came from (mobile or web app, editor, backoffice, api or cron), the module involved and how many different endpoints were affected, so repeated failures of a module are easy to find (#999).
- 2026-11-03 Optional circuit breaker for the mobile and web apps: a failing endpoint answers quickly with a "try again later" message instead of running the broken code again and again. Off by default, turn it on with `module_app_circuit_breaker` in `config.php` (#1000).

### Fixed

- 2026-11-03 Backoffice and editor pages: a translation containing an apostrophe (for example "dell'utente" in Italian) no longer breaks the page script, so buttons and confirmations keep working in every language (#998).

## [5.1.9] 2026-11-03

A reworked dashboard and module installer page, and a quarantine that leaves the mobile and web apps alone, on top of 5.1.8. The update package is built against 5.1.8 (`lastversion`), the last published version. MAE and PE carry the same changes (tags `v5.1.9` on their `master`).

### Added

- 2026-11-01 Dashboard: compact Web space and Services cards (services as two lines of pills, a readable storage bar), the cron state apart from its errors, and a quota row to set the web space quota (#990).
- 2026-11-01 Module installer page: one click-or-drop area, a clear "Check for updates" button and feedback where it was silent (#991).
- 2026-11-01 Backoffice: the language menu sits in the navbar, left of the user menu (#989).

### Changed

- 2026-11-03 Module quarantine: a fatal error on a call of the mobile app or of the web app (`<module>/mobile_*`, `front/mobile_*`, `application/mobile_*`) no longer puts the module in quarantine, the call keeps its plain 5xx instead of a 404 on every later request. The editor, the backoffice, the api and the cron still quarantine. `$_config['module_quarantine_app_requests'] = true` brings the previous behaviour back (#996).

### Fixed

- 2026-11-01 Module install: only `<table>.php` files of `resources/db/schema` are tables (#987).
- 2026-11-03 Tests: the user menu, log-out and licence prompt checks follow the new markup (#996).

## [5.1.8] 2026-10-31

Fourteen locales shipped in the application, a safer locale manager, and a store account that survives a cache clear, on top of 5.1.7. The update package is built against 5.1.7 (`lastversion`), the last published version. MAE and PE carry the same changes (tags `v5.1.8`, released from `master`).

### Added

- 2026-10-31 Fourteen locales are shipped with the application, enabled one by one in Settings > Translations; their catalogs were completed and reviewed for errors and typos (#976, #977, #980).
- 2026-10-31 Locale manager: older Locale packs can be compared with the built-in locale, and switching to the built-in locale makes an automatic backup first (#978).
- 2026-10-31 Locale manager: an outdated Locale pack that the built-in languages already cover is flagged, with advice to disable it (#984).

### Changed

- 2026-10-31 Module updates: the licence key prompt opens instead of an error banner (#982).

### Fixed

- 2026-10-31 Store account: the encryption key moves out of `var/cache` to `var/private/store-account`, so "Clear cache" and module updates no longer disconnect the store. A key left in the old folder is moved over; if the key was already lost, the page asks to connect again instead of showing a silent "not connected" (#985).
- 2026-10-31 Translations: the translation cache is cleared when a locale is switched (#979), and a locale or translation the user already has is never overridden (#983).
- 2026-10-31 Cron: the session garbage collector job received the scheduler as the lifetime (#981).
- 2026-10-31 PHP 8.5: the bundled minifier no longer triggers the `parent` callable deprecation, its output is unchanged (#975).

## [5.1.7] 2026-10-30

More PHP 8.5 fixes, a backoffice top menu that fits small windows, and the camera permissions of Android apps, on top of 5.1.6. The update package is built against 5.1.6 (`lastversion`), the last published version. MAE and PE carry the same changes (tags `v5.1.7`, released from `master`).

### Changed

- 2026-10-30 Backoffice: the top menu uses the full width of the window and folds under a toggle button on small windows.
- 2026-10-30 Android: the camera only asks for the storage permissions that the app manifest declares.

### Fixed

- 2026-10-30 PHP 8.5: deprecations and warnings in the translator and the bundled gettext (#945), `null` passed to string functions in the security helpers and the mobile detection (#946), the session save handler is registered as an object and a missing session type is handled (#947), `null` and `false` values no longer reach `foreach` and string functions (#952).
- 2026-10-30 PHP 8.5: the analytics store no longer hands `null` to `addslashes()` (#949), and a first-party sweep of casts, nullable parameters, case terminators and dynamic properties (#950).
- 2026-10-30 Modules: a module init no longer runs the closure of the previous module, and registering a payment gateway twice is harmless (#948).
- 2026-10-30 Encryption: `Cypher::dcRun()` checks that the encrypted counterpart of a file exists before reading it (#951).
- 2026-10-30 Backoffice: the profile drop-down closes after a click on one of its entries; the log-out entry is unchanged.
- 2026-10-30 Backoffice: the design cache of the three editions is rebuilt, so the files added since the last rebuild are sent to the browser.

### Developer

- 2026-10-30 `docs/` describes the issue triage flow, and the triage labels are part of a triage.

## [5.1.6] 2026-10-30

Error reporting, Google Play App Signing, one-time loyalty QR codes and a series of PHP 8.5 fixes, on top of 5.1.5. The update package is built against 5.1.5 (`lastversion`), the last published version. MAE and PE carry the same changes (tags `v5.1.6`, released from `master`).

### Added

- 2026-10-30 Error reporting: errors, warnings, deprecations and fatal errors are grouped on the server (same error at the same place is one group with a counter) and sent once an hour to the Siberian team, to find the bugs that matter. On by default; Settings > Advanced > Configuration turns it off, and nothing is kept on the server while it is off. Only an anonymous random id, the edition, the Siberian and PHP versions, and per error its type, a message without values, the file path inside the installation and the line are sent. See `docs/error-reports.md`.
- 2026-10-30 Android: Google Play App Signing option, with the keystore upload and a backup of the keystore (#855).
- 2026-10-30 Loyalty card: one-time QR codes for employees (#851).

### Fixed

- 2026-10-30 PHP 8.5: four fatal errors of the core (#933), a fatal method signature mismatch on PHP 8, an undefined `HTTP_ORIGIN` on `OPTIONS` requests without an Origin header, and a broken placeholder in a translation no longer throws a `ValueError`.
- 2026-10-30 SCSS: space separated `rgb()` / `hsl()` colors raise a compile error instead of a `TypeError` (#933).
- 2026-10-30 Editor: re-initialising a DataTables table destroys it instead of showing an alert.
- 2026-10-30 Modules: the quarantine probe finds the base classes of the probed module like a web request does.

### Developer

- 2026-10-30 A class link check in the PHP regression suite catches fatal method signature mismatches before a release; `docs/editions.md` describes how SAE, MAE and PE layer.

## [5.1.5] 2026-10-28

Backoffice refresh, a safety net for modules that break on PHP 8, and fixes, on top of 5.1.4. The update package is built against 5.1.4 (`lastversion`), the last published version. MAE and PE carry the same changes (tags `v5.1.5`, released from `master`).

### Added

- 2026-10-28 Modules: a module that stops the application with a fatal error (for example an old `copyTo()` signature on PHP 8) is quarantined instead of taking the editor down. The dashboard shows the quarantined modules with Recheck, Re-enable and Scan, `php cli module:quarantine` does the same from the command line, and `$_config['module_quarantine'] = false` turns it off. The probe skips files that declare a class which already exists.
- 2026-10-28 Cron: the jobs are checked against the PHP version, a "Compatibility with PHP" section on the cron page and a Services line on the dashboard show the result, and one failing job no longer blocks the others (a job whose class or method is missing used to count as a success, it is now a failure).
- 2026-10-28 Settings > General and Settings > Email: sections in a side navigation, each section saves only its own settings.
- 2026-10-28 Settings > Communications: password eye toggle, test field row, and the custom SMTP switch loads as No when nothing is stored.
- 2026-10-28 Backoffice: new top bar contained like the page, coloured logo on the light bar, typed messages with an icon, cleaner login page (no menu flash when logged out, spinner inside the button).

### Changed

- 2026-10-28 Tools page: sections reordered, and the integrity check runs by slices so it finishes on large installations and its result is readable (MAE and PE got the same controller).
- 2026-10-28 Translations: the suggestions (dead Weblate service) are removed, the machine translation is kept and repaired (the services are asked with curl, Google answered 429 to the PHP stream wrapper).

### Fixed

- 2026-10-28 Layouts: `user-style.css` is only linked when the file exists (no more 404 on every page).
- 2026-10-28 Backoffice default theme: progress bars are aligned in table cells.
- 2026-10-28 Settings: the read of all settings is not cached, so a section save shows what was stored.

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
