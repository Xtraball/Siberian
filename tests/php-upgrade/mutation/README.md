# Mutation testing

Infection changes the code of a source file one small step at a time (a `+` becomes a `-`, a condition is negated) and
runs the tests. A mutant that no test fails on is a missing assertion. The score of the files we touch is the metric,
not line coverage (docs/testing-plan.md, quality gates).

```
tests/php-upgrade/mutation/run.sh regression/ColorTest Color.php 8.5
```

- The CI image carries `infection` (PHP 8.3+ only), `phpunit` and `pcov`, pinned by checksum in `Dockerfile.ci`.
- `RunnerBridgeTest.php` turns every closure of one custom-runner test file into a PHPUnit case, so the existing
  tests are the test suite for Infection. Only files that need no database.
- Read "Covered Code MSI". Surviving mutants are listed in `last-run.log` (git-ignored).
- First measure (2026-11-12): `Color.php` with `ColorTest`, covered MSI 69% (565 killed, 250 escaped).
