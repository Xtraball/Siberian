#!/bin/sh
# Checks a branch against the rules of AGENTS.md before opening a pull request.
#
#   tests/php-upgrade/check-pr.sh [--strict] [base-ref]     (default base: origin/trunk)
#
# Fails (exit 1) on: CRLF in added lines, new __() / p__() texts that are not in a .po catalog (new texts never go
# in the legacy csv files; a text that already lives in a csv of the base ref can be reused).
# Warns on: source changes without a test change, and fragile smells (see AGENTS.md) in added lines.
# --strict turns the warnings into failures. Only reads git, changes nothing.
set -eu
cd "$(dirname "$0")/../.."

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

echo "check-pr: $FAIL failure(s), $WARN warning(s) against $BASE"
[ "$FAIL" -eq 0 ] || exit 1
[ "$STRICT" -eq 0 ] || [ "$WARN" -eq 0 ] || exit 1
exit 0
