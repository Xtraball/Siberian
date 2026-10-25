# Changelog

Notable changes to Siberian, newest first. Dates are `YYYY-MM-DD`. Longer notes live in `docs/`.

## [Unreleased] on branch `feat-module-updates` (not released)

### Added

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
