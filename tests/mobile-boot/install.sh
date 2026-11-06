#!/bin/bash
# Installs a throw-away Siberian SAE through its web installer (like the browser wizard does), then generates the
# source of the first app (Android by default, TYPE=iosnoads for iOS) through the same cron task as the back office "Generate sources" button.
#
#   install.sh <siberian-dir> <base-url> <db-host> <package-name> <device-host> <out-zip-copy>
#
# <base-url>    where this script reaches the running instance, e.g. http://127.0.0.1:28900
# <device-host> the host:port the generated app will call, e.g. 10.0.2.2:28900 (emulator to the runner)
#
# Environment: TYPE (android | iosnoads, default android), MYSQL (client binary, default mysql).
set -eu
SIB=$1; B=$2; DBH=$3; PKG=$4; DEVHOST=$5; OUT=$6
TYPE=${TYPE:-android}
MYSQL=${MYSQL:-mysql}

J=$(mktemp); c() { curl -s -H "X-Requested-With: XMLHttpRequest" -b "$J" -c "$J" "$@"; }
ok() { case "$1" in *'"success":1'*|*'"success": true'*) return 0;; *) return 1;; esac; }
sql() { "$MYSQL" -h"$DBH" -uroot -proot siberian -N -e "$1"; }

plugins=$(c "$B/" | grep -o 'setPlugins(\[[^]]*\]' | sed 's/setPlugins(\[//; s/\]//; s/"//g' | tr ',' ' ')
echo "installer modules: $(echo "$plugins" | wc -w)"
[ -n "$plugins" ] || { echo "installer page did not list modules"; exit 1; }

out=$(c -X POST -d "host=$DBH&dbname=siberian&username=root&password=root" "$B/installer/installation_database/check")
ok "$out" || { echo "db check failed: $out"; exit 1; }

for pass in install insertdata; do
  for m in $plugins; do
    out=$(c "$B/installer/installation_database/$pass?name=$m")
    ok "$out" || { echo "$pass $m failed: $out" | head -c 400; echo; exit 1; }
  done
done
ok "$(c "$B/installer/installation_database/cronscheduler")" || { echo "cron scheduler failed"; exit 1; }
ok "$(c -X POST -d 'email=admin@example.com&password=Passw0rd!x&confirm_password=Passw0rd!x' "$B/installer/installation_user/create")" || { echo "admin failed"; exit 1; }
ok "$(c -X POST -d 'name=BootTest' "$B/installer/installation_application/create")" || { echo "app failed"; exit 1; }
ok "$(c "$B/installer/installation/end")" || { echo "installer end failed"; exit 1; }
echo "installed, app: $(sql 'select app_id, name from application')"

# A fresh checkout has an empty dist/templates.js in every app shell. The installer schedules the compilation of the
# templates (core, modules, features) for the cron (Tools::RESTORE_APP_SOURCES); there is no cron here, so run the
# same step by hand with `cli app:manifest`. Without it the app cannot open any page.
sql "update system_config set value='${B#http://}' where code='main_domain'"
(cd "$SIB" && php cli app:manifest 2>&1 | tail -2)
case "$TYPE" in
  android) TPL="$SIB/var/apps/ionic/android/app/src/main/assets/www/dist/templates.js";;
  iosnoads) TPL="$SIB/var/apps/ionic/ios-noads/www/dist/templates.js";;
  *) echo "unknown TYPE $TYPE"; exit 1;;
esac
test "$(wc -c < "$TPL")" -gt 100000 || { echo "$TPL is still empty"; exit 1; }

# SAE serves its single app at /overview (Application_Model_Application::getKey()); Android and iOS need a dotted package / bundle id.
sql "update application set package_name='$PKG', bundle_id='$PKG' where app_id=1"

# An app without an uploaded icon makes res/values/themes.xml point at @mipmap/ic_splash, which is then never written
# (getAndroidSplashIcon() returns inline image data, ionicResources() only copies readable files), so the build fails.
# Give the test app an icon like a real one has. (Fixed in Siberian by Xtraball/Siberian#1047; drop this once merged.)
mkdir -p "$SIB/images/application/boottest"
cp "$SIB/images/application/placeholder/no-image.png" "$SIB/images/application/boottest/icon.png"
sql "update application set icon='/boottest/icon.png' where app_id=1"
sql "insert into source_queue (name,url,path,app_id,protocol,host,type,design_code,user_id,user_type,status,build_time,build_start_time,is_autopublish,log,created_at,updated_at) values ('BootTest','','',1,'https://','$DEVHOST','$TYPE','ionic',1,'admin','queued',0,0,0,'',now(),now())"

(cd "$SIB" && php cron.php runcommand sources 2>&1 | grep -v sendmail | tail -5)
status=$(sql 'select status from source_queue where source_queue_id=1')
[ "$status" = success ] || { echo "source generation ended as: $status"; sql 'select log from source_queue'; exit 1; }
zip=$(sql 'select path from source_queue where source_queue_id=1')
# path is stored as seen by the generating process; here that is the same filesystem
cp "$zip" "$OUT"
echo "source zip: $(ls -l "$OUT" | awk '{print $5}') bytes"
