# Module installer page

Settings > Updates & Modules (`/installer/backoffice_module`). This page documents how it behaves **today on `trunk`** (derived from the code, not from guesses), the caveats found, and what the pull request that added this file changes. For the module updates fed by the downloader (the "Module updates" table) see also [module-updates.md](module-updates.md).

Nothing in the PHP update logic (upload, parse, permissions, copy, install, licence, module updates) was changed. The one PHP edit is a new text (`licenseNeeded`) in the `load` action.

## Where the code is

| What | File |
|---|---|
| Page template | `siberian/app/sae/design/desktop/backoffice/template/installer/module.phtml` |
| Page controller (AngularJS) | `.../backoffice/js/controllers/installer.js` |
| HTTP calls | `.../backoffice/js/factories/installer.js` |
| Actions | `siberian/app/sae/modules/Installer/controllers/Backoffice/ModuleController.php` |
| Archive extraction, dependency checks, permissions, copy | `siberian/app/sae/modules/Installer/Model/Installer/Module/Parser.php` |
| Schema and data install of the copied module | `.../Installer/Model/Installer/Module.php`, `Installer.php` |
| Module updates (downloader) | `.../Installer/Model/ModuleUpdates.php`, `siberian/lib/Siberian/ModuleUpdates*` |

Every action answers JSON; an answer with `error: true` is sent with HTTP 400 (`_sendJson`), so the page receives it in its error callback.

## The flow

```
choose / drop a file ──► upload ──► package details ──► (licence) ──► Install
check for updates ─────► downloadupdate ─┘                              │
Module updates > Update ► updatemodule ──┘                              ▼
                                         checkpermissions ► (FTP form) ► copy ► install ► reload
```

All three entries end in the same steps: package details, permissions, copy, install.

## Gherkin, as it works today

```gherkin
Feature: Install a new module by upload

  Background:
    Given I am logged in the backoffice on Settings > Updates & Modules

  Scenario: Choose a file with the picker
    When I choose "module.zip"
    Then the file is added to the "Module to upload" table, nothing is sent yet
    And I must click the small upload icon of that row to send it            # trunk, see caveat 1

  Scenario: Drop a file
    When I drop "module.zip" on the dashed area
    Then it is added to the "Module to upload" table like the picker does, nothing is sent yet

  Scenario: Only one file at a time
    Given a file is already in the table (sent, failed or not)
    When I choose or drop another file
    Then it is refused with "You can't upload more than one file at a time."
    And the only way out is to reload the page                                 # trunk, see caveat 6

  Scenario: The file is larger than PHP accepts
    Given upload_max_filesize or post_max_size is smaller than the file
    When the file is added
    Then the toast says it is larger than `post_max_size` or `upload_max_filesize` and the file is removed

  Scenario: Upload accepted
    When the upload succeeds
    Then the package is extracted in var/tmp and checked (see "Invalid or refused archive")
    And the "Package Details" card shows the name, the version and the description (or a link to the release notes)
    And the "Install" button is available

  Scenario: Demo instance
    Given the instance is a demo
    Then the upload answers "This is a demo version, no modules can be uploaded"

  Scenario: Hosted MAE or PE instance, module not sold on the marketplace
    Then the upload answers "Only modules sold on our marketplace can be installed on your Hosted service ..."
    # SAE is never restricted; the whitelist can be bypassed by the configuration file.
```

```gherkin
Feature: Invalid or refused archive (upload, update download, all share Parser)

  Scenario Outline: The package is refused
    When the archive is extracted and checked
    Then the toast shows "<message>"
    Examples:
      | situation                                         | message                                                                       |
      | var/tmp is not writable                           | #19-001 The folder ... is not writable. Please fix this issue and try again.  |
      | the temporary file vanished                       | #19-102 Unable to find the file tmp.                                          |
      | not a zip, or zip/unzip missing                   | #19-002 Unable to extract the archive. Please make sure that the 'zip' and 'unzip' commands are installed. |
      | no package.json                                   | #19-010 The package you have uploaded is invalid.                             |
      | package.json is not JSON                          | #19-011 The package you have uploaded is invalid. (the exception is logged)   |
      | a file contains "nwicode" (typed packages only)   | #88-888 The archive contains Nwicode code and may break your Siberian installation ... |
      | module requires a newer Siberian or edition       | #19-014 Your system doesn't meet the requirements ..., version >=X is required. |
      | module requires other modules                     | #19-015 The module your are about to install requires the following ones, A@1, B@2. |
      | same or older version already installed           | #19-016 You already have installed this module or a newer version.            |
      | core update built for another edition             | #19-003 This update is designed for the X, you can't install it in your Y.    |
      | core update already installed                     | #19-004 You already have installed this update.                               |
      | core update needs a newer Siberian first          | #19-005 / #19-007 / #19-009 Please update your system to the X version ...    |
      | template already installed                        | #19-008 You already have installed this template.                             |

  Scenario: The upload keeps the extracted package until it is installed
    Then the installed-module row is not written at upload time (the upload skips the save), the downloaded update does save it
```

```gherkin
Feature: Licence of an uploaded or updated module

  Scenario: The package has no licence code
    When the package details are ready
    Then the Package Details card is shown at once

  Scenario: The package has a code and an item id, no key is saved
    When the package details are ready
    Then the licence is checked (GET check-module-license) and the answer is "license_empty"
    And a prompt asks "Please enter your module license before installing!" with Validate / Go back!
    And an empty value answers "License is required!" inside the prompt

  Scenario: The key typed is valid
    When I press Validate
    Then the key is checked by the licence service and saved as <code>_key (GET set-module-license)
    And the toast shows the success message and the Package Details card appears

  Scenario: The key typed is refused
    Then the prompt stays open and shows the refusal message

  Scenario: The saved key is refused ("license_error")
    Then the same prompt opens so a new key can be typed

  Scenario: The saved key is expired or replaceable ("license_replaceable")
    Then the prompt explains it and offers "Keep the current key"
    And keeping it shows the Package Details card and the install goes on as before

  Scenario: I cancel the prompt (Go back!)                                   # trunk
    Then the card stays hidden and nothing tells me why                        # fixed in this PR, see caveat 3
```

```gherkin
Feature: Install

  Scenario: Same version is already installed
    Given the package details say can_reinstall
    When I press Install
    Then a confirmation asks to reinstall; "No, cancel" stops the whole install
    # in practice the parse step already refused the same version (#19-016), see caveat 4

  Scenario: The package has release notes to show
    When I press Install
    Then a modal shows them in an iframe with "Dismiss & install later" and "Yes, install the update."

  Scenario: Major update
    Then I must type yes-proceed-to-update-<version> exactly, a mismatch is refused

  Scenario: Permissions are fine
    When the permission check runs (GET checkpermissions)
    Then every destination folder and file, and every file to delete, is checked with is_writable
    And on success the copy starts at once

  Scenario: Some files or folders are not writable
    Then the check also tries the saved FTP settings (connection and Siberian directory)
    And if that does not work the page shows the FTP form and the list of paths to fix, with a Retry button
    And saving the form checks the connection ("Unable to connect to your FTP ...") and the path ("Unable to detect your site ...")
    And a valid form is saved in the configuration and the copy starts

  Scenario: The permission check fails without a message (server error)
    Then the page clears the app_manifest cache and reloads itself after 1.5 seconds     # caveat 8

  Scenario: Copy
    Then files to delete are removed, the module folder is emptied when the package says replace_module
    And missing folders are created (mkdir, or FTP), files are copied (or sent by FTP)
    And an unwritable folder answers "#19-013 The folder X is not writable." (several: a list)
    And on success the extracted folder is deleted

  Scenario: Install
    Then caches are cleaned, every module whose package.json is newer than the database is installed (schema, then data)
    And templates, design, translation and minify caches are cleared, the cache builder cron is enabled
    And near the PHP time limit the answer is "reached_timeout" and the page calls again, up to 5 times
    And on success the toast says "Module successfully installed", app_manifest is cleared, apps are restored if the package asks for it, and the page reloads after 1.5 seconds
    And an exception shows its message and a Retry button on the Installing row
```

```gherkin
Feature: Check for updates (Siberian itself)

  Scenario: Nothing to update
    When I click the button
    Then the server asks <channel>-updates02.siberiancms.com/check.php (channel: production, stable, beta or preview, stable by default)
    And the toast says "Your system is up to date."

  Scenario: An update is available
    Then the package is downloaded to var/tmp in the same request (POST, sae=1, or licenseKey and host on MAE/PE)
    And the page continues with the Package Details, permissions, copy and install steps (no module licence)

  Scenario Outline: The check or the download fails
    Then the toast shows "<message>"
    Examples:
      | situation                                  | message                                                       |
      | exec() is disabled                         | Please enable/add function: exec()                            |
      | MAE/PE without a CMS licence key           | There is no CMS license key set.                              |
      | empty answer                               | An error occurred while loading. Please, try again later.     |
      | non 200 or not JSON                        | Unable to check for updates now. Please, try again later. (or the server's own message) |
      | download answered empty / not 200 / no file| #100 / #101 / #102 Unable to fetch the update. Please, try again later. |
```

```gherkin
Feature: Module updates from the downloader (table "Module updates")

  Scenario: Page opened
    Then the cached list is read (fresh for one day, served stale up to seven days with a warning)
  Scenario: Check now
    Then the downloader is asked again; "All your modules are up to date." when the list is empty
  Scenario: Update, terms not accepted yet (or changed)
    Then the live terms open; "I agree" records them, then the update continues
  Scenario: Update, no module key saved
    Then a prompt asks for the key (the key travels in the POST body), it is saved only after the package verified
  Scenario Outline: The update is refused
    Examples:
      | code             | message                                                   |
      | not_found        | There is no update for this module.                       |
      | siberian_too_old | This update requires Siberian X or newer.                 |
      | license_missing  | The Siberian licence key is required.                     |
      | license_required | Please enter the licence key of <module>.                 |
      | module_license   | This licence key is not valid. (prompt again)             |
      | disk_full        | Not enough free disk space to download the update.        |
      | demo             | This is a demo version, no modules can be updated.        |
  Scenario: The package is downloaded and verified
    Then it goes through the same Package Details, permissions, copy, install steps
```

## What this pull request changes (UI only)

- **One click-or-drop area** replaces the picker block, the "OR" and the drop block. Click, Enter or Space opens the file dialog, a drop works the same. The file is **sent as soon as it is chosen**, the area shows the file name, a spinner and the progress, then "Module sent", the licence check, or the error. Choosing another file after a failure or a sent file replaces it, unless it is being sent or the installation started.
- **"Check for updates"** is a large primary button with an icon and a label. It still calls the same action. It is disabled while it runs, shows "Checking for updates...", then the result stays next to it ("Your system is up to date.", the error, or "Update downloaded").
- Feedback where the page was silent: a spinner on "Check now", on the Update row being downloaded and while the module list loads; the loader text says "Downloading the update" during a module update; the licence check has a loader; cancelling the licence prompt is explained; the page scrolls to the package details and the installation.

## Caveats found

Ranked. "Fixed" means fixed by this pull request, the others are left on purpose because they touch the update logic or need a decision.

| # | Severity | Caveat | Status |
|---|---|---|---|
| 1 | High | A chosen or dropped file was only added to a table at the bottom; nothing was sent until a small upload icon was clicked, so it looked like nothing happened. | **Fixed** (sent at once, loader and name in the area) |
| 2 | High | "Check for updates" was an unlabeled icon button; the check and the download are one request, with only a toast as result, and an unreachable server could throw in the page (`data.message` of an empty answer). | **Fixed** (label, loader, persistent result, safe error). The check and the download stay one request. |
| 3 | Medium | Cancelling the licence prompt left the page silent, the package details never appeared. | **Fixed** |
| 4 | High | A module of the same version cannot be reinstalled from an upload: `Parser::_checkDependenciesModule` refuses it with #19-016 before the reinstall confirmation (#524) can appear. Found by reading the code, not run on a real install. | Not fixed, product decision |
| 5 | High | Core update packages are downloaded with TLS verification off and no signature check (module updates are signed and verified). `check.php` is also asked with TLS verification off. | Not fixed, update logic |
| 6 | Medium | After a failed or finished upload the page refused every other file ("only one at a time") until reload. | **Fixed** |
| 7 | Medium | The licence key typed for an uploaded module is sent in the URL (GET `set-module-license`), so it lands in access logs. The module updates flow uses POST. | Not fixed, update logic |
| 8 | Medium | A failing permission check without a message silently clears a cache and reloads the page after 1.5 s, losing the uploaded package context. | Not fixed |
| 9 | Medium | The copy step showed an empty toast on error (`setText()` without text). | **Fixed** |
| 10 | Medium | A non JSON upload answer (proxy page, 413) threw in the page and showed nothing. | **Fixed** |
| 11 | Medium | The "Installing" progress bars are driven by timers, not by the server; a long step looks stuck. No backup or rollback of the module folder before the copy (see module-updates.md, "Not done yet"). | Not fixed |
| 12 | Medium | The upload error answer contains the PHP stack trace (`trace`). | Not fixed, update logic |
| 13 | Medium | The text "We are checking for updates" was shown while a module update was downloading; there was no loader on the licence check or the module list. | **Fixed** |
| 14 | Low | Restoring apps after an install referenced an undefined variable (`manifestData`), so its message failed. | **Fixed** |
| 15 | Low | A size limit of 0 or -1 (unlimited post_max_size) refused every file; the missing `ini` data crashed the size check. | **Fixed** |
| 16 | Low | `checkpermissions` and `copy` answer an empty 200 body when called without `file`. | Not fixed |
| 17 | Low | Dropping a file next to the area makes the browser open it and leave the page. | Not fixed |
| 18 | Low | A file that is not a zip is only refused by the server (#19-002, whose text blames the zip and unzip binaries). | Not fixed |
| 19 | Low | A few texts are English-only strings in the JavaScript (terms dialog, generic install error). | Not fixed |
| 20 | Low | `Installer.checkForUpdates()` calls an action that does not exist; the page does not use it. A test pins this. | Documented |

## Tests

- `tests/php-upgrade/regression/InstallerModuleUiTest.php`: the page structure, the texts and their catalog, that the actions the page uses still exist, and the known dead URL.
- `tests/jsdom/installer-module-ui.js`: the real controller, template and angular-file-upload in jsdom, checking the console on every action (picker, keyboard, drop, upload success, refusal, non JSON error, too large, licence checking and cancel, check for updates in all four outcomes, double click, copy error). `NODE_PATH=<dir with jsdom> node tests/jsdom/installer-module-ui.js`.
- Not tested: a real upload against a real instance, the browser's native file dialog, a real licence service, the FTP path.

## Other editions

MAE and PE replace SAE files of the same path, so the template, the controller script, the stylesheet and the translation catalog of this page may be overridden there (siberian01 runs MAE). A maintainer with access to the overlays must check them and port the same change; see [editions.md](editions.md).
