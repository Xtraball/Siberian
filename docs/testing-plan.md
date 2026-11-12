# Testing plan

Status: proposal. Goal: tests that catch the defects we actually ship, not a coverage number.

## Where we are (measured 2026-11-12, trunk)

- `tests/php-upgrade/regression`: 118 files, about 1,930 assertions, PHP 8.2 to 8.5 in `bin/ci`.
- 104 of the 118 files read repository source (`file_get_contents` then `strpos`). Only 13 load and run a real class.
  A source-text test proves a string exists, not that the code works, and it breaks when code is moved without a
  change of behaviour (the #524 test failed when the install loop moved into a shared class).
- Nothing runs against a database. The upgrade path (schema sync, data files, `canUpdate()`), which caused the
  5.1.14 and 5.1.18 fixes, has no executable test.
- `lib/Siberian`: 174 files, 32,900 lines. 50 files (about 5,200 lines) are never mentioned by a test; the biggest
  ones that matter are `Db/Migration/Table.php` (tested indirectly at best), `Security.php`, `Cron/Human.php`,
  `Network.php`, `Cache/Design.php`, `Ftp.php`.
- Modules: 121 private repos, 117 have a CI file, about 8 have real tests (enterprisepayment 66 files, xdelivery 29,
  classifieds 20, a few with 2 to 4).
- The AGENTS.md rule "each fix PR has a test that fails on trunk" works and stays. This plan is about what we add
  beyond it.

## What is worth testing

Test where a defect is expensive, or where the logic has many branches and few people understand it:

1. **Money, identity, access**: payments, licence checks, password and token comparisons, ACL and controller access,
   2FA, session and customer lookups.
2. **Upgrade and install**: schema sync, data files, module version gates, the platform update (web and CLI). A bug
   here breaks every customer on the next release and cannot be hot-fixed on their servers.
3. **Pure logic with branches**: parsers and formatters (durations, numbers, colours, cron expressions, locale
   packs, placeholders), anything that takes app input and does arithmetic.
4. **Boundaries to the outside**: HTTP clients, push, FTP, update server. Tested against a fake server, not mocks of
   our own code.
5. **Output contracts**: the JSON the mobile app reads, the generated manifests. Golden files, few and deliberate.

Not worth testing: getters and setters, ZF1 glue, form markup, translations (the `.po` lint covers them), one-line
wrappers, and source text. A test that cannot fail when the behaviour breaks is removed, not kept.

## Tiers

| Tier | What | Runs | Tooling |
| --- | --- | --- | --- |
| 0 | Pure unit: one class, no DB, no network | every PR, seconds | PHP 8.2 to 8.5 matrix |
| 1 | Class against a real MariaDB: schema sync, installer, Customer and Application queries | every PR, under 2 minutes | MariaDB container, fixtures made with `php cli` and the public API |
| 2 | HTTP contract: the mobile and backoffice endpoints on an installed instance | every PR, a handful of tests | `php -S` router + the tier 1 DB, fake update/licence/push server |
| 3 | Upgrade rehearsal: install the previous release, update to the candidate with `php cli update`, assert versions, schema, data, login | before each release | the staging rig used for #1071 (`/srv/data/cli-stage`) made reproducible |

## Quality gates (so value is measured, not assumed)

- **Mutation testing** (Infection) on tier 0 and tier 1 code under `lib/Siberian`: a surviving mutant is a missing
  assertion. The score of the files we touch is the metric, not line coverage. Coverage (pcov) is a map of blind
  spots, never a target.
- **Behaviour, not structure**: new tests call the public method and assert results or side effects. No `strpos`
  on source, no assertion on private method names.
- **Characterization first** for old big classes (Pure, Color, Assets, Layout, Template): record today's output for
  representative inputs as golden files, then refactor behind them. These do not claim the output is right, only
  that it did not change.
- **Test or removal when touching**: a PR that changes a source-text test either rewrites it as a behaviour test or
  deletes it with a reason.
- **PHPUnit for new tests** (data providers, expected exceptions, Infection support); keep the current runner for the
  existing files until they are rewritten. Pin PHPUnit through the CI image, not `composer.json` of the product.

## Phase 1: lib/Siberian and around (priority order)

| # | Target | Why | Tier |
| --- | --- | --- | --- |
| 1 | `Db/Migration/Table.php`, `Installer_Model_Installer_Module` (`canUpdate`, `install`, `insertData`), `ModuleUpdates` | upgrade correctness; rule 9; idempotent schema and data; a module at the same version is not replayed | 1 |
| 2 | `Security.php`, password and token helpers, `Customer` session and `session_uuid` queries, ACL and `Backoffice` access | identity; HY093 and `hash_equals` class of bugs | 0, 1 |
| 3 | Controller access matrix: every backoffice and API controller action has an explicit access rule (see `security.md`) | a route pattern alone is never enough; one test lists the actions and fails on a new unguarded one | 1 |
| 4 | `Request`, `Network`, `Ftp`, `Update/Platform`, `Update/Cli`, push senders | timeouts, status handling, error paths, against a fake server | 0 |
| 5 | `Cron`, `Cron/Human`, `Cron/Callback` | scheduling expressions and due-time arithmetic | 0 |
| 6 | `Locale`, `Locale/Packs`, `Translation`, gettext placeholders | placeholder and escaping bugs reached users | 0 |
| 7 | Number, date, duration and colour helpers (`Color`, `Pure`, `Currency`, the PHP 8 TypeError class) | arithmetic on app input; property tests with random inputs | 0 |
| 8 | `Mail`, `StoreAccount`, `License`, `Hook`, `CircuitBreaker` | external effects and licence rules | 0, 1 |
| 9 | `Assets`, `Layout`, `Template`, `Minify`, `Cache/Design` | golden files, characterization only | 0 |
| 10 | Mobile JSON contract: login, register, error cleaning (`ClientMessage`), push registration | what the apps depend on; the 5.1.16 and 5.1.17 incident | 2 |
| 11 | Upgrade rehearsal (tier 3) | what we did by hand for 5.1.17 and #1071 | 3 |

Exit criteria: tier 1 harness merged with items 1 to 2; each later item merged with its tests and the mutation score
of its files recorded; every source-text test touched is rewritten or deleted.

## Phase 2: modules

Two layers, so effort follows risk:

1. **One generic contract test for all 121 modules** (the best value per effort): against a fresh trunk
   (`SIBERIAN_SRC`, no stubs) install the module, run it twice (idempotent schema and data), load every class (the
   quarantine probe already does this in a separate process), uninstall or quarantine cleanly. This catches the
   failures that happen to customers (PHP 8 compile errors, missing core method, version gates) for the whole
   catalogue at once. Ship it as a shared script in the module CI template.
2. **Real tests for the modules that touch money, identity or data loss**, in this order: enterprisepayment (extend the
   66 files it has), xdelivery (orders, payments, delivery), commercepro, appointmentpro, twofactorauth, cabride,
   queuemodule, importexportcustomer. Same tiers as the core; a module `bin/ci` runs them against `SIBERIAN_SRC`.
   The other modules get tests only when a bug fix needs one.

Do not backfill tests for the 100+ low-risk modules (layout, template, display-only features).

## Decisions to take

1. PHPUnit and Infection in the CI image for new tests (recommended), or keep the custom runner only.
2. Tier 1 needs a MariaDB container in `bin/ci`; the CI image already needs Docker, so the cost is run time (about a
   minute).
3. Whether the tier 3 rehearsal blocks `release-flow.sh` (recommended: yes, as a dry-run stage) or stays advisory.
4. Whether to delete source-text tests that duplicate a behaviour test, or keep them until a rewrite.
