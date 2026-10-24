# Module updates

Status: built on branch `feat-module-updates`, tested, not released. Counterpart of `feat-module-update-api` in `Xtraball/marketplace-module-downloader` (`LOGBOOK/features/feat-module-update-api.md` there has the API contract).

## What it does

1. A daily cron (and the Check now button) fetches `https://downloader.siberiancms.com/api/v1/modules.json`, one cached JSON with every module version and the live terms hash.
2. Siberian compares it with the `module` table (only modules with a `code` and a saved `<code>_key`) and shows the available updates.
3. Before the first update, the administrator reads and accepts the terms of download (served live by the downloader). A changed hash means asking again.
4. Update: `POST /api/v1/modules/{code}/download` with both licence keys, the accepted terms hash, a timestamp and a nonce. The package comes back with `X-Package-Sha256` and an Ed25519 signature.
5. Siberian verifies both, stores the `.sib` in `var/tmp`, then the existing installer takes over (package details, permissions, copy, install).

## Where the code is

- `siberian/lib/Siberian/ModuleUpdates.php`: the service. No framework or database dependency, so it is unit tested with a scripted transport.
- `siberian/lib/Siberian/ModuleUpdates/`: `Transport` (+ strict `CurlTransport`), `Store` (+ `ConfigStore` on `system_config`), `Exception` (stable error codes).
- `siberian/app/sae/modules/Installer/Model/ModuleUpdates.php`: glue (installed modules, licence keys, tmp dir, cron entry point).
- `Installer_Backoffice_ModuleController`: `checkupdatesAction`, `termsAction`, `accepttermsAction`, `updatemoduleAction`.
- Backoffice UI: `design/desktop/backoffice/{template,js}/installer`.
- Cron: `Installer/resources/db/data/installer_module_updates_cron.php` (runs when the `Installer` module version changes, 4.8.8).

## Things to remember

- **Downloads are off until a key is pinned.** `ModuleUpdates::PINNED_KEYS` is empty on purpose. After the downloader signing key exists (`scripts/generate-signing-key.php` there), put its public key (from `/api/v1/keys.json`) in `PINNED_KEYS`. Existing installations can receive a key without a release through the `module_updates_public_keys` setting (JSON, key id to base64). Keys are never taken from the network.
- Settings used: `module_updates_enabled` (`0` disables everything), `module_updates_terms_hash`, `module_updates_terms_version`, `module_updates_terms_accepted_at`, `module_updates_terms_accepted_by`, `module_updates_available` (count, written by the cron), `module_updates_public_keys`.
- The list is cached in `var/cache/module-updates.json`. Fresh for 1 day, served stale up to 7 days if the downloader is down (the UI shows a warning), then an error.
- The module `code` in the `module` table must equal the downloader `download_key`. Modules installed from older packages without a code are skipped.
- The EDD licence check is unchanged (`Siberian\License` still has TLS verification off). Fixing it is a separate change.
- The terms HTML comes from the downloader (already escaped there) and is shown in the backoffice with `swal html:true`. Trusting that depends on TLS and on the downloader.
- The backoffice has no CSRF token mechanism; the state-changing actions require login and POST, nothing more.

## Not done yet

- Module folder backup before the install (the regular install overwrites in place, so a backup belongs to the copy step; rollback is a reinstall of the previous package).
- An updates badge in the main menu (`module_updates_available` is already written by the cron).
- Install lock beyond the existing flow, per-module release notes, and a staging pass against the deployed downloader.

## Decisions

### 2026-10-25

- **Decision:** the service has no dependency on the application, the glue lives in `Installer_Model_ModuleUpdates`. **Why:** the part that handles keys, signatures and caching must be testable without a database. **Impact:** tests run on the bare regression runner.
- **Decision:** a pinned empty key list refuses downloads before any request leaves. **Why:** sending licence keys to a service whose packages cannot be verified is worse than no feature. **Impact:** the release that ships this needs the key pinned first.
- **Decision:** `updatemodule` returns the same payload as `downloadupdate`, and the UI continues with the existing steps. **Why:** one install path for uploads, core updates and module updates.
- **Decision:** the terms are fetched live and the accepted hash is compared on every list refresh. **Why:** a terms change must reach every Siberian without a release. **Impact:** accepting a stale page is refused (`terms_changed`).
