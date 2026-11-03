# Module quarantine

A module of `app/local/modules` written for an older PHP or an older core can stop the whole application with a
fatal error that no `try/catch` can stop. The typical case is a method that no longer matches the core one, for
example `copyTo($option)` where the core has `copyTo($option, $parent_id = null)`: PHP 8 refuses to load that class,
and every page that touches the module (the editor feature list for one) shows an error page.

The quarantine keeps the rest of the application working and tells the administrator.

## What puts a module in quarantine

| Reason | Meaning |
| --- | --- |
| `fatal` | A fatal error came from a file of `app/local/modules/<Module>/` while a page was served. The shutdown handler records it, and the page is replayed once without the module (a POST or an ajax call gets an explained error instead, never a loop). |
| `probe` | A compatibility check loaded the classes of the module in a separate process and that process died. |
| `version` | The module asks for a newer Siberian than the installed one (`dependencies.system.version` in its `package.json`). |

| `manual` | An administrator pressed "Quarantine now" on the dashboard card "Errors on app calls" (see below). Nothing puts a module in quarantine for its errors on app calls by itself. |

Core modules are never quarantined.

### Errors on app calls

Since 5.1.9 a fatal error on a call of the mobile or web app is not recorded in the quarantine. The dashboard shows
them instead, in the card **Errors on app calls**: per module of `app/local/modules`, the fatal errors and errors of the
last 24 hours, the number of distinct endpoints, the last time and the last message. A module with 5 or more fatal errors
within about 10 minutes (the history is kept in buckets of 2 minutes, so the window is 10 to 12 minutes) turns the card
red with a warning. **Quarantine now** puts the module in quarantine with the reason `manual`, after a confirmation;
**Recheck** and **Enable** work as for any quarantined module. The card needs the error reports to be on (Settings >
Advanced > Configuration): nothing is recorded while they are off, and the dashboard says so. Source: the local history of
`Siberian\ErrorReporter`, see `docs/error-reports.md`.

Only the editor, the backoffice, the api and the cron can put a module in quarantine. A fatal error on a call of the
mobile app or of the web app (`<module>/mobile_<controller>/<action>`, `front/mobile_*`, `application/mobile_*`, `mobile/*`)
is not recorded: the visitor keeps the plain 5xx of the failing call and the module stays enabled, so one broken call
does not turn every later request of the apps into a 404. A module that is already in quarantine stays disabled for
everyone. `$_config['module_quarantine_app_requests'] = true;` brings the previous behaviour back.

## What a quarantined module is

It is treated like a module switched off by hand (`module.disabled`): its controllers, bootstrap, cron jobs and design
cache are skipped, it is listed as disabled in Settings > Advanced > Modules with a `Quarantine` badge, and its
classes are not autoloaded. It is **not** touched on disk.

The records are in `var/quarantine/modules.json` (outside the caches that "clear cache" empties). A quarantine never
expires by itself. When the module, the core or PHP changed since, the record is flagged "updated since" and the
dashboard asks to recheck it.

## The dashboard

The backoffice home shows `Modules in quarantine: X/N` with the reason and the PHP message of each module, and:

- **Recheck** loads the module again in a separate process and releases it when it works (or keeps it, with the new message).
- **Re-enable** releases it anyway. If it breaks the application again it goes back to quarantine.
- **Recheck all** and **Scan all modules**. A scan checks every module of `app/local/modules` that is not switched off,
  in the background, and quarantines the incompatible ones before a page breaks. After PHP or Siberian changed since the
  last scan the dashboard suggests to run one.

Enabling the module with its checkbox in Settings > Advanced > Modules releases it as well.

## Command line

```
php cli module:quarantine                 # list
php cli module:quarantine recheck [Module]
php cli module:quarantine release Module
php cli module:quarantine scan
```

A probe loads the files of `Model`, `Form`, `Plugin` and `controllers` of the module, never install scripts. Run the
scan with the same PHP as the web server (`php -v`).

## Circuit breaker for the app calls (opt-in)

A fatal error on a call of the mobile app or of the web app is not quarantined, so a broken endpoint keeps running its
broken code on every call. The circuit breaker stops that, for the app calls only. **Off by default**:

```php
$_config['module_app_circuit_breaker'] = true;
// optional, these are the defaults
$_config['module_app_circuit_breaker_threshold'] = 5;         // fatal errors ...
$_config['module_app_circuit_breaker_window_minutes'] = 10;   // ... within this window open the circuit
$_config['module_app_circuit_breaker_cooldown_minutes'] = 5;  // how long it stays open
```

- A fatal error of a module on an app call is counted for its **endpoint** (the first three path segments, numbers
  dropped: `xdelivery/mobile_cart/add`). At the threshold the circuit of that endpoint opens.
- While it is open, app calls to that endpoint get `503` with `Retry-After` and `{"error":true,"circuit_open":"<Module>","retry_after":<seconds>}`
  **before any module code runs**. Other endpoints, the editor, the backoffice, the api and the cron are not touched.
- After the cool-down one call is let through (half open), the others still get the 503. When it ends without a fatal
  error the circuit closes; when it dies again the circuit opens for another cool-down. A test call that never ends
  (killed) frees its place after 30 seconds.
- The check is in `index.php` right after `config.php`: the configuration is there and no framework or module code has
  run. The shutdown handler records the fatal errors, before the quarantine decides about them.
- State: `var/quarantine/breaker.json` (outside the caches that "clear cache" empties), capped at 200 endpoints, quiet
  endpoints are forgotten. Only fatal errors count; an exception that ends in a 500 does not.

## Turning it off

```php
$_config['module_quarantine'] = false;
```

in `config.php` or `config.user.php`. Nothing is quarantined or blocked then, the previous behaviour.
