#!/bin/bash
# usage: run.sh [playwright args]   (e2e dir is /srv/data/pw-editor/e2e)
cd /srv/data/pw-editor/e2e
[ -d node_modules/@playwright ] || npm install --no-audit --no-fund 2>&1 | tail -2
mkdir -p results
docker run --rm --network host --ipc=host -u 1000:1000 -v "$PWD":/e2e -w /e2e mcr.microsoft.com/playwright:v1.63.0-noble npx playwright test "$@" 2>&1 | tail -60
