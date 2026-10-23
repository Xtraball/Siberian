#!/bin/sh
# Happy paths for the non-PHP dependencies that carry advisories (axios in the
# node CLI, xcodeproj/rexml in the iOS patch scripts). Host-side, uses docker.
#
#   tests/php-upgrade/tooling.sh [--record]   --record rewrites the golden hash
set -eu
cd "$(dirname "$0")/../.."
GOLDEN=tests/php-upgrade/tooling/golden/xcodeproj-alter.sha256

# axios: install exactly the range package.json declares, then drive it.
SPEC=$(sed -n 's/.*"axios": *"\([^"]*\)".*/\1/p' package.json)
docker run --rm -u "$(id -u):$(id -g)" -e HOME=/tmp -v "$PWD:/src:ro" node:22-slim sh -c "
    mkdir /tmp/ax && cd /tmp/ax && npm install --silent --no-audit --no-fund --no-package-lock axios@'$SPEC' >/dev/null &&
    cp /src/tests/php-upgrade/tooling/axios_happy_path.js . && node axios_happy_path.js"

# xcodeproj: bundle the repo's own Gemfile.lock, run the patch on the real project.
OUT=$(docker run --rm -u "$(id -u):$(id -g)" -e HOME=/tmp -v "$PWD:/src:ro" ruby:3.3-slim sh -c "
    mkdir /tmp/w && cp -r /src/bin/scripts/. /tmp/w/ && cd /tmp/w &&
    bundle config set --local path /tmp/bundle >/dev/null &&
    bundle install --quiet >/dev/null &&
    bundle exec ruby /src/tests/php-upgrade/tooling/xcodeproj_happy_path.rb /src /tmp/w &&
    bundle exec ruby -e 'puts \"VERSIONS \" + %w[xcodeproj rexml].map { |g| g + \" \" + Gem.loaded_specs[g].version.to_s }.join(\", \")' ")
echo "$OUT" | grep VERSIONS
HASH=$(echo "$OUT" | sed -n 's/^SHA256 //p')
if [ "${1:-}" = "--record" ]; then
    mkdir -p "$(dirname "$GOLDEN")"; echo "$HASH" > "$GOLDEN"; echo "recorded $HASH"
elif [ "$HASH" = "$(cat "$GOLDEN")" ]; then
    echo "ok   xcodeproj output identical to golden ($HASH)"
else
    echo "FAIL xcodeproj output changed: $HASH != $(cat "$GOLDEN")"; exit 1
fi
