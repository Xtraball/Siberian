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

Core modules are never quarantined.

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

## Turning it off

```php
$_config['module_quarantine'] = false;
```

in `config.php` or `config.user.php`. Nothing is quarantined or blocked then, the previous behaviour.
