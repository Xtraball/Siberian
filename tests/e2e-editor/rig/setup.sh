#!/bin/bash
# Own copy of the e2e rig for editor visual tests: SAE source of the PR checkout, own DB, port 28850.
set -e
W=/srv/data/pw-editor
mkdir -p $W
[ -d $W/rig ] || cp -a /srv/data/sibmae82/rig $W/rig
cd $W/rig
sed -i 's/^name: sibmae82/name: pweditor/; s/28840/28850/g' docker-compose.yml
sed -i 's#SIB_SRC:-/srv/data/sibmae82/siberian#SIB_SRC:-/srv/data/pw-editor/siberian#' docker-compose.yml
sed -i 's/28840/28850/; s/sibmae82/pweditor/' install.sh
if [ ! -d $W/siberian ]; then
  cp -a /srv/data/sib-pr/rel519/siberian $W/siberian
  cd $W/siberian
  cp lib/Siberian/Version.sample.php lib/Siberian/Version.php
  sed -i "s/const TYPE = '[A-Z]*';/const TYPE = 'SAE';/" lib/Siberian/Version.php
  cp config.sample.php config.php
  cp /srv/data/sibmae82/siberian/config.user.php config.user.php
  cat > config.php <<'PHP'
<?php

$_config = [];
$_config['environment'] = 'production';
$_config['redirect_https'] = false;

try {
    if (is_file(__DIR__ . '/config.user.php')) {
        require __DIR__ . '/config.user.php';
    }
} catch (\Exception $e) {
}
PHP
  grep -n "const TYPE\|const VERSION" lib/Siberian/Version.php
fi
cd $W/rig
docker compose up -d --build 2>&1 | tail -8
docker compose ps --format '{{.Name}} {{.Status}}'
