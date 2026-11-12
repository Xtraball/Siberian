# Updating the platform from the command line

`php cli update:check` and `php cli update` do what Settings > Updates & Modules does in the back office, with the same
classes and in the same order. Nothing is a second implementation: the back office controller calls the same code.

```
cd /path/to/siberian
php cli update:check            # what is available, nothing is changed
php cli update                  # check, download, install
php cli update --confirm=yes-proceed-to-update-6.0.0     # a major update
php cli update --reinstall      # install again the version that is already there
```

## The steps

| Step | Back office | Command line | Code |
| --- | --- | --- | --- |
| Check | "Check for updates" | `update:check`, first part of `update` | `Siberian\Update\Platform::check()` |
| Licence | MAE/PE send the licence key and the host, SAE sends `sae=1` | the host is `main_domain`; no key stops with "There is no CMS license key set." | `Platform::fields()` |
| Channel | `update_channel`: production, stable, beta, preview | same setting | `Platform::server()` |
| Download | the package is posted for with the same fields, written to `var/tmp` | same, the file name is reduced to its base name | `Platform::download()` |
| Read the package | version, release note, restore apps | same | `Installer_Model_Installer::parse()` |
| Major update | the typed phrase `yes-proceed-to-update-<version>` | `--confirm=<phrase>`, or typed when a terminal is attached; `--yes` never replaces it | `Cli::confirmPhrase()` |
| Same version | "Reinstall?" | `--reinstall`, or asked on a terminal; off by default | `Installer_Model_Installer_Reinstall` |
| Permissions | web user | same | `Installer_Model_Installer_Update::checkPermissions()` |
| Copy | files copied over the installation | same | `Installer_Model_Installer_Update::copyPackage()` |
| Install | modules, data, template, caches | same, in a **new process** (`update:install`) | `Installer_Model_Installer_Update::installPending()` |
| Finalize | autoupdater marker, cache builder cron host | same, with `use_https` + `main_domain` | `Installer_Model_Installer_Update::finalize()` |
| After | restore the apps sources, or rebuild the manifest | scheduled for the next cron run | `Backoffice\Model\Tools::scheduleTask()` |

The copy replaces PHP files that the running process has already loaded. The back office installs in another HTTP
request, so the CLI installs in another process (`php cli update:install`), with the new code only. If an install stops
half way, run `php cli update:install` again: the modules already installed are skipped.

## Differences with the back office

- The install is not cut by the PHP time limit, so there is no "installation will continue" round trip.
- The apps manifest or sources are rebuilt by the cron (the back office triggers them right away from the browser),
  so the cron must run.
- Only one update runs at a time (`var/tmp/cli-update.lock`).
- The check and the download now use `Siberian\Request` for both ways of updating (30 s for the check, 1800 s for the
  download) instead of `Zend_Http_Client` in the controller.

## Exit codes

0 done or up to date, 1 failed, 2 wrong usage, 3 major update not confirmed, 4 another update is running.
