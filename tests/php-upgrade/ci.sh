#!/bin/sh
# Host-side entry point: build the CI image for one PHP version and run the gate in it.
#
#   tests/php-upgrade/ci.sh 8.5 [--update-baseline] [--vendor] [--no-db]
#
# Tier 1 tests (tests/php-upgrade/db) need a database server. This script starts a throwaway MariaDB on a private
# network and removes it on exit. --no-db skips it (the db stage then reports "skip"). Set SIB_TEST_DB_HOST yourself
# to use a server you already run.
set -eu
cd "$(dirname "$0")/../.."
V=${1:?usage: ci.sh <php-version e.g. 8.5> [fast-fail options]}
shift
docker build -q -t "siberian-ci:php$V" --build-arg "PHP_VERSION=$V" -f tests/php-upgrade/Dockerfile.ci tests/php-upgrade >/dev/null

NODB=0
for a in "$@"; do [ "$a" = "--no-db" ] && NODB=1; done

NET=""
DBHOST=${SIB_TEST_DB_HOST:-}
if [ "$NODB" = 0 ] && [ -z "$DBHOST" ]; then
    SUFFIX="$$"
    NET="sibci-net-$SUFFIX"
    DBHOST="sibci-db-$SUFFIX"
    docker network create "$NET" >/dev/null
    trap 'docker rm -f "$DBHOST" >/dev/null 2>&1; docker network rm "$NET" >/dev/null 2>&1' EXIT INT TERM
    docker run -d --name "$DBHOST" --network "$NET" --tmpfs /var/lib/mysql \
        -e MARIADB_ROOT_PASSWORD=root mariadb:11.8 >/dev/null
    ready=0
    for _ in $(seq 1 60); do
        if docker exec "$DBHOST" mariadb-admin -uroot -proot ping >/dev/null 2>&1; then ready=1; break; fi
        docker exec "$DBHOST" true >/dev/null 2>&1 || break
        sleep 1
    done
    [ "$ready" = 1 ] || { echo "FAIL [db] the MariaDB container did not become ready"; exit 1; }
fi

set +e
docker run --rm ${NET:+--network "$NET"} ${DBHOST:+-e "SIB_TEST_DB_HOST=$DBHOST"} \
    -u "$(id -u):$(id -g)" -e HOME=/tmp -v "$PWD:/src" -w /src "siberian-ci:php$V" sh tests/php-upgrade/fast-fail.sh "$@"
rc=$?
exit $rc
