#!/bin/sh
# Builds the app source archives that "Restore Apps sources" (Backoffice > Advanced > Tools) downloads:
#   browser.tgz  (extracts to browser/, the restore also copies it to overview/)  -> var/apps
#   android.tgz  (extracts to android/)                                           -> var/apps/ionic
#   ios.tgz      (extracts to ios/)                                               -> var/apps/ionic
#   ios-noads.tgz (extracts to ios-noads/)                                        -> var/apps/ionic
# They are taken from a git ref (a clean export, so no .git), without .git / .gitignore / .gitattributes / .DS_Store.
# The output is reproducible: sorted entries, fixed owner, the commit date as mtime, gzip without a name or date.
set -eu

usage() {
    cat <<'EOF'
Usage: bin/build-app-sources.sh <version> [--ref <git ref>] [--out <dir>] [--dry-run]

  <version>   e.g. 5.1.3, only used for the default ref (v<version>) and the default output folder
  --ref       git ref to export, default v<version>
  --out       output folder, default ./app-sources-<version>
  --dry-run   only check that the ref has the four source folders and print what would be built

Writes browser.tgz, android.tgz, ios.tgz, ios-noads.tgz and SHA256SUMS in the output folder.
EOF
}

die() { echo "build-app-sources: $*" >&2; exit 1; }

VERSION=""
REF=""
OUT=""
DRY_RUN=0
while [ $# -gt 0 ]; do
    case "$1" in
        -h|--help) usage; exit 0 ;;
        --ref) [ $# -ge 2 ] || die "--ref needs a value"; REF="$2"; shift ;;
        --out) [ $# -ge 2 ] || die "--out needs a value"; OUT="$2"; shift ;;
        --dry-run) DRY_RUN=1 ;;
        -*) usage >&2; die "unknown option $1" ;;
        *) [ -z "$VERSION" ] || die "only one version"; VERSION="$1" ;;
    esac
    shift
done

[ -n "$VERSION" ] || { usage >&2; exit 2; }
case "$VERSION" in
    *[!0-9.]*|""|.*|*.) die "bad version '$VERSION'" ;;
esac
[ -n "$REF" ] || REF="v$VERSION"
[ -n "$OUT" ] || OUT="./app-sources-$VERSION"

ROOT=$(git rev-parse --show-toplevel) || die "run it inside the Siberian checkout"
git -C "$ROOT" rev-parse --verify --quiet "$REF^{commit}" >/dev/null || die "unknown ref $REF"
STAMP=$(git -C "$ROOT" log -1 --format=%ct "$REF")

# archive name : folder in the repository
SOURCES="browser:siberian/var/apps/browser
android:siberian/var/apps/ionic/android
ios:siberian/var/apps/ionic/ios
ios-noads:siberian/var/apps/ionic/ios-noads"

for entry in $SOURCES; do
    path=${entry#*:}
    git -C "$ROOT" cat-file -e "$REF:$path" 2>/dev/null || die "$path does not exist in $REF"
done

if [ "$DRY_RUN" = 1 ]; then
    for entry in $SOURCES; do
        name=${entry%%:*}
        path=${entry#*:}
        echo "dry run: $name.tgz <- $REF:$path ($(git -C "$ROOT" ls-tree -r --name-only "$REF" "$path" | wc -l) files)"
    done
    exit 0
fi

mkdir -p "$OUT"
OUT=$(cd "$OUT" && pwd)
STAGE=$(mktemp -d)
trap 'rm -rf "$STAGE"' EXIT

for entry in $SOURCES; do
    name=${entry%%:*}
    path=${entry#*:}
    git -C "$ROOT" archive --format=tar --prefix="$name/" "$REF:$path" -o "$STAGE/export.tar"
    tar -x -f "$STAGE/export.tar" -C "$STAGE"
    rm -f "$STAGE/export.tar"
    find "$STAGE/$name" \( -name .git -o -name .gitignore -o -name .gitattributes -o -name .DS_Store \) -prune -exec rm -rf {} +
    tar --sort=name --owner=0 --group=0 --numeric-owner --mtime="@$STAMP" -C "$STAGE" -cf "$STAGE/$name.tar" "$name"
    gzip -9n < "$STAGE/$name.tar" > "$OUT/$name.tgz"
    rm -rf "$STAGE/$name" "$STAGE/$name.tar"
    echo "built $name.tgz ($(wc -c < "$OUT/$name.tgz") bytes)"
done

(cd "$OUT" && sha256sum browser.tgz android.tgz ios.tgz ios-noads.tgz > SHA256SUMS)
echo "done: $OUT"
