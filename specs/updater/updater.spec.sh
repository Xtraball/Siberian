#!/bin/sh
# Critical path of the backoffice updater (Settings > Updates & Modules): the two ways something gets installed.
#   drag and drop   upload -> copy -> install            (a module archive sent from the browser)
#   remote          downloadupdate -> copy -> install    (the Siberian update server, mocked by specs/rig/mock.php)
# If one of these breaks, nobody can update an instance from its backoffice any more, so every step is checked on
# both the happy path and the ways it is fed garbage (not an archive, no manifest, path traversal, empty download...).
#
# Runs against the isolated rig (specs/rig/apply.sh): it installs a throw-away module named SpecUpd and removes it
# afterwards, and drives the update server mock through files in the mock container. Never point it at a real instance.
#   SPEC_SIB=sibtest2-siberian-1  SPEC_FAKE=sibtest2-fake-1  SPEC_DB=sibtest2-db-1  (container names of the rig)
. "$(dirname "$0")/../lib.sh"
HERE=$(cd "$(dirname "$0")" && pwd)
SRC=${SPEC_SRC:-$(cd "$HERE/../.." && pwd)/siberian}
SIB=${SPEC_SIB:-sibtest2-siberian-1}
FAKE=${SPEC_FAKE:-sibtest2-fake-1}
DB=${SPEC_DB:-sibtest2-db-1}
MOD=/srv/sib/app/local/modules/SpecUpd
WORK=$(mktemp -d /tmp/updater-spec.XXXXXX)
U=/installer/backoffice_module

# --- helpers -----------------------------------------------------------------------------------------------------
# mkzip FILE [VERSION] [extra python statement]: a module archive (manifest + init.php), optionally with more entries
mkzip() {
  python3 - "$1" "${2:-1.0.0}" "${3:-}" <<'PY'
import sys, json, zipfile
out, version, extra = sys.argv[1], sys.argv[2], sys.argv[3]
manifest = {"name": "SpecUpd", "description": "updater spec module", "type": "module", "version": version,
            "code": "specupd", "item_id": 900002, "dependencies": {"system": {"type": "SAE", "version": "5.0.0"}}}
with zipfile.ZipFile(out, "w") as z:
    if "nomanifest" not in extra:
        z.writestr("package.json", json.dumps(manifest))
    z.writestr("init.php", "<?php\n// updater spec module %s\n" % version)
    z.writestr("resources/hello.php", "<?php return 'hello %s';\n" % version)
    if "slip" in extra:
        z.writestr("../../../../spec-evil.txt", "evil")
        z.writestr("/tmp/spec-evil-abs.txt", "evil")
    if "nwicode" in extra:
        z.writestr("resources/bad.php", "<?php // nwicode\n")
PY
}
# upload FILE -> BODY/CODE of the drag and drop endpoint, as the browser sends it
upload() {
  BODY=$(curl -s -m 60 -o - -w '\n%{http_code}' -H 'X-Requested-With: XMLHttpRequest' -b "$SPEC_JAR" -F "file=@$1;filename=$2" "$SPEC_BASE$U/upload")
  CODE=$(printf '%s' "$BODY" | tail -n 1); BODY=$(printf '%s' "$BODY" | sed '$d')
}
unauth_upload() {
  BODY=$(curl -s -m 30 -o - -w '\n%{http_code}' -H 'X-Requested-With: XMLHttpRequest' -F "file=@$1;filename=$2" "$SPEC_BASE$U/upload")
  CODE=$(printf '%s' "$BODY" | tail -n 1); BODY=$(printf '%s' "$BODY" | sed '$d')
}
# install empties the whole cache, sessions included: the next call must log in again, as the screen does
install() { request POST "$U/install"; login; }
SPEC_TIMEOUT=180
in_sib() { docker exec "$SIB" sh -c "$1"; }
installed_version() { in_sib "cat $MOD/package.json 2>/dev/null" | jq -r .version 2>/dev/null; }
scenario() { docker exec "$FAKE" sh -c "mkdir -p /tmp/specs-updater && echo $1 > /tmp/specs-updater/scenario"; }
cleanup() {
  in_sib "rm -rf $MOD /srv/sib/var/tmp/SpecUpd* /srv/sib/var/tmp/spec-evil* /tmp/spec-evil*" 2>/dev/null
  # the rig's own database; the module has no uninstall, and a left-over row would make the next run "already installed"
  docker exec "$DB" sh -c 'mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" "${MARIADB_DATABASE:-siberian}" -e "DELETE FROM module WHERE name=\"SpecUpd\""' 2>/dev/null
  docker exec "$FAKE" sh -c "rm -rf /tmp/specs-updater" 2>/dev/null
  rm -rf "$WORK"
}
trap cleanup EXIT
cleanup; WORK=$(mktemp -d /tmp/updater-spec.XXXXXX)

# --- 1. the screen is wired to real actions ----------------------------------------------------------------------
spec_name "updater screen: every URL the page calls exists"
JS="$SRC/app/sae/design/desktop/backoffice/js"
CTRL="$SRC/app/sae/modules/Installer/controllers/Backoffice/ModuleController.php"
# checkforupdates is a leftover of the old updater: the factory still defines it, no template calls it
for a in $(grep -oh 'installer/backoffice_module/[a-z-]*' "$JS/factories/installer.js" "$JS/controllers/installer.js" | sed 's#.*/##' | sort -u); do
  [ "$a" = checkforupdates ] && continue
  [ "$a" = template ] && continue   # the Angular route's template, rendered by the layout, not an action
  fn=$(printf '%s' "$a" | tr -d '-')
  if grep -qi "function ${fn}Action" "$CTRL"; then pass "$a has an action"; else fail "$a has an action" "no ${fn}Action in ModuleController"; fi
done
grep -q 'nv-file-drop' "$SRC/app/sae/design/desktop/backoffice/template/installer/module.phtml" && pass "the drop zone is in the template" || fail "the drop zone is in the template" "nv-file-drop missing"
grep -q "installer/backoffice_module/upload" "$JS/controllers/installer.js" && pass "the uploader posts to /upload" || fail "the uploader posts to /upload" "url missing"

login

# --- 2. the page data --------------------------------------------------------------------------------------------
spec_name "updater screen: load"
request GET "$U/load"
expect_status "load" 200
[ "$(json '.ini.max_size')" -gt 0 ] 2>/dev/null && pass "load: upload limit is a positive number" || fail "load: upload limit is a positive number" "$(json '.ini.max_size')"
expect_json "load" '.words.confirmKey' 'yes-proceed-to-update-#VERSION#'
expect_json "load" '.icon' 'fa-cloud-download'

# --- 3. drag and drop --------------------------------------------------------------------------------------------
spec_name "drag and drop: bad input is refused cleanly"
request POST "$U/upload"
expect_status "no file" 400
expect_json "no file" '.error' true
printf 'this is not an archive' > "$WORK/text.sib"
upload "$WORK/text.sib" "text.sib"
[ "$CODE" = 400 ] && [ "$(json .error)" = true ] && pass "a text file is refused" || fail "a text file is refused" "HTTP $CODE $(printf '%s' "$BODY" | head -c 120)"
mkzip "$WORK/nomanifest.sib" 1.0.0 nomanifest
upload "$WORK/nomanifest.sib" "SpecUpd.sib"
[ "$CODE" = 400 ] && [ "$(json .error)" = true ] && pass "an archive without package.json is refused" || fail "an archive without package.json is refused" "HTTP $CODE $(printf '%s' "$BODY" | head -c 120)"
mkzip "$WORK/nwicode.sib" 1.0.0 nwicode
upload "$WORK/nwicode.sib" "SpecUpd.sib"
[ "$CODE" = 400 ] && printf '%s' "$BODY" | grep -q '88-888' && pass "an archive carrying Nwicode code is refused" || fail "an archive carrying Nwicode code is refused" "HTTP $CODE"
mkzip "$WORK/slip.sib" 1.0.0 slip
upload "$WORK/slip.sib" "SpecUpd.sib"
in_sib 'test -e /srv/sib/spec-evil.txt -o -e /srv/sib/var/spec-evil.txt -o -e /srv/sib/app/spec-evil.txt -o -e /tmp/spec-evil-abs.txt' && fail "path traversal in an archive writes nothing outside tmp" "a file escaped" || pass "path traversal in an archive writes nothing outside tmp"
unauth_upload "$WORK/slip.sib" "SpecUpd.sib"
[ "$CODE" != 200 ] || [ "$(json .success)" != true ] && pass "no session: no upload" || fail "no session: no upload" "HTTP $CODE success"
[ "$CODE" -lt 500 ] && pass "no session: no server error" || fail "no session: no server error" "HTTP $CODE"
[ -z "$(installed_version)" ] && pass "nothing was installed by the refused archives" || fail "nothing was installed by the refused archives" "SpecUpd is there"

spec_name "drag and drop: a module goes in, then gets updated"
mkzip "$WORK/SpecUpd-1.0.0.sib" 1.0.0
upload "$WORK/SpecUpd-1.0.0.sib" "SpecUpd-1.0.0.sib"
[ "$CODE" = 200 ] || echo "       server said: $(json .message)"
expect_status "upload 1.0.0" 200
expect_json "upload 1.0.0" '.package_details._name' SpecUpd
expect_json "upload 1.0.0" '.package_details.version' 1.0.0
FN=$(json .filename)
request GET "$U/copy?file=$FN"
expect_status "copy 1.0.0" 200
expect_json "copy 1.0.0" '.success' 1
request POST "$U/install"
expect_status "install 1.0.0" 200
expect_json "install 1.0.0" '.success' 1
expect_eq "the module files are in place" 1.0.0 "$(installed_version)"
mkzip "$WORK/SpecUpd-1.0.1.sib" 1.0.1
upload "$WORK/SpecUpd-1.0.1.sib" "SpecUpd-1.0.1.sib"
expect_json "upload 1.0.1" '.package_details.version' 1.0.1
request GET "$U/copy?file=$(json .filename)"
expect_json "copy 1.0.1" '.success' 1
request POST "$U/install"
expect_json "install 1.0.1" '.success' 1
expect_eq "the update replaced the files" 1.0.1 "$(installed_version)"

spec_name "drag and drop: copy refuses what it was not given"
request GET "$U/copy?file=$(printf 'does-not-exist.sib' | base64)"
expect_json "copy of a missing file" '.error' 1
request GET "$U/copy?file=$(printf '../../../../etc/passwd' | base64 -w0)"
expect_json "copy of a path outside tmp" '.error' 1
[ -f "$MOD/package.json" ] || in_sib "test -f $MOD/package.json" && pass "a refused copy leaves the installed module alone" || fail "a refused copy leaves the installed module alone" "module gone"
request GET "$U/copy"
[ "$CODE" = 400 ] && pass "copy without a file answers an error" || fail "copy without a file answers an error" "HTTP $CODE"

# --- 4. remote: the Siberian update server -----------------------------------------------------------------------
spec_name "remote update: nothing to update"
scenario none
request POST "$U/downloadupdate"
expect_status "up to date" 200
expect_json "up to date" '.success' true
[ -z "$(json .filename)" ] && pass "up to date: nothing is downloaded" || fail "up to date: nothing is downloaded" "$(json .filename)"

spec_name "remote update: the update server misbehaves"
for sc in check-500 check-garbage download-500 empty-download; do
  scenario $sc
  request POST "$U/downloadupdate"
  [ "$CODE" = 400 ] && [ "$(json .error)" = true ] && pass "$sc: a clean error" || fail "$sc: a clean error" "HTTP $CODE $(printf '%s' "$BODY" | head -c 100)"
done

spec_name "remote update: download, copy, install"
mkzip "$WORK/remote.sib" 1.0.2
docker exec "$FAKE" sh -c "mkdir -p /tmp/specs-updater"
docker cp "$WORK/remote.sib" "$FAKE:/tmp/specs-updater/package"
docker exec -u root "$FAKE" chmod 644 /tmp/specs-updater/package
scenario available
request POST "$U/downloadupdate"
expect_status "download" 200
expect_json "download" '.package_details._name' SpecUpd
expect_json "download" '.package_details.version' 1.0.2
RFN=$(json .filename)
request GET "$U/copy?file=$RFN"
expect_json "copy" '.success' 1
request POST "$U/install"
expect_json "install" '.success' 1
expect_eq "the remote package was installed" 1.0.2 "$(installed_version)"

spec_name "remote update: no session, no update"
: > "$WORK/anon.jar"
BODY=$(curl -s -m 30 -o - -w '\n%{http_code}' -X POST -H 'X-Requested-With: XMLHttpRequest' -b "$WORK/anon.jar" "$SPEC_BASE$U/downloadupdate")
CODE=$(printf '%s' "$BODY" | tail -n 1); BODY=$(printf '%s' "$BODY" | sed '$d')
[ "$CODE" -lt 500 ] && ! printf '%s' "$BODY" | grep -q '"filename"' && pass "downloadupdate without a session fetches nothing" || fail "downloadupdate without a session fetches nothing" "HTTP $CODE"

spec_done
