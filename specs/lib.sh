#!/bin/sh
# Shared helpers for the controller specs. Sourced by every *.spec.sh, never run alone.
#
# Target (defaults match tests/e2e-module-updates, an installed throwaway instance):
#   SPEC_BASE      http://127.0.0.1:28800
#   SPEC_EMAIL     admin@example.com
#   SPEC_PASSWORD  Passw0rd!x
# The specs create and delete their own records; they never touch a user or row they did not create, but still point
# them at a test instance, not at production.

SPEC_BASE=${SPEC_BASE:-http://127.0.0.1:28800}
SPEC_EMAIL=${SPEC_EMAIL:-admin@example.com}
SPEC_PASSWORD=${SPEC_PASSWORD:-Passw0rd!x}
SPEC_JAR=${SPEC_JAR:-$(mktemp /tmp/spec.XXXXXX)}
SPEC_PASS=0
SPEC_FAIL=0
CODE=
BODY=

spec_name() { echo "== $1"; }

# request METHOD PATH [JSON_BODY]   -> sets CODE (HTTP status) and BODY. Uses the session in $SPEC_JAR.
request() {
  _m=$1; _p=$2; _b=${3-}
  if [ -n "$_b" ]; then
    BODY=$(curl -s -m ${SPEC_TIMEOUT:-15} -o - -w '\n%{http_code}' -X "$_m" -H 'X-Requested-With: XMLHttpRequest' -H 'Content-Type: application/json' \
      -b "$SPEC_JAR" -c "$SPEC_JAR" -d "$_b" "$SPEC_BASE$_p")
  else
    BODY=$(curl -s -m ${SPEC_TIMEOUT:-15} -o - -w '\n%{http_code}' -X "$_m" -H 'X-Requested-With: XMLHttpRequest' -H 'Content-Type: application/json' \
      -b "$SPEC_JAR" -c "$SPEC_JAR" "$SPEC_BASE$_p")
  fi
  CODE=$(printf '%s' "$BODY" | tail -n 1)
  BODY=$(printf '%s' "$BODY" | sed '$d')
}

# json FILTER -> value of a jq filter on $BODY ("" when absent)
json() { printf '%s' "$BODY" | jq -r "$1 // empty" 2>/dev/null; }

login() {
  : > "$SPEC_JAR"
  request POST /backoffice/account_login/post "{\"email\":\"$SPEC_EMAIL\",\"password\":\"$SPEC_PASSWORD\"}"
  [ "$(json .success)" = 1 ] || { echo "cannot log in as $SPEC_EMAIL on $SPEC_BASE ($CODE)"; exit 2; }
}

pass() { SPEC_PASS=$((SPEC_PASS + 1)); echo "  ok   $1"; }
fail() { SPEC_FAIL=$((SPEC_FAIL + 1)); echo "  FAIL $1 ($2)"; }
expect_eq() { if [ "$2" = "$3" ]; then pass "$1"; else fail "$1" "expected [$2] got [$3]"; fi; }
# expect_status "label" 200   -> checks $CODE
expect_status() { expect_eq "$1: HTTP status" "$2" "$CODE"; }
# expect_json "label" FILTER VALUE -> checks a field of $BODY
expect_json() { expect_eq "$1: $2" "$3" "$(json "$2")"; }

spec_done() {
  echo "  -> $SPEC_PASS passed, $SPEC_FAIL failed"
  rm -f "$SPEC_JAR"
  [ "$SPEC_FAIL" -eq 0 ]
}
