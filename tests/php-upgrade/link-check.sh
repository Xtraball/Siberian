#!/bin/sh
# Loads every first-party class file in its own PHP process, the way the application would. A method that does not
# match its parent (a fatal error since PHP 8, only a warning before) only shows when the class is linked, which
# `php -l` never does.
#
#   sh tests/php-upgrade/link-check.sh [--help] [root] [edition]     root defaults to ./siberian, edition to sae
#
# Files listed in tests/php-upgrade/link-check.known.txt are expected to fail (dead code referring to classes that
# do not exist) and are reported but do not fail the run. MAE / PE repositories hold app/mae and app/pe: check them
# against an assembled tree with edition mae or pe. Exit code 1 when a file outside the known list does not load.
if [ "$1" = "--help" ] || [ "$1" = "-h" ]; then
    sed -n '2,11p' "$0" | sed 's/^# \{0,1\}//'
    exit 0
fi
HERE=$(cd "$(dirname "$0")" && pwd)
ROOT=$(cd "${1:-siberian}" && pwd)
ED=${2:-sae}
case "$ED" in
    sae) DIRS="sae" ;;
    mae) DIRS="mae sae" ;;
    pe) DIRS="pe mae sae" ;;
    *) echo "edition must be sae, mae or pe" >&2; exit 2 ;;
esac
LIST=$(mktemp)
OUT=$(mktemp)
trap 'rm -f "$LIST" "$OUT"' EXIT
EXCL='/(resources|Resources|design|languages|tests|migrations)/|/(bootstrap|init|install|uninstall|update)\.php$'
SEEN=$(mktemp)
for d in $DIRS; do
    [ -d "$ROOT/app/$d/modules" ] || continue
    (cd "$ROOT/app/$d/modules" && find . -name '*.php' | sed 's#^\./##' | grep -Ev "$EXCL" | sort) | while read -r rel; do
        grep -qxF "$rel" "$SEEN" || { echo "$rel" >> "$SEEN"; echo "$ROOT/app/$d/modules/$rel" >> "$LIST"; }
    done
done
rm -f "$SEEN"
(cd "$ROOT/lib" && find Siberian -name '*.php' | sort | sed "s#^#$ROOT/lib/#") >> "$LIST"

one() {
    out=$(php -d memory_limit=512M "$HERE/link-check.php" "$ROOT" "$ED" "$1" 2>&1)
    if [ $? -ne 0 ]; then
        printf 'FAIL\t%s\t%s\n' "${1#"$ROOT"/}" "$(printf '%s' "$out" | grep -m1 -i 'fatal\|error\|exception' | tr '\t\n' '  ' | cut -c1-300)"
    fi
}
while read -r f; do one "$f"; done < "$LIST" > "$OUT"

known="$HERE/link-check.known.txt"
bad=0
while IFS="$(printf '\t')" read -r tag file msg; do
    if [ -f "$known" ] && grep -qxF "$file" "$known"; then
        echo "known   $file"
    else
        echo "FAILING $file: $msg"
        bad=1
    fi
done < "$OUT"
echo "link-check $ED: $(wc -l < "$LIST") files, $(grep -c '^FAIL' "$OUT") not loading, exit $bad"
exit $bad
