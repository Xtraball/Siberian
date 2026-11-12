#!/bin/sh
# Runs every specs/**/*.spec.sh and sums them up. Usage: sh specs/run.sh [pattern]   e.g. sh specs/run.sh backoffice
# Target: SPEC_BASE / SPEC_EMAIL / SPEC_PASSWORD (see specs/lib.sh).
cd "$(dirname "$0")" || exit 1
FAILED=0
for f in $(find . -name "*${1:-}*.spec.sh" -o -path "*${1:-}*" -name '*.spec.sh' | sort -u); do
  echo "### $f"
  sh "$f" || FAILED=$((FAILED + 1))
done
echo
[ "$FAILED" -eq 0 ] && echo "all specs passed" || { echo "$FAILED spec file(s) failed"; exit 1; }
