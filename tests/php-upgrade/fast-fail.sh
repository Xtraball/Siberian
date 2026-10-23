#!/bin/sh
# Fast-fail gate for the PHP upgrade. Runs INSIDE a php image (see ci.sh).
# Stages run cheapest first and the first failure stops everything.
#
#   fast-fail.sh [--update-baseline] [--vendor] [--skip=stage[,stage]]
#
# 1. extensions   every ext-* in composer.json is loaded        (<1s)
# 2. manifest     composer.json parses, php constraint admits us (<1s)
# 3. syntax       php -l on first-party code: parse/fatal = fail (~10s)
# 4. ratchet      deprecation count may fall, never rise
# 5. regression   tests/php-upgrade/regression/*Test.php
# --vendor adds a php -l pass over the committed lib/vendor tree.
set -u
cd "$(dirname "$0")/../.."

MM=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
BASELINE="tests/php-upgrade/baseline/deprecations-$MM.txt"
UPDATE=0; VENDOR=0; SKIP=,
for a in "$@"; do
    case "$a" in
        --update-baseline) UPDATE=1 ;;
        --vendor) VENDOR=1 ;;
        --skip=*) SKIP=",${a#--skip=}," ;;
    esac
done

skipped() { case "$SKIP" in *",$1,"*) echo "skip [$1]"; return 0;; esac; return 1; }
fail() { echo "FAIL [$1] $2"; exit 1; }
ok()   { echo "ok   [$1] $2"; }

# 1 -----------------------------------------------------------------------
missing=$(php -r '
    $c = json_decode(file_get_contents("siberian/composer.json"), true);
    foreach (array_keys($c["require"]) as $r) {
        if (strpos($r, "ext-") === 0 && !extension_loaded(strtolower(substr($r, 4)))) echo substr($r, 4), " ";
    }')
[ -z "$missing" ] || fail extensions "PHP $MM is missing: $missing"
ok extensions "PHP $MM has every ext-* from composer.json"

# 2 -----------------------------------------------------------------------
skipped manifest || { php -r '
    $c = json_decode(file_get_contents("siberian/composer.json"), true);
    if (!$c) { fwrite(STDERR, "composer.json is not valid JSON\n"); exit(1); }
    $req = $c["require"]["php"];
    // Exact pins ("8.2") are what blocked upgrades before; anything not a range is flagged.
    if (preg_match("/^\d+\.\d+(\.\d+)?$/", $req)) {
        fwrite(STDERR, "php constraint \"$req\" is an exact pin, use a range such as \">=8.2 <8.6\"\n");
        exit(2);
    }
' || fail manifest "composer.json php constraint (see message above)"
ok manifest "composer.json constraint is a range"; }

# 3 -----------------------------------------------------------------------
# AdminGenerator templates hold {{placeholders}} and are not valid PHP by design.
git ls-files '*.php' | grep -v -e '^siberian/lib/vendor/' -e '^resources/AdminGenerator/templates/' > /tmp/ff-files.txt
[ "$VENDOR" = 1 ] && git ls-files 'siberian/lib/vendor/*.php' >> /tmp/ff-files.txt
xargs -P8 -n1 php -d error_reporting=-1 -l < /tmp/ff-files.txt > /tmp/ff-lint.txt 2>&1
errs=$(grep -E '^(PHP )?(Parse|Fatal) error' /tmp/ff-lint.txt)
[ -z "$errs" ] || { echo "$errs" | head -20; fail syntax "$(echo "$errs" | wc -l) file(s) do not compile on PHP $MM"; }
ok syntax "$(wc -l < /tmp/ff-files.txt) files compile on PHP $MM"

# 4 -----------------------------------------------------------------------
count=$(grep -c '^Deprecated:' /tmp/ff-lint.txt)
if [ "$UPDATE" = 1 ]; then
    mkdir -p "$(dirname "$BASELINE")"; echo "$count" > "$BASELINE"
    grep '^Deprecated:' /tmp/ff-lint.txt | sed -E 's/ in .* on line [0-9]+//' | sort | uniq -c | sort -rn > "${BASELINE%.txt}.detail.txt"
    ok ratchet "baseline for $MM set to $count"
elif [ -f "$BASELINE" ]; then
    base=$(cat "$BASELINE")
    [ "$count" -le "$base" ] || { grep '^Deprecated:' /tmp/ff-lint.txt | head -10; fail ratchet "$count deprecations on $MM, baseline allows $base"; }
    ok ratchet "$count deprecations (baseline $base)"
else
    ok ratchet "no baseline for $MM yet ($count deprecations); run with --update-baseline"
fi

# 5 -----------------------------------------------------------------------
php -d display_errors=0 tests/php-upgrade/run.php --fail-fast || fail regression "see failure above"
ok regression "suite green on PHP $MM"
echo "ALL STAGES PASSED on PHP $MM"
