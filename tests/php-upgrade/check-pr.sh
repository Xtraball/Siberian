#!/bin/sh
# Checks a branch against the rules of AGENTS.md before opening a pull request.
#
#   tests/php-upgrade/check-pr.sh [--strict] [base-ref]     (default base: origin/trunk)
#
# Fails (exit 1) on: CRLF in added lines, new __() / p__() texts that are not in a .po catalog (new texts never go
# in the legacy csv files; a text that already lives in a csv of the base ref can be reused).
# Also fails on new use of Zend Framework 1 (any Zend_ class name): see "5." below and docs/decisions/0001-remove-zf1.md.
# Also fails on a change to a module's resources/db/schema or resources/db/data without a new package.json version of
# that module: see "6." below and AGENTS.md rule 9.
# Also fails on growth of a file listed in size-limits.txt, and on a new or longer PHP method over 150 lines (rule 10).
# Warns on: raw request values in arithmetic or a cast (use Siberian\Input), source changes without a test change, and fragile smells (see AGENTS.md) in added lines.
# --strict turns the warnings into failures. Only reads git, changes nothing.
#
#   tests/php-upgrade/check-pr.sh --zf1-list [ref]    prints the files that use Zend_ today (how zf1-allowlist.txt is made)
set -eu
cd "$(dirname "$0")/../.."

ZF1_LIST=tests/php-upgrade/zf1-allowlist.txt

# The source files the ZF1 rule applies to: ours, not the bundled framework, third-party code, data or tests.
zf1_in_scope() {
  case $1 in
    siberian/lib/Zend/*|siberian/lib/vendor/*|siberian/var/*|siberian/languages/*) return 1 ;;
  esac
  case $1 in
    siberian/*.php|siberian/*.phtml|siberian/*.inc) return 0 ;;
  esac
  return 1
}

# Number of lines of a file, at a ref, that name a Zend_ class and are not marked "ZF1-OK: why".
zf1_count() {
  git cat-file -e "$1:$2" 2>/dev/null || { echo 0; return; }
  git show "$1:$2" | grep -E 'Zend_[A-Za-z]' | grep -vc 'ZF1-OK' || true
}

if [ "${1:-}" = "--zf1-list" ]; then
  git grep -l -E 'Zend_[A-Za-z]' "${2:-HEAD}" -- 'siberian/*.php' 'siberian/*.phtml' 'siberian/*.inc' | sed 's/^[^:]*://' | sort |
    while IFS= read -r f; do
      zf1_in_scope "$f" || continue
      [ "$(zf1_count "${2:-HEAD}" "$f")" -gt 0 ] && echo "$f"
    done
  exit 0
fi

STRICT=0
if [ "${1:-}" = "--strict" ]; then STRICT=1; shift; fi
BASE=${1:-origin/trunk}
git rev-parse --verify --quiet "$BASE" >/dev/null || { echo "check-pr: unknown base ref $BASE" >&2; exit 2; }

FAIL=0
WARN=0
fail() { echo "FAIL  $*"; FAIL=$((FAIL + 1)); }
warn() { echo "WARN  $*"; WARN=$((WARN + 1)); }

SRC=$(mktemp)
ALL=$(mktemp)
trap 'rm -f "$SRC" "$ALL"' EXIT
# Added lines, third-party code and the catalogs themselves excluded for the source checks.
git diff -U0 "$BASE"...HEAD -- . ':!siberian/languages' ':!tests' ':!siberian/lib/vendor' ':!*.md' | grep '^+[^+]' > "$SRC" || true
git diff -U0 "$BASE"...HEAD | grep '^+[^+]' > "$ALL" || true

# 1. CRLF
if grep -q "$(printf '\r')" "$ALL"; then
  fail "CRLF line endings in added lines, LF only (git config core.autocrlf false, then convert the files back to LF)"
fi

# 2. New texts must be in the catalogs (English is the default, msgstr = msgid)
catalog_has() {
  text=$1
  grep -rqF -e "msgid \"$text\"" siberian/languages --include=*.po 2>/dev/null && return 0
  # Legacy csv: only a text that was already there on the base ref counts, a csv line added now does not.
  git grep -qF -e "\"$text\"" "$BASE" -- 'siberian/languages/*.csv' 2>/dev/null && return 0
  return 1
}
TEXTS=$(mktemp)
trap 'rm -f "$SRC" "$ALL" "$TEXTS"' EXIT
# __('text') and p__('ctx', 'text'), single or double quoted, one line.
sed -n -E "s/.*[^[:alnum:]_]p?__\((['\"])[A-Za-z_]+\1, *'(([^'\\\\]|\\\\.)*)'.*/\2/p; s/.*[^[:alnum:]_]__\('(([^'\\\\]|\\\\.)*)'.*/\1/p" "$SRC" | sed "s/\\\\'/'/g" | sort -u > "$TEXTS"
sed -n -E 's/.*[^[:alnum:]_]p?__\(("[A-Za-z_]+", *)?"(([^"\\]|\\.)*)".*/\2/p' "$SRC" | sed 's/\\"/\\"/g' | sort -u >> "$TEXTS"
while IFS= read -r text; do
  [ -n "$text" ] || continue
  catalog_has "$text" || fail "text not in a .po catalog (add it to siberian/languages/base/c_<context>.po): $text"
done < "$TEXTS"

# 3. A change to the product needs a test change
if [ -s "$SRC" ] && ! git diff --name-only "$BASE"...HEAD | grep -q '^tests/'; then
  warn "source changed but nothing under tests/ did (every fix carries a test that fails on trunk)"
fi

# 4. Fragile smells in added lines (advisory)
grep -nE '\|\| *true|^\+ *(usleep|sleep)\(|@\$|error_reporting\(0\)' "$SRC" | grep -v 'FRAGILE-OK' | while IFS= read -r line; do
  echo "WARN  fragile? $line"
done > /tmp/check-pr.$$ || true
if [ -s /tmp/check-pr.$$ ]; then cat /tmp/check-pr.$$; WARN=$((WARN + $(wc -l < /tmp/check-pr.$$))); fi
rm -f /tmp/check-pr.$$

# 5. No new use of Zend Framework 1. It is being removed (docs/decisions/0001-remove-zf1.md), so the number of Zend_
#    references in a file may go down, never up, and a file that does not use it today may not start.
#    tests/php-upgrade/zf1-allowlist.txt lists the files that use it today (remake it with --zf1-list on trunk).
#    A line that must name Zend_ (a facade that wraps it) carries "ZF1-OK: why" and is not counted.
MERGE_BASE=$(git merge-base "$BASE" HEAD)
ZOUT=$(mktemp)
git diff --name-status -M "$MERGE_BASE" HEAD | while IFS="$(printf '\t')" read -r status first second; do
  case $status in
    D*) continue ;;
    R*|C*) old=$first; new=$second ;;
    *) old=$first; new=$first ;;
  esac
  zf1_in_scope "$new" || continue
  now=$(zf1_count HEAD "$new")
  before=$(zf1_count "$MERGE_BASE" "$old")
  listed=0
  if [ -f "$ZF1_LIST" ] && { grep -qxF "$new" "$ZF1_LIST" || grep -qxF "$old" "$ZF1_LIST"; }; then listed=1; fi
  if [ "$now" -gt "$before" ]; then
    echo "FAIL  $new: $((now - before)) new Zend_ reference(s) (ZF1 is being removed: use a class of the Siberian namespace or plain PHP, or mark a facade line 'ZF1-OK: why', see docs/decisions/0001-remove-zf1.md)"
  elif [ "$now" -gt 0 ] && [ "$listed" -eq 0 ]; then
    echo "FAIL  $new uses Zend_ and is not in $ZF1_LIST (remake the list with check-pr.sh --zf1-list, see docs/decisions/0001-remove-zf1.md)"
  elif [ "$now" -eq 0 ] && [ "$listed" -eq 1 ]; then
    echo "WARN  $new has no Zend_ left: remove it from $ZF1_LIST"
  fi
done > "$ZOUT"
if [ -s "$ZOUT" ]; then
  cat "$ZOUT"
  FAIL=$((FAIL + $(grep -c '^FAIL' "$ZOUT" || true)))
  WARN=$((WARN + $(grep -c '^WARN' "$ZOUT" || true)))
fi
rm -f "$ZOUT"

# 6. A module's schema and data files are only played on an installation whose database has an older module version
#    than the package.json (Installer_Model_Installer_Module::canUpdate()). A change to resources/db/schema or
#    resources/db/data without a new "version" in the same module's package.json never reaches an installation that
#    updates (5.1.14 added application.android_edge_to_edge and Application stayed at 5.0.15: updated installs never got
#    the column). Deleted files do not count, nothing is played for them.
pkg_version() {
  git show "$1:$2" 2>/dev/null | grep -m1 -E '^[[:space:]]*"version"' |
    sed -E 's/.*"version"[[:space:]]*:[[:space:]]*"([^"]*)".*/\1/'
}
VOUT=$(mktemp)
git diff --name-only --diff-filter=ACMR "$MERGE_BASE" HEAD -- \
  'siberian/app/*/modules/*/resources/db/schema/*.php' 'siberian/app/*/modules/*/resources/db/data/*.php' |
  sed -E 's#/resources/db/.*##' | sort -u | while IFS= read -r module_dir; do
    before=$(pkg_version "$MERGE_BASE" "$module_dir/package.json")
    now=$(pkg_version HEAD "$module_dir/package.json")
    if [ -z "$now" ] || [ "$now" = "$before" ]; then
      echo "FAIL  $module_dir: schema/data files changed but package.json \"version\" did not (${now:-none}); raise it, or installations that update never play them (AGENTS.md rule 9)"
    fi
  done > "$VOUT"
if [ -s "$VOUT" ]; then
  cat "$VOUT"
  FAIL=$((FAIL + $(grep -c '^FAIL' "$VOUT" || true)))
fi
rm -f "$VOUT"

# 7. The biggest files do not grow. They are listed with their line count in tests/php-upgrade/size-limits.txt: a
#    change may make them smaller (lower the number then), never larger. New behaviour goes in a new class.
SIZE_LIST=tests/php-upgrade/size-limits.txt
if [ -f "$SIZE_LIST" ]; then
  git diff --name-only "$BASE"...HEAD | while IFS= read -r changed; do
    cap=$(awk -F'\t' -v f="$changed" '$1 == f { print $2 }' "$SIZE_LIST")
    [ -n "$cap" ] || continue
    [ -f "$changed" ] || continue
    now=$(wc -l < "$changed" | tr -d ' ')
    if [ "$now" -gt "$cap" ]; then
      echo "FAIL  $changed has $now lines, the limit is $cap (no net growth of the big files: put the new code in a new class, AGENTS.md rule 10)"
    elif [ "$now" -lt "$cap" ]; then
      echo "NOTE  $changed shrank to $now lines: lower its limit in $SIZE_LIST (was $cap)"
    fi
  done > /tmp/check-pr-size.$$ || true
  if [ -s /tmp/check-pr-size.$$ ]; then
    cat /tmp/check-pr-size.$$
    FAIL=$((FAIL + $(grep -c '^FAIL' /tmp/check-pr-size.$$ || true)))
  fi
  rm -f /tmp/check-pr-size.$$
fi

# 8. No new or longer method over METHOD_MAX lines in the product's PHP. A method that is already longer may stay as it
#    is or shrink; a new one, or one that grows, fails. Needs php (the CI image has it).
METHOD_MAX=150
METHOD_TOOL=tests/php-upgrade/tooling/method-lengths.php
CHANGED_PHP=$(git diff --name-only --diff-filter=AM "$BASE"...HEAD -- 'siberian/app/*.php' 'siberian/lib/Siberian/*.php' | grep -v -e '^siberian/lib/vendor/' -e '^siberian/var/' || true)
if [ -n "$CHANGED_PHP" ]; then
  if command -v php >/dev/null 2>&1; then
    MTMP=$(mktemp -d)
    for f in $CHANGED_PHP; do
      [ -f "$f" ] || continue
      php "$METHOD_TOOL" --min=$((METHOD_MAX + 1)) "$f" > "$MTMP/now" || true
      [ -s "$MTMP/now" ] || continue
      if git cat-file -e "$BASE:$f" 2>/dev/null; then
        git show "$BASE:$f" > "$MTMP/before.php"
        php "$METHOD_TOOL" "$MTMP/before.php" > "$MTMP/before" || true
      else
        : > "$MTMP/before"
      fi
      while IFS="$(printf '\t')" read -r len name _; do
        was=$(awk -F'\t' -v n="$name" '$2 == n && $1 > m { m = $1 } END { print m + 0 }' "$MTMP/before")
        if [ "$was" -eq 0 ]; then
          fail "$f: method $name is $len lines (new, the limit is $METHOD_MAX): split it (AGENTS.md rule 10)"
        elif [ "$len" -gt "$was" ]; then
          fail "$f: method $name grew from $was to $len lines (the limit is $METHOD_MAX): extract what you add (AGENTS.md rule 10)"
        fi
      done < "$MTMP/now"
    done
    rm -rf "$MTMP"
  else
    warn "php not found: the method length check (rule 10) was skipped"
  fi
fi

# 9. Input from outside is read through Siberian\Input (advisory): arithmetic or a cast on a raw request parameter is the
#    PHP 8 TypeError / division by zero that ten fixes patched one call site at a time (AGENTS.md rule 10).
grep -nE '(getParam|getPost|getQuery)\([^)]*\) *[-+*/%] |[-+*/%] *\$?[A-Za-z_>-]*(getParam|getPost|getQuery)\(|\((int|float|double)\) *\$[A-Za-z_>()-]*(getParam|getPost|getQuery)\(' "$SRC" | grep -v 'Input::' | while IFS= read -r line; do
  echo "WARN  raw request value in arithmetic or a cast, read it with Siberian\\Input::int/float/string: $line"
done > /tmp/check-pr-input.$$ || true
if [ -s /tmp/check-pr-input.$$ ]; then cat /tmp/check-pr-input.$$; WARN=$((WARN + $(wc -l < /tmp/check-pr-input.$$))); fi
rm -f /tmp/check-pr-input.$$

echo "check-pr: $FAIL failure(s), $WARN warning(s) against $BASE"
[ "$FAIL" -eq 0 ] || exit 1
[ "$STRICT" -eq 0 ] || [ "$WARN" -eq 0 ] || exit 1
exit 0
