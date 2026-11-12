#!/bin/sh
# Drives the Siberian web installer with curl, like the browser wizard does. Usage: sh install-siberian.sh
set -u
B=http://127.0.0.1:28800
J=/tmp/sib.cookies
c() { curl -s -H "X-Requested-With: XMLHttpRequest" -b $J -c $J "$@"; }

plugins=$(c "$B/" | grep -o 'setPlugins(\[[^]]*\]' | sed 's/setPlugins(\[//; s/\]//; s/"//g' | tr ',' ' ')
echo "modules: $(echo $plugins | wc -w)"

echo "db check:"
c -X POST -d 'host=db&dbname=siberian&username=root&password=root' "$B/installer/installation_database/check"; echo

for pass in install insertdata; do
  echo "== $pass"
  for m in $plugins; do
    out=$(c "$B/installer/installation_database/$pass?name=$m")
    case "$out" in *'"success":1'*) ;; *) echo "  $m -> $out" | head -c 400; echo;; esac
  done
done

echo "cron:"; c "$B/installer/installation_database/cronscheduler"; echo
echo "license:"; c "$B/installer/installation/savelicense?key=sk-valid-0001"; echo
echo "admin:"
c -X POST -d 'email=admin@example.test&password=Passw0rd!x&confirm_password=Passw0rd!x' "$B/installer/installation_user/create"; echo
echo "end:"; c "$B/installer/installation/end"; echo
