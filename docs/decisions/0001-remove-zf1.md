# 0001 Remove Zend Framework 1 from Siberian

Date: 2026-11-06. Status: proposed, accepted when this pull request is merged.

## Context

Siberian is built on Zend Framework 1 (ZF1), bundled in `siberian/lib/Zend`: 2518 PHP files, 41 MB on disk. ZF1 has been dead for years. The bundled copy was patched and stretched to run on current PHP (8.2 to 8.5 now), every PHP release costs another round of deprecation fixes, and the framework travels in every update package.

What depends on it (measured on `trunk` on 2026-11-06, outside `siberian/lib/Zend` and `siberian/lib/vendor`; "files" are files that name a `Zend_` class, "refs" are occurrences):

| Area | Files | Refs |
|---|---|---|
| `Zend_Db` (adapter, table, row, rowset, select, expr, exceptions) | 92 | 259 |
| `Zend_Form` (+ `Zend_View`, `Zend_Layout`) | 78 (+11, +20) | 254 (+30, +31) |
| `Zend_Registry` | 63 | 108 |
| `Zend_Date` | 53 | 121 |
| `Zend_Controller` (front controller, actions, dispatch) | 41 | 99 |
| `Zend_Validate` | 38 | 73 |
| `Zend_Session` | 28 | 69 |
| `Zend_Cache` | 24 | 43 |
| `Zend_Locale` | 23 | 48 |
| `Zend_Exception` | many | 356 |
| `Zend_Json` | many | 207 |
| `Zend_Debug`, `Zend_Uri`, `Zend_Log`, `Zend_Currency`, `Zend_Mail`, `Zend_Http`, `Zend_Config`, `Zend_Loader` | few each | under 50 each |

In total 491 of the roughly 1600 core PHP files name a `Zend_` class (the list is `tests/php-upgrade/zf1-allowlist.txt`, remade with `tests/php-upgrade/check-pr.sh --zf1-list`). `siberian/app/Bootstrap.php` and `BootstrapCron.php` extend `Zend_Application_Bootstrap_Bootstrap`. About 160 core classes sit on `Core_Model_Db_Table` / `Zend_Db_Table_Abstract`.

Modules are not clean either. In the one module collection we could scan (`siberian-modules`, 250 PHP files) 113 files name a `Zend_` class, mostly `Zend_Db` (191 refs), `Zend_Exception` (138), `Zend_Form` (55) and `Zend_Date` (38). The module SDK was meant to be `Siberian\`, in practice modules reach through to ZF1. We have not scanned the marketplace modules yet, and ciphered modules cannot be read at all.

`lib/Siberian` already holds classes for several areas (`Date`, `Json`, `Exception`, `Locale`, `Cache`, `Mail`, `Layout`, `Log`, `Session`, `Db/*`, `Form/*`, `Controller/*`). They are partial: most still call ZF1 inside (`Date` wraps `Zend_Date`, `Json` wraps `Zend_Json`, `Mail` has 22 ZF1 references, 273 ZF1 lines in `Form`, `Controller`, `Layout` and `View`). They are the starting point of the facades, not a finished layer.

## Decision

1. **ZF1 is removed from Siberian, completely, with no compatibility layer in core.** `siberian/lib/Zend` is deleted in the end.
2. **It ships as a major version, 6.0.** Modules that name `Zend_` classes break there, and the release says so (outline below).
3. **Method: a facade per area, in the `Siberian\` namespace.** For each area we (a) write or complete the `Siberian\` class with the contract the code really uses, first as a thin wrapper around ZF1 (lines that must name `Zend_` say `ZF1-OK: why`), (b) move core and modules onto it, (c) swap the implementation underneath, (d) delete the ZF1 classes of that area. Steps (a) and (b) change no behaviour and can ship in 5.x; (c) and (d) are 6.0.
4. **No big bang.** One area per pull request series, in the order below, each with tests that pass on the old and the new implementation before the swap.
5. **The count of `Zend_` references may only go down.** `tests/php-upgrade/check-pr.sh` fails a branch that adds one (AGENTS.md rule 8). That guard is a separate pull request (the one that adds `zf1-allowlist.txt`); its failure message points to this file.
6. **A separate compat package is an option, not part of this decision.** See "Optional compat package" below. Core stays free of it.

## Order and layers

Cheapest and most mechanical first, the framework core last.

| Step | Areas | Replacement (to confirm in the step) | Measure before |
|---|---|---|---|
| 1 | `Zend_Exception` and the `Zend_*_Exception` family, `Zend_Registry`, `Zend_Json`, `Zend_Uri`, `Zend_Debug`, `Zend_Log` | `\Exception` subclasses under `Siberian\`, a `Siberian\Registry` container, `json_encode`/`json_decode` with `JSON_THROW_ON_ERROR`, `parse_url`/a small Uri value class, `Siberian\Log` | Which exception types are caught in core and modules (a catch of a `Zend_*_Exception` must keep working through the step); what is stored in the Registry and who reads it |
| 2 | `Zend_Date`, `Zend_Locale`, `Zend_Currency` | `DateTimeImmutable`, `IntlDateFormatter`, `NumberFormatter` behind `Siberian\Date`, `Siberian\Locale`, `Siberian\Currency` | Every format string passed to `Zend_Date` (its tokens differ from `date()` and ICU), the locale data files read, `ext-intl` present on supported installs |
| 3 | `Zend_Cache`, `Zend_Session`, `Zend_Validate`, `Zend_Mail`, `Zend_Http`, `Zend_Config`, `Zend_Loader` | `Siberian\Cache` (PSR cache is already required), native sessions over the storage used today (to check), small validators, one mail library and one HTTP client (decide, a new dependency has a cost) | Cache backends and tags in use, session lifetime and cookie settings, which validators and filters forms rely on, mail transports in use |
| 4 | `Zend_Db` | `Siberian\Db` over PDO, see [siberian-db-facade.md](../zf1-removal/siberian-db-facade.md) | The query surface (done for the design, redo before the swap), SQL differences, types returned by PDO |
| 5 | `Zend_Form`, `Zend_View`, `Zend_Layout`, `Zend_Controller`, `Zend_Application` (bootstrap) | `Siberian\Form` (already started), `Siberian\View`, a small router and dispatcher that keeps the controller class contracts (`<Module>_<Controller>Controller`, `*Action`, `getRequest()`, `_forward`, helpers), a plain bootstrap | Which action helpers, plugins and `Zend_Controller_Plugin_*` are used, how the three entry points (web, cron, CLI) boot, every form decorator in templates |
| 6 | Delete `siberian/lib/Zend`, the allowlist and the `ZF1-OK` markers | | The tree contains no `Zend_` and the guard has nothing left to guard |

Steps 1 to 3 can run in parallel with each other. Step 4 needs step 1 (exceptions). Step 5 needs steps 3 (session, validate) and 4. A step is done when its allowlist entries are gone and the guard shows no `Zend_` of that area.

## Components or our own code

Recommendation of 2026-11-07, to confirm in each step. The rule: **adopt a maintained component where the problem is generic or security-sensitive, write thin code of our own where the shape is Siberian's.** Either way the code under `siberian/` and the modules name the `Siberian\` facade only, so the choice behind a facade can change without touching them.

We do **not** adopt a framework (Symfony HttpKernel, bundles, the container): that would swap one framework for another and rewrite the application, which is not what this decision is about. We also do not adopt Twig: `.phtml` templates are the module API and stay.

| Area | Choice | Why |
|---|---|---|
| Mail | `symfony/mailer` | Security-sensitive, already solved, do not hand-roll |
| HTTP client | `symfony/http-client` (Guzzle is already in the vendor tree, one of the two, not both) | Same |
| Cache | `symfony/cache` (PSR-6/16) | Standalone, clean drop-in |
| Log | `monolog` (PSR-3) | The standard choice |
| Session | Native PHP sessions behind `Siberian\Session` | `Zend_Session` is mostly a wrapper |
| Validate | `symfony/validator`, or small validators of our own if the 38 core uses stay simple | Decide in step 3 from the measured uses |
| Date, Locale, Currency | Native `DateTimeImmutable`, `IntlDateFormatter`, `NumberFormatter` | No library needed; the format tokens need the differential test |
| Exception, Json, Registry, Uri, Debug, Config, Loader | Native PHP or tiny classes of our own | Search-and-replace sized |
| `Db` | **Our own** thin `Siberian\Db` over PDO, keeping the `select()` / `fetchRow()` / `insert()` shape | Siberian runs on MariaDB only, so the portability of Doctrine DBAL buys little; DBAL stays possible under the facade later |
| `Form` | **Our own** | `Siberian_Form_*` is already our Bootstrap layer; Symfony Form is heavy and built around Twig |
| `View`, `Layout` | **Our own** minimal PHP renderer keeping the phtml helper contract | Protects every module template |
| `Controller`, bootstrap | **Our own** small router and dispatcher keeping `module/controller/action` | Symfony Routing has no convention routing; the dispatch is a few hundred lines |

The module scan (2026-11-06) shows why the last four rows stay our own: modules reach ZF1 mostly **through our base classes** (`Core_Model_Default`, `Siberian_Form_*`, `*_Controller_*`), 1157 files in 96 modules against 341 files that name `Zend_` directly. Those base classes are the real module API, so their contracts must survive the swap.

If components are adopted, pin the Symfony 7.4 LTS line, which runs on PHP 8.2. Symfony 8 needs PHP 8.4 and would raise the minimum again. The vendor tree is committed, so each component adds to the update package; measure it against the 41 MB removed before adding one (listed under "Update size" in the risks).

## Facade contracts

A facade is the contract the code **really uses**, not the whole ZF1 API. Before a facade is written:

- measure the surface used by core and by the module collections (method names, constructor options, static calls, `instanceof` and `catch` types, string class names in config and `ini` files);
- write the contract as a short list, and a test for each item that runs against the old and the new implementation;
- name what is not kept, so a module author can tell from the note what to change.

The rule for the code under `siberian/` is: name the facade, never the ZF1 class. Where a type has to be named while the wrapper still extends ZF1, the line carries `ZF1-OK: why`.

## Risks

- **Modules break.** Third-party modules that name `Zend_` classes fail on 6.0. We do not know how many: the marketplace modules have not been scanned and ciphered modules cannot be read. The scan is the first piece of work; its result decides whether the optional compat package is worth building.
- **Ciphered modules cannot be inspected or fixed by us.** Their authors have to move. This is a communication task as much as a technical one.
- **The front controller (step 5) is the riskiest.** Every request goes through it, and modules rely on its conventions (class naming, action names, `_forward`, view rendering, layouts). It is last on purpose, and its replacement keeps those contracts instead of redesigning them.
- **Forms.** 78 core files and every template that renders a form. Decorators are a second API that templates depend on.
- **Dates and locales.** `Zend_Date` format tokens are not `date()` tokens. A wrong conversion changes what users see, silently.
- **Database types and quoting.** The swap must return the same PHP types and generate equivalent SQL. See the facade design for the tests.
- **Update size and install time.** Removing 41 MB changes update packages and the installer, which is good, but the installer and the update path themselves use ZF1 and must keep working at every step. Not measured yet: compressed size of `lib/Zend` in a package.
- **Other editions override some files**, so every step must be checked against them (read [editions.md](../editions.md), and follow AGENTS.md rule 6).
- **PHP 8.5 and later.** A removed framework is also a smaller surface for the next PHP release, but the work competes with ordinary releases. One area at a time keeps releases possible in between.

## Release 6.0 and the migration note

Outline, to be filled as the steps land:

1. What changes for a store owner or an agency running Siberian: nothing visible, a smaller package, PHP 8.2 minimum (8.5 recommended).
2. What changes for module authors: the `Zend_` classes are gone. A table from each ZF1 class to its `Siberian\` replacement, built from the facades as they are written, with before and after code.
3. Which versions of a module are 6.0-ready, and how the module downloader marks a module that is not (field in the catalog, shown in the back office before an update, never an update that breaks a running app).
4. How to check a module before updating: a scanner (the one used for the module inventory) published for module authors.
5. Dates: a 5.x release that ships all non-behavioural facade steps and the scanner first, 6.0 after the modules had time to move.

## Optional compat package

A separate package, outside the Siberian repository and outside core, that puts the old `Zend_` class names back for modules that cannot be ported (for example ciphered modules whose authors are gone). It would carry the bundled ZF1 as it is on the last 5.x release, load only when an operator installs it, and a module would declare that it needs it. Siberian core would never require or load it.

This costs ongoing work (PHP compatibility of a dead framework, support requests) and keeps the old attack surface alive on the servers that install it. The decision is the owner's and is not taken here. What decides it: the result of the module scan (how many modules, how many installs, how many ciphered), and whether the authors of the most used modules will port.

## What must be measured before each step

- Before any step: the files and references of the area (`check-pr.sh --zf1-list`, then count by area), the surface of its API used in core and in the module collections, the tests that pin the current behaviour (so the swap is judged against them, not against memory).
- Before a swap: a differential test that runs the old and the new implementation on the same inputs and compares results. For `Db`, SQL text and returned rows; for `Date`, formatted strings over a table of formats and locales; for the router, the resolved class and action for every route of the product.
- After a swap: the full suite on every supported PHP version, an install from scratch, an update from the previous release, a cron run, and the editor and back office opened with the browser console watched (AGENTS.md).

## Consequences

- The guard makes the problem stop growing from the day it is merged.
- A visible roadmap: each step is a pull request series with a clear end, and the count of `Zend_` files is the progress bar.
- 6.0 is a breaking release for some modules, announced in advance, with a scanner.
- Until step 6, `siberian/lib/Zend` stays and keeps getting PHP compatibility fixes where a step has not reached its area yet.
