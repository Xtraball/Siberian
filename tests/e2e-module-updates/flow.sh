#!/bin/sh
# End-to-end module update flow against the sibtest rig. Usage: sh flow.sh   (after setup.sh, install-siberian.sh, seed-module.sh)
set -u
cd "$(dirname "$0")"
B=http://127.0.0.1:28800/installer/backoffice_module
J=/tmp/sib.admin
PASS=0
FAIL=0
c() { curl -s -H "X-Requested-With: XMLHttpRequest" -b $J -c $J "$@"; }
sql() { docker compose exec -T db mariadb -uroot -proot siberian -N -e "$1"; }
dl() { docker compose exec -T dl "$@"; }
ok() { PASS=$((PASS + 1)); echo "  ok   $1"; }
bad() { FAIL=$((FAIL + 1)); echo "  FAIL $1 ($2)"; }
eq() { if [ "$2" = "$3" ]; then ok "$1"; else bad "$1" "expected [$2] got [$3]"; fi; }

login() {
  rm -f $J
  c -X POST -H "Content-Type: application/json" -d '{"email":"admin@example.com","password":"Passw0rd!x"}' http://127.0.0.1:28800/backoffice/account_login/post >/dev/null
}

echo "== setup"
login
PUB=$(docker compose exec -T siberian php -r 'echo file_get_contents("https://downloader.test/api/v1/keys.json");' | jq -r '.keys[0].public')
[ -n "$PUB" ] && ok "downloader serves its public key" || bad "public key" "empty"
upsert() { sql "DELETE FROM system_config WHERE code='$1'; INSERT INTO system_config (code,label,value) VALUES ('$1','$1','$2')"; }
upsert demoupd_key lk-valid-0001
upsert toonew_key lk-valid-0002
upsert module_updates_enabled 1
sql "DELETE FROM system_config WHERE code IN ('module_updates_terms_hash','module_updates_public_keys')"
sql "DELETE FROM module WHERE code='toonew'; INSERT INTO module (name,code,version,use_license,can_uninstall,is_enabled,type,created_at,updated_at) VALUES ('TooNew','toonew','0.5.0',1,1,1,'module',NOW(),NOW())"
rm -f ../siberian-module-updates/siberian/var/cache/module-updates.json

echo "== 1. downloads are refused until a key is pinned"
r=$(c -X POST "$B/updatemodule?code=demoupd")
echo "$r" | head -c 200 | tr -d '\n'; echo
# terms are not accepted yet, so the first answer is terms_required; pin the key and continue
eq "terms required first" terms_required "$(echo "$r" | jq -r .code)"

upsert module_updates_public_keys "{\"k1\":\"$PUB\"}"

echo "== 2. list"
r=$(c "$B/checkupdates?force=1")
eq "list ok" true "$(echo "$r" | jq -r .success)"
eq "one installed module has an update" "demoupd,toonew" "$(echo "$r" | jq -r '[.updates[].code] | join(",")')"
eq "demoupd installed 1.0.0" 1.0.0 "$(echo "$r" | jq -r '.updates[] | select(.code=="demoupd") | .installed')"
eq "demoupd available 1.1.0" 1.1.0 "$(echo "$r" | jq -r '.updates[] | select(.code=="demoupd") | .available')"
eq "demoupd compatible" true "$(echo "$r" | jq -r '.updates[] | select(.code=="demoupd") | .compatible')"
eq "toonew incompatible" false "$(echo "$r" | jq -r '.updates[] | select(.code=="toonew") | .compatible')"
eq "newmod (not installed) not offered" 0 "$(echo "$r" | jq '[.updates[] | select(.code=="newmod")] | length')"
eq "terms not accepted yet" false "$(echo "$r" | jq -r .terms_accepted)"
H1=$(echo "$r" | jq -r .terms_hash)

echo "== 3. terms"
r=$(c "$B/terms")
eq "terms served" "$H1" "$(echo "$r" | jq -r .terms.hash)"
r=$(c -X POST "$B/acceptterms?hash=not-the-hash")
eq "wrong hash refused" terms_changed "$(echo "$r" | jq -r .code)"
r=$(c "$B/acceptterms?hash=$H1")
eq "accept needs POST" bad_request "$(echo "$r" | jq -r .code)"
r=$(c -X POST "$B/acceptterms?hash=$H1")
eq "accepted" true "$(echo "$r" | jq -r .success)"
eq "acceptance stored" "$H1" "$(sql "SELECT value FROM system_config WHERE code='module_updates_terms_hash'")"
eq "who accepted is stored" 1 "$(sql "SELECT value FROM system_config WHERE code='module_updates_terms_accepted_by'")"

echo "== 4. refusals"
r=$(c -X POST "$B/updatemodule?code=toonew")
eq "incompatible module refused" siberian_too_old "$(echo "$r" | jq -r .code)"
r=$(c -X POST "$B/updatemodule?code=newmod")
eq "module without update refused" not_found "$(echo "$r" | jq -r .code)"
r=$(c "$B/updatemodule?code=demoupd")
eq "update needs POST" bad_request "$(echo "$r" | jq -r .code)"
upsert demoupd_key lk-bad-0001
r=$(c -X POST "$B/updatemodule?code=demoupd")
eq "bad module licence refused by the downloader" module_license "$(echo "$r" | jq -r .code)"
echo "     message: $(echo "$r" | jq -r .message)"
upsert demoupd_key lk-used-0001
r=$(c -X POST "$B/updatemodule?code=demoupd")
eq "licence used elsewhere refused" module_license "$(echo "$r" | jq -r .code)"
upsert demoupd_key lk-valid-0001

echo "== 5. terms change while the admin is on the page"
dl sh -c "printf '\n- A new rule added during the e2e test.\n' >> terms/terms.md && php scripts/build-manifest.php" >/dev/null
r=$(c -X POST "$B/updatemodule?code=demoupd")
eq "downloader answers terms_changed (409)" terms_changed "$(echo "$r" | jq -r .code)"
H2=$(echo "$r" | jq -r '.terms.hash // empty')
[ -n "$H2" ] && [ "$H2" != "$H1" ] && ok "new terms returned with the error" || bad "new terms" "[$H2]"
eq "old acceptance forgotten" "" "$(sql "SELECT IFNULL(value,'') FROM system_config WHERE code='module_updates_terms_hash'")"
r=$(c -X POST "$B/acceptterms?hash=$H2")
eq "new terms accepted" true "$(echo "$r" | jq -r .success)"

echo "== 6. the update"
r=$(c -X POST "$B/updatemodule?code=demoupd")
eq "package accepted" true "$(echo "$r" | jq -r .success)"
eq "package is DemoUpd 1.1.0" "DemoUpd 1.1.0" "$(echo "$r" | jq -r '.package_details._name + " " + .package_details.version')"
eq "package carries the code" demoupd "$(echo "$r" | jq -r .package_details.code)"
FN=$(echo "$r" | jq -r .filename)
r=$(c "$B/copy?file=$FN")
eq "copy ok" 1 "$(echo "$r" | jq -r .success)"
r=$(c "$B/install")
echo "$r" | head -c 200 | tr -d '\n'; echo
eq "installed version is 1.1.0" 1.1.0 "$(sql "SELECT version FROM module WHERE code='demoupd'")"
SIB=${SIB_SRC:-/home/claude/siberian-module-updates/siberian}
eq "module files are in place" 1 "$(ls $SIB/app/local/modules/DemoUpd/package.json >/dev/null 2>&1 && echo 1 || echo 0)"
grep -q "__dcRun\|eval" $SIB/app/local/modules/DemoUpd/resources/hello.php && ok "module php is the encrypted loader stub" || bad "stub" "plain php installed"
docker compose exec -T siberian php -r '
$f = "/srv/sib/app/local/modules/DemoUpd/resources/hello.ec.php";
$fc = file_get_contents($f); $iv = substr($fc, 0, 16);
$k = openssl_digest("sk-valid-0001" . "lk-valid-0001", "sha256");
$plain = openssl_decrypt(substr($fc, 16), "aes-256-cbc", $k, OPENSSL_RAW_DATA, $iv);
echo strpos((string)$plain, "hello from DemoUpd 1.1.0") !== false ? "DECRYPT-OK" : "DECRYPT-FAIL";' | grep -q DECRYPT-OK && ok "payload decrypts with this Siberian's licence pair" || bad "decrypt" "failed"

echo "== 7. after the update"
r=$(c "$B/checkupdates?force=1")
eq "demoupd no longer listed" "toonew" "$(echo "$r" | jq -r '[.updates[].code] | join(",")')"
r=$(c -X POST "$B/updatemodule?code=demoupd")
eq "no second update" not_found "$(echo "$r" | jq -r .code)"

echo "== 8. downloader down: stale list, then recovery"
docker compose stop tls >/dev/null 2>&1
r=$(c "$B/checkupdates?force=1")
eq "stale list served" true "$(echo "$r" | jq -r .stale)"
eq "list still there" toonew "$(echo "$r" | jq -r '[.updates[].code] | join(",")')"
docker compose start tls >/dev/null 2>&1
sleep 2
r=$(c "$B/checkupdates?force=1")
eq "fresh again" false "$(echo "$r" | jq -r .stale)"

echo "== 9. kill switch"
upsert module_updates_enabled 0
r=$(c "$B/checkupdates?force=1")
eq "disabled" disabled "$(echo "$r" | jq -r .code)"
upsert module_updates_enabled 1

echo "== 10. not logged in"
r=$(curl -s -H "X-Requested-With: XMLHttpRequest" "$B/checkupdates")
echo "$r" | head -c 80 | grep -q "success" && bad "anonymous" "got a list" || ok "anonymous request gets no list"

echo "== 11. downloader audit log"
dl php -r '
$db = new PDO("sqlite:/app/var/audit.sqlite");
foreach ($db->query("SELECT module, version, result, length(licence) l FROM events ORDER BY id") as $r) echo $r["module"], " ", $r["version"], " ", $r["result"], " ", $r["l"], PHP_EOL;'

echo
echo "$PASS passed, $FAIL failed"
[ $FAIL -eq 0 ]
