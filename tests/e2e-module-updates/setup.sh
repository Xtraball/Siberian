#!/bin/sh
# Builds the e2e rig in ~/sibtest on a dev box. Idempotent. Usage: sh setup.sh
set -eu
cd "$(dirname "$0")"
SIB=${SIB_SRC:-/home/claude/siberian-module-updates/siberian}

# 1. private CA and a server certificate for the three test hostnames
mkdir -p certs
if [ ! -f certs/ca.pem ]; then
  openssl req -x509 -newkey rsa:2048 -nodes -keyout certs/ca.key -out certs/ca.pem -days 30 -subj "/CN=sibtest CA" 2>/dev/null
  openssl req -newkey rsa:2048 -nodes -keyout certs/server.key -out certs/server.csr -subj "/CN=downloader.test" 2>/dev/null
  printf 'subjectAltName=DNS:downloader.test,DNS:services.test,DNS:store.test\n' > certs/ext.cnf
  openssl x509 -req -in certs/server.csr -CA certs/ca.pem -CAkey certs/ca.key -CAcreateserial -out certs/server.pem -days 30 -extfile certs/ext.cnf 2>/dev/null
  chmod 644 certs/*
fi

# 2. Siberian user config: only the config file may redirect the update client
cat > "$SIB/config.user.php" <<'PHP'
<?php
$_config['module_updates_base_url'] = 'https://downloader.test';
PHP
grep -q 'config.user.php' "$(git -C "$SIB" rev-parse --git-dir)/info/exclude" 2>/dev/null || {
  mkdir -p "$(git -C "$SIB" rev-parse --git-dir)/info"
  printf 'siberian/config.user.php\nsiberian/config.php\nsiberian/app/configs/app.ini\nsiberian/var/\nsiberian/app/local/\n' >> "$(git -C "$SIB" rev-parse --git-dir)/info/exclude"
}

# 3. downloader config (licence services point at the fakes)
mkdir -p downloader/var
cat > downloader/var/config.php <<'PHP'
<?php
return [
    'registry_path' => '/app/var/fixture/modules.json',
    'dist_dir' => '/app/var/fixture/dist',
    'license_service_url' => 'https://services.test/api/external/license/check',
    'edd_url' => 'https://store.test/',
];
PHP
echo setup ok
