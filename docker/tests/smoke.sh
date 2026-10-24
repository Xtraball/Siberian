#!/bin/bash
# Boot smoke test: drives the web installer of a running docker/docker-compose.yml stack from the first page
# to the end (database, every module, admin user, first application), then checks the login page answers.
#
#   cd docker && cp .env.example .env   # passwords set
#   docker compose up -d --build --wait
#   tests/smoke.sh                       # SIBERIAN_URL defaults to http://localhost:${SIBERIAN_PORT:-8080}
#
# Exit 0 = installed and login reachable. Prints one line per step, the failing response on error.
set -euo pipefail

cd "$(dirname "$0")/.."
caller_port=${SIBERIAN_PORT:-}
[ -f .env ] && set -a && . ./.env && set +a
SIBERIAN_PORT=${caller_port:-${SIBERIAN_PORT:-8080}}
URL="${SIBERIAN_URL:-http://localhost:${SIBERIAN_PORT:-8080}}"
DB_HOST="${SMOKE_DB_HOST:-db}"
ADMIN_EMAIL="${SMOKE_ADMIN_EMAIL:-admin@example.com}"
ADMIN_PASSWORD="${SMOKE_ADMIN_PASSWORD:-smoke-$(head -c 9 /dev/urandom | base64 | tr -dc 'a-zA-Z0-9')}"
JAR="$(mktemp)"
trap 'rm -f "$JAR"' EXIT

step() { printf '%-34s' "$1"; }
ok() { echo "ok"; }
fail() { echo "FAILED"; echo "--- $1" >&2; exit 1; }

# post <path> [curl -d args...]: JSON endpoint, must answer {"success":...}
post() {
    local path=$1; shift
    local out
    out=$(curl -sS --max-time 600 -H 'X-Requested-With: XMLHttpRequest' -b "$JAR" -c "$JAR" -w '\n%{http_code}' "$@" "$URL/$path") || fail "curl $path"
    local code=${out##*$'\n'} body=${out%$'\n'*}
    [[ $code == 200 && $body == *'"success"'* ]] || fail "$path -> HTTP $code: ${body:0:400}"
}

step "installer page"
page=$(curl -sSL --max-time 60 -b "$JAR" -c "$JAR" "$URL/installer/installation") || fail "curl installer"
[[ $page == *'installer.setPlugins('* ]] || fail "no installer on $URL/installer/installation: ${page:0:300}"
modules=$(sed -n 's/.*installer\.setPlugins(\[\(.*\)\]).*/\1/p' <<<"$page" | tr -d '"' | tr ',' ' ')
[ -n "$modules" ] || fail "empty module list"
ok

step "database connection"
post installer/installation_database/check \
    --data-urlencode "host=$DB_HOST" --data-urlencode "dbname=${MARIADB_DATABASE:-siberian}" \
    --data-urlencode "username=${MARIADB_USER:-siberian}" --data-urlencode "password=${MARIADB_PASSWORD:?}"
ok

for phase in install insertdata; do
    step "modules: $phase ($(wc -w <<<"$modules"))"
    for m in $modules; do
        post "installer/installation_database/$phase" --data-urlencode "name=$m"
    done
    ok
done

step "cron scheduler"
curl -sS --max-time 120 -b "$JAR" -c "$JAR" "$URL/installer/installation_database/cronscheduler" -o /dev/null || fail "cronscheduler"
ok

step "admin user"
post installer/installation_user/create \
    --data-urlencode "email=$ADMIN_EMAIL" --data-urlencode "password=$ADMIN_PASSWORD" --data-urlencode "confirm_password=$ADMIN_PASSWORD"
ok

step "first application"
post installer/installation_application/create --data-urlencode "name=Smoke test app"
ok

step "end of installation"
end=$(curl -sS --max-time 120 -H 'X-Requested-With: XMLHttpRequest' -b "$JAR" -c "$JAR" "$URL/installer/installation/end")
[[ $end == *'"success"'* ]] || fail "installation/end: ${end:0:300}"
ok

step "installed flag"
# the site root serves the installer until isInstalled = "1", then the front office
home=$(curl -sSL --max-time 60 -b "$JAR" "$URL/")
[[ $home != *"installer.setPlugins("* ]] || fail "root still serves the installer after the end"
ok

step "login page"
login=$(curl -sS -L --max-time 60 -b "$JAR" -c "$JAR" -w '\n%{http_code}' "$URL/backoffice")
code=${login##*$'\n'}
[[ $code == 200 && $login == *'password'* ]] || fail "backoffice login page -> HTTP $code: ${login:0:300}"
ok

echo "SMOKE PASSED ($URL)"
