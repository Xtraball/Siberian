# Error reports

Status: built, tested, not released. Counterpart of the collector in `Xtraball/marketplace-module-downloader` (`docs/error-reports.md` there has the full API contract and the limits).

## What it does

Errors, warnings, notices and deprecations that PHP raises in this installation, and fatal errors, are grouped on the server and pushed once an hour to `https://downloader.siberiancms.com/api/v1/errors/reports`. The Siberian team reads the aggregate across installations to find the bugs that matter.

- **On by default**, like `send_statistics`. Turn it off in Backoffice > Settings > Advanced > Configuration ("Send grouped error reports to improve Siberian"). While it is off nothing is kept on the server.
- Same level, message and file:line is one group with a counter (`count`, `first`, `last`).

## What is sent

Per report: a random install id and secret (generated on the first push, stored in `system_config`, not derived from the licence, host or IP), the edition (`sae`/`mae`/`pe`), the Siberian version and the PHP version.

Per group: level, a scrubbed message, the file path **relative to the install root**, the line, the counters.

Never sent: request data, users, IP addresses, host names, licence keys, SQL values, stack-trace arguments, absolute paths. Messages lose quoted values, numbers, e-mail addresses, URLs, long hex strings and absolute paths before they are stored (`ErrorReporter::normalize()`), and are cut at 300 characters.

## Where the code is

- `siberian/lib/Siberian/ErrorReporter.php`: the service. No framework or database dependency, so it is unit tested with a scripted transport (reuses `ModuleUpdates\Transport` and `Store`).
- `siberian/app/sae/modules/Core/Model/ErrorReports.php`: glue (install root, `system_config`, cron entry point, `boot()`).
- `siberian/app/Bootstrap.php` `_initErrorMessages()`: registers the handlers after the existing ones (chained).
- Cron: `Installer/resources/db/data/installer_error_reports_cron.php` (hourly, minute from a per-installation seed; runs when the `Installer` module version changes, 4.9.3).
- Setting: `error_reports_enabled` in `System/resources/db/data/system.php` (System 4.20.23), `Backoffice_Advanced_ConfigurationController::$_codes`, `cron.phtml`.
- Tests: `tests/php-upgrade/regression/ErrorReporterTest.php`.

## Things to remember

- **Production runs with `error_reporting(0)`**, which also hides `@`-suppressed errors from handlers, so a handler could not tell them apart. In production the reporter raises `error_reporting` to `E_ALL`, sets `display_errors=0` and `log_errors=0`, and its handler returns `true` (the standard handler is skipped): what is displayed and logged stays what it was. Outside production, PHP's own handling carries on and the previous handler (`Siberian\Error`) is chained.
- Fatal errors are found at shutdown with `error_get_last()` (fatal errors have no handler), then the request's groups are merged into `var/cache/error-reporter.json` under a lock. The cap is 500 groups; the overflow is counted as `dropped` and reported.
- The push backs off: one hour, doubling to 24 hours, longer when the downloader sends `retry_after`. The groups are kept meanwhile. A refused report (400, 403, 413, 422) is dropped; a refused key (403) resets the install identity.
- Only the configuration file can point the push somewhere else (`error_reports_base_url`), never the database.
- Caught exceptions are not reported (only what PHP raises and fatals); a hook for the Zend error controller is a follow-up.
