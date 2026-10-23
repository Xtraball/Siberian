# PHP upgrade path

Branch: `chore/php-upgrade-path`. Target: **PHP 8.5** (8.5.12 is current as of 2026-10-23; there is no 8.6 image yet).
This document is a proposal. No application code has been changed on this branch; it only adds the safety net below.

## Where the project stands

| Place | PHP it assumes |
|---|---|
| `siberian/composer.json` | `"php": "8.2"` (an exact pin, not a range) |
| `docker/php/Dockerfile` | `php:7.3-fpm`, and `docker-compose.yml` mounts `/etc/php/7.3/...` |
| `Installer/Model/Installer/Module.php:283` | refuses modules below 7.3.0 |
| `Installer/Model/Installer.php:60`, `ci/scripts/requirements.php` | version checks for 5.6 / 7.0 |
| Bundled Zend Framework 1 (`siberian/lib/Zend`, 41 MB) and `siberian/lib/vendor` (committed, 40 MB) | written for PHP 5/7 |

So the docker image and the manifest already disagree by a full major version, and the code runs on 8.2 only as far as nobody has complained.

## What was measured

`tests/php-upgrade/ci.sh <ver>` on 4,262 first-party PHP files (vendor excluded, AdminGenerator `{{placeholder}}` templates excluded):

| | PHP 8.2 | PHP 8.5 |
|---|---|---|
| Files that fail to compile | 0 | 0 |
| Deprecation notices | 46 | 1,202 |
| Regression suite (19 tests) | green | green |

The 8.5 notices are almost all mechanical:

| Count | Notice | Fix |
|---|---|---|
| 347 / 222 / 10 | non-canonical casts `(boolean)` `(integer)` `(double)` | rewrite to `(bool)` `(int)` `(float)` |
| 141 | `case X;` terminated with a semicolon | use `:` |
| 435 | implicit nullable parameters (`Type $x = null`, deprecated in 8.4), spread across first-party code and `lib/vendor` (guzzle promises, scssphp, onesignal) | `?Type $x = null`, or upgrade the library |
| 8 | `${var}` interpolation (8.2) | `{$var}` |
| 27 | ZF1 `Zend_Reflection_*` and iterator return types (8.1) | add `#[\ReturnTypeWillChange]` or real return types |
| ~4 | other (e.g. optional parameter before a required one) | by hand |

Full per-kind counts: `tests/php-upgrade/baseline/deprecations-*.detail.txt`.

Caveat: `php -l` only sees compile-time problems. Runtime breakage (removed functions, changed behaviour of `strtolower` and friends, dynamic properties on 8.2+, stricter internal-function types) is not detected by linting. The 19 regression tests cover Zend and the vendor libraries' core paths, not the 4,000 application files, which have no tests at all.

## Proposed path

One minor version per step, each step merged only when `ci.sh <that version>` is green.

0. **This branch.** Gate and baselines land first, on today's code.
1. **Fix the manifest and the image (8.2, no behaviour change).** `composer.json` php → `">=8.2 <8.6"`, regenerate the lock hash, rebuild `docker/php/Dockerfile` on `php:8.2-fpm` using `tests/php-upgrade/Dockerfile.ci` as the extension list (it already lists what composer requires; the old file also builds `xmlrpc`, gone from core in 8.0, and `imap`, gone in 8.4; check whether anything uses them before dropping). Drop the 7.3/5.6/7.0 version checks.
2. **8.3.** Expected to be a no-op. Add `8.3` to the CI matrix, set its baseline.
3. **8.4.** Implicit-nullable parameters become the main cost. Fix first-party ones with a codemod (Rector `ExplicitNullableParamTypeRector`); for `lib/vendor`, prefer bumping the library over patching it.
4. **8.5.** Casts and `case;` fixes (Rector or `phpcbf`, fully mechanical). Then drive the ratchet to 0.
5. **Pay down the frozen dependencies** (separate effort, can start any time): symfony 3, stripe-php 7, phpseclib 2, endroid/qrcode 2, goutte (abandoned), `reisraff/phulp`. These are the likeliest runtime failures and the reason the regression suite covers them.
6. **Longer term:** Zend Framework 1 has no maintainer upstream. Options are to keep the bundled copy and own the patches, or to migrate off it; the latter is a project, not a step.

Ratchet rule: after each step, rerun `ci.sh <ver> --update-baseline` and commit it. The count may only fall.

## Tests

```
tests/php-upgrade/ci.sh 8.5            # builds the image, runs every stage
tests/php-upgrade/ci.sh 8.5 --vendor   # also compiles the committed vendor tree
tests/php-upgrade/ci.sh 8.5 --skip=manifest
tests/php-upgrade/ci.sh 8.5 --update-baseline
```

Fast-fail stages, cheapest first, stopping at the first failure:

1. `extensions`: every `ext-*` in composer.json is loaded in that PHP.
2. `manifest`: composer.json parses and `php` is a range, not an exact pin. **This fails today by design** (step 1 above fixes it). Use `--skip=manifest` until then.
3. `syntax`: `php -l` on first-party code; any parse or fatal error fails.
4. `ratchet`: deprecation count must not exceed `baseline/deprecations-<ver>.txt`.
5. `regression`: `tests/php-upgrade/run.php`, plain PHP with no phpunit dependency. Add tests as `regression/*Test.php` returning `['name' => callable]`.

Not covered: anything needing a database or the booted application. A bootable docker-compose smoke test (install wizard through to a login page on each version) is the natural next layer.
