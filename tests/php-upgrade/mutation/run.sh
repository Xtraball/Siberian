#!/bin/sh
# Mutation score of one source file, using the tests of the custom runner through the PHPUnit bridge.
#
#   tests/php-upgrade/mutation/run.sh <tests> <source file name> [php version]
#   tests/php-upgrade/mutation/run.sh regression/ColorTest Color.php 8.5
#
# <tests> is a file of tests/php-upgrade (without .php) that needs no database; <source file name> is matched under
# siberian/lib/Siberian. Needs PHP 8.3+ (the image of 8.2 has no Infection). The report is printed, the details of the
# surviving mutants (the missing assertions) are in the text log printed at the end.
set -eu
cd "$(dirname "$0")/../../.."
TESTS=${1:?usage: run.sh <regression/ColorTest> <Color.php> [php version]}
SOURCE=${2:?usage: run.sh <regression/ColorTest> <Color.php> [php version]}
V=${3:-8.5}
docker build -q -t "siberian-ci:php$V" --build-arg "PHP_VERSION=$V" -f tests/php-upgrade/Dockerfile.ci tests/php-upgrade >/dev/null
docker run --rm -u "$(id -u):$(id -g)" -e HOME=/tmp -e "MUTATION_TESTS=$TESTS" -v "$PWD:/src" -w /src/tests/php-upgrade/mutation \
    "siberian-ci:php$V" sh -c "infection --configuration=infection.json5 --filter='$SOURCE' --threads=4 --no-progress --show-mutations=20; cp /tmp/infection.log /src/tests/php-upgrade/mutation/last-run.log"
echo "Surviving mutants: tests/php-upgrade/mutation/last-run.log"
