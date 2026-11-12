#!/bin/sh
# Installs DemoUpd 1.0.0 in Siberian the regular way (login, upload, copy, install) and gives it a licence key.
set -u
B=http://127.0.0.1:28800
J=/tmp/sib.admin
c() { curl -s -H "X-Requested-With: XMLHttpRequest" -b $J -c $J "$@"; }
W=$(mktemp -d)

mkdir -p $W/pkg/resources
cat > $W/pkg/package.json <<'JSON'
{
  "name": "DemoUpd",
  "description": "E2E test module",
  "type": "module",
  "version": "1.0.0",
  "code": "demoupd",
  "item_id": 900001,
  "dependencies": {"system": {"type": "SAE", "version": "5.0.0"}}
}
JSON
printf '<?php\n// e2e module\n' > $W/pkg/init.php
printf "<?php\nreturn 'hello from DemoUpd 1.0.0';\n" > $W/pkg/resources/hello.php
docker run --rm --user $(id -u) -v $W:/w sibtest-dl sh -c "cd /w/pkg && zip -q -r ../DemoUpd-1.0.0.sib ./"

echo "login:"; rm -f $J
c -X POST -H "Content-Type: application/json" -d '{"email":"admin@example.com","password":"Passw0rd!x"}' $B/backoffice/account_login/post | head -c 120; echo

echo "upload:"
up=$(c -F "file=@$W/DemoUpd-1.0.0.sib" $B/installer/backoffice_module/upload); echo "$up" | head -c 600; echo
fn=$(echo "$up" | grep -o '"filename": *"[^"]*"' | sed 's/.*: *"//; s/"//')
echo "copy:"; c "$B/installer/backoffice_module/copy?file=$fn"; echo
echo "install:"; c "$B/installer/backoffice_module/install" | head -c 400; echo
