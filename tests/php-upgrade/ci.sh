#!/bin/sh
# Host-side entry point: build the CI image for one PHP version and run the gate in it.
#
#   tests/php-upgrade/ci.sh 8.5 [--update-baseline] [--vendor]
set -eu
cd "$(dirname "$0")/../.."
V=${1:?usage: ci.sh <php-version e.g. 8.5> [fast-fail options]}
shift
docker build -q -t "siberian-ci:php$V" --build-arg "PHP_VERSION=$V" -f tests/php-upgrade/Dockerfile.ci tests/php-upgrade >/dev/null
exec docker run --rm -u "$(id -u):$(id -g)" -e HOME=/tmp -v "$PWD:/src" -w /src "siberian-ci:php$V" sh tests/php-upgrade/fast-fail.sh "$@"
