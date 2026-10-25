#!/bin/sh
# Generic controller contracts for every endpoint of specs/manifest.tsv, per session context, in parallel.
#
#   sh specs/contracts.sh backoffice [pattern]     (contexts: backoffice editor mobile api installer public)
#   SPEC_WORKERS=3 (default) workers share one pre-built session; the instance must be the isolated rig (specs/rig/apply.sh)
#
# Three cases per endpoint:
#   unauth   no session at all: never a server error, never a PHP error on the page, never a success payload
#   invalid  the right session, input that cannot be valid ({} body or zero ids): a clean answer, never a server
#            error, a PHP error on the page, or no response at all (a rendered page where JSON is expected)
#   valid    the right session and acceptable input. Read-only list/load/find actions run on their own; the rest come
#            from specs/valid/<context>.tsv: url TAB METHOD TAB body-or-query TAB expected-status TAB jq-check ("-" = none)
# specs/skip.tsv (url TAB reason): endpoints that would end the shared session or change the instance, never called.
# specs/html.tsv (url TAB reason): endpoints that legitimately answer a page or a file, so the JSON rule does not apply.
# specs/known.tsv (url TAB case TAB reason): a failure that is documented, reported as "known" instead of failing.
# The session is built once; every worker probes it every 20 calls and rebuilds it if an endpoint ended it.
. "$(dirname "$0")/lib.sh"
CTX=${1:?usage: contracts.sh <context> [pattern]}
PATTERN=${2:-}
HERE=$(cd "$(dirname "$0")" && pwd)
WORKERS=${SPEC_WORKERS:-3}
TMO=${SPEC_TIMEOUT:-25}
BAD_PAGE='Fatal error|Parse error|Uncaught |Stack trace|(Warning|Notice|Deprecated): .* on line [0-9]+|Undefined (index|variable|array key|offset|property)'
RESULTS=${SPEC_RESULTS:-/tmp/contracts-$CTX.tsv}
WORK=$(mktemp -d /tmp/contracts.XXXXXX)
MASTER=$WORK/master.jar
START=$(date +%s)

build_session() {
  case "$CTX" in
    backoffice) SPEC_JAR=$MASTER; login ;;
    editor) SPEC_JAR=$MASTER; editor_login ;;
    *) : > "$MASTER" ;;
  esac
}

# call JAR METHOD URL [BODY] -> CODE, BODY. The JAR is only read: a call never changes the shared session's cookies.
call() {
  _j=$1; _m=$2; _u=$3; _b=${4-}
  [ "$_b" = - ] && _b=
  if [ "$_m" = POST ]; then
    [ -n "$_b" ] || _b='{}'
    BODY=$(curl -s -m "$TMO" -o - -w '\n%{http_code}' -X POST -H 'X-Requested-With: XMLHttpRequest' -H 'Content-Type: application/json' \
      -b "$_j" -d "$_b" "$SPEC_BASE$_u")
  else
    BODY=$(curl -s -m "$TMO" -o - -w '\n%{http_code}' -X GET -H 'X-Requested-With: XMLHttpRequest' -b "$_j" "$SPEC_BASE$_u${_b:+?$_b}")
  fi
  CODE=$(printf '%s' "$BODY" | tail -n 1)
  BODY=$(printf '%s' "$BODY" | sed '$d')
}

skipped() { grep -q "^$1	" "$HERE/skip.tsv" 2>/dev/null; }

clean() {
  if [ "$CODE" = 000 ]; then echo "no answer within ${TMO}s"; return; fi
  if [ "$CODE" -ge 500 ] 2>/dev/null; then echo "HTTP $CODE"; return; fi
  if printf '%s' "$BODY" | grep -Eq "$BAD_PAGE"; then echo "PHP error in the answer: $(printf '%s' "$BODY" | grep -Eo "$BAD_PAGE" | head -1)"; return; fi
  echo ok
}

# worker FILE_OF_ROWS OUT ID (row: kind TAB url TAB verb TAB body TAB want TAB check)
worker() {
  rows=$1; out=$2; mine=$WORK/jar.$3; noauth=$WORK/noauth.$3
  : > "$noauth"; n=0
  while IFS="	" read -r kind url verb body want check; do
    n=$((n + 1))
    if [ $((n % 20)) -eq 0 ] && [ "$CTX" = backoffice ]; then
      cp "$MASTER" "$mine"
      call "$mine" GET /backoffice/account_list/load
      if ! printf '%s' "$BODY" | jq -e .title >/dev/null 2>&1; then
        echo "session lost before $url, rebuilt" >> "$WORK/session.log"
        flock "$WORK/login.lock" sh -c ". '$HERE/lib.sh'; SPEC_JAR='$MASTER'; SPEC_BASE='$SPEC_BASE'; SPEC_EMAIL='$SPEC_EMAIL'; SPEC_PASSWORD='$SPEC_PASSWORD'; login" >/dev/null
      fi
    fi
    cp "$MASTER" "$mine"
    case "$kind" in
      unauth)
        call "$noauth" "$verb" "$url"
        v=$(clean)
        if [ "$v" = ok ] && printf '%s' "$BODY" | grep -Eq '"success": *(1|true)'; then v="success payload without a session"; fi
        ;;
      invalid)
        if [ "$verb" = POST ]; then call "$mine" POST "$url" '{}'; else call "$mine" GET "$url" 'id=0&value_id=0&app_id=0&user_id=0&page=0&limit=0'; fi
        v=$(clean)
        if [ "$v" = ok ] && [ "$CODE" = 404 ]; then v="404 on an existing route (no response from the action)"; fi
        if [ "$v" = ok ] && [ "$CTX" = backoffice ] && ! grep -q "^$url	" "$HERE/html.tsv" 2>/dev/null && ! printf '%s' "$BODY" | jq -e . >/dev/null 2>&1; then v="answers a page, not JSON (the action sent no response)"; fi
        if [ "$v" = ok ] && [ "$verb" = POST ] && printf '%s' "$BODY" | grep -Eq '"success": *(1|true)'; then v="reports success for an empty request"; fi
        ;;
      valid)
        call "$mine" "$verb" "$url" "$body"
        v=$(clean)
        if [ "$v" = ok ] && [ "$CODE" != "$want" ]; then v="expected HTTP $want, got $CODE"; fi
        if [ "$v" = ok ] && [ -n "$check" ] && [ "$check" != - ] && [ "$(printf '%s' "$BODY" | jq -r "$check" 2>/dev/null)" != true ]; then v="check failed: $check"; fi
        ;;
    esac
    printf '%s\t%s\t%s\n' "$url" "$kind" "$v" >> "$out"
  done < "$rows"
}

build_session
# the rows: unauth + invalid for every endpoint, valid for the read-only ones and those in valid/<ctx>.tsv
: > "$WORK/rows"
grep -P "\t$CTX\t" "$HERE/manifest.tsv" | grep -F "$PATTERN" | while IFS="	" read -r mod url ctx verb base; do
  skipped "$url" && continue
  printf 'unauth\t%s\t%s\t-\t-\t-\n' "$url" "$verb"
  printf 'invalid\t%s\t%s\t-\t-\t-\n' "$url" "$verb"
  case "$verb:$url" in
    GET:*/load|GET:*/find|GET:*/findall|GET:*/list|GET:*/index|GET:*/template|GET:*/findbyadmin|GET:*/loadlogs)
      printf 'valid\t%s\tGET\t-\t200\t-\n' "$url" ;;
  esac
done >> "$WORK/rows"
[ -f "$HERE/valid/$CTX.tsv" ] && grep -v '^#' "$HERE/valid/$CTX.tsv" | grep -F "$PATTERN" | while IFS="	" read -r url verb body want check; do
  skipped "$url" || printf 'valid\t%s\t%s\t%s\t%s\t%s\n' "$url" "$verb" "$body" "$want" "$check"
done >> "$WORK/rows"

# split round-robin so slow endpoints spread over the workers, then run them side by side
i=0; while [ "$i" -lt "$WORKERS" ]; do : > "$WORK/part.$i"; : > "$WORK/out.$i"; i=$((i + 1)); done
awk -v n="$WORKERS" -v d="$WORK" '{ print > (d "/part." ((NR - 1) % n)) }' "$WORK/rows"
i=0
while [ "$i" -lt "$WORKERS" ]; do
  worker "$WORK/part.$i" "$WORK/out.$i" "$i" &
  i=$((i + 1))
done
wait

cat "$WORK"/out.* | sort > "$RESULTS"
PASS=0; FAIL=0; KNOWN=0
while IFS="	" read -r url kind v; do
  if [ "$v" = ok ]; then PASS=$((PASS + 1))
  elif grep -q "^$url	$kind	" "$HERE/known.tsv" 2>/dev/null; then KNOWN=$((KNOWN + 1)); echo "  known $url [$kind]: $v"
  else FAIL=$((FAIL + 1)); echo "  FAIL $url [$kind]: $v"; fi
done < "$RESULTS"
[ -f "$WORK/session.log" ] && { echo "  note: the shared session ended during the run:"; sed 's/^/    /' "$WORK/session.log"; }
URLS=$(cut -f1 "$RESULTS" | sort -u | wc -l)
VALIDS=$(awk -F'\t' '$2=="valid"{print $1}' "$RESULTS" | sort -u | wc -l)
echo "== $CTX: $URLS endpoints, $VALIDS with a valid case; $PASS passed, $FAIL failed, $KNOWN known; $(( $(date +%s) - START ))s with $WORKERS workers"
rm -rf "$WORK"
[ "$FAIL" -eq 0 ]
