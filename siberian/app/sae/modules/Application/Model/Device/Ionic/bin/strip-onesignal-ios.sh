#!/bin/bash
# DEBUG ONLY. Removes OneSignal from a generated iOS shell (the "ios-noads" template or a downloaded copy of it), so
# an app can be tested on real hardware without the push SDK. Never publish what this produces.
#
# Usage: strip-onesignal-ios.sh <ios-source-dir>
#
# <ios-source-dir> is the folder that holds AppsMobileCompany/, AppsMobileCompany.xcodeproj, Podfile, ios.json, www/.
# The script is idempotent. After it, run `pod install` once on the Mac (the Pods folder and Podfile.lock are
# removed because they are the OneSignal binaries), then build as usual.
#
# What it removes: the native plugin (OneSignalPush.m/.h and its project entries), the config.xml feature, the
# cordova plugin registration (ios.json, www/cordova_plugins.js) and the JavaScript plugin that defines
# window.plugins.OneSignal, so the app code sees "no OneSignal plugin" and skips push (Push2Service already does).
set -euo pipefail

SRC="${1:-}"
if [ -z "$SRC" ] || [ ! -d "$SRC/AppsMobileCompany.xcodeproj" ] || [ ! -d "$SRC/AppsMobileCompany" ]; then
    echo "usage: $0 <ios-source-dir> (a folder holding AppsMobileCompany.xcodeproj)" >&2
    exit 2
fi
SRC="$(cd "$SRC" && pwd)"

PBX="$SRC/AppsMobileCompany.xcodeproj/project.pbxproj"
CONFIG="$SRC/AppsMobileCompany/config.xml"
PLIST="$SRC/AppsMobileCompany/AppsMobileCompany-Info.plist"

# 1. Native plugin
rm -rf "$SRC/AppsMobileCompany/Plugins/Onesignal"
# Every project entry for the plugin (build file, two file references, group children, sources phase) names OneSignalPush.
[ -f "$PBX" ] && perl -ni -e 'print unless /OneSignalPush/' "$PBX"

# 2. Cordova registration
for f in "$CONFIG" "$SRC/config.bck.xml"; do
    [ -f "$f" ] && perl -0pi -e 's{[ \t]*<feature name="OneSignalPush">.*?</feature>[ \t]*\n?}{}s' "$f"
done
if [ -f "$SRC/ios.json" ]; then
    perl -0pi -e '
        s/,\s*\{\s*"xml":\s*"<feature name=\\"OneSignalPush\\">[^{}"]*(?:\\"[^{}"]*)*"[^{}]*\}//g;
        s/,\s*"Onesignal":\s*\{[^{}]*\}//g;
        s/,\s*\{\s*"id":\s*"Onesignal\.[^{}]*\}//g;
        s/^[ \t]*"Onesignal":\s*"[^"]*",?[ \t]*\n//mg;
    ' "$SRC/ios.json"
fi
[ -f "$SRC/www/cordova_plugins.js" ] && perl -0pi -e '
    s/,\s*\{\s*"id":\s*"Onesignal\.[^{}]*\}//g;
    s/^[ \t]*"Onesignal":\s*"[^"]*",?[ \t]*\n//mg;
' "$SRC/www/cordova_plugins.js"
[ -f "$SRC/pods.json" ] && perl -0pi -e 's/\s*"OneSignalXCFramework":\s*\{[^{}]*\}//g' "$SRC/pods.json"

# 3. JavaScript plugin (defines window.plugins.OneSignal)
rm -rf "$SRC/www/plugins/Onesignal"

# 4. CocoaPods: the only pod is OneSignal. Drop the pod line and the committed binaries.
[ -f "$SRC/Podfile" ] && perl -ni -e 'print unless /pod\s+.OneSignal/' "$SRC/Podfile"
rm -rf "$SRC/Pods" "$SRC/Podfile.lock"

# 5. Make the build impossible to mistake for a release build
if [ -f "$PLIST" ] && ! grep -q '\[DEBUG\]' "$PLIST"; then
    perl -0pi -e 's{(<key>CFBundleDisplayName</key>\s*<string>)(.*?)(</string>)}{$1$2 [DEBUG]$3}s' "$PLIST"
fi
cat > "$SRC/DEBUG-NO-ONESIGNAL.txt" <<'EOF'
DEBUG BUILD: OneSignal has been removed from this iOS source (push notifications do not work).
For development on real hardware only. Never submit this to the App Store or TestFlight.

Before building: run `pod install` once in this folder, then open AppsMobileCompany.xcworkspace.
EOF

# 6. Self-check: nothing may still name OneSignal in the shell (the packed web bundles keep their guarded JS code).
LEFT="$(grep -rIil onesignal "$SRC" --exclude-dir=dist --exclude=DEBUG-NO-ONESIGNAL.txt || true)"
if [ -n "$LEFT" ]; then
    echo "OneSignal is still referenced in:" >&2
    echo "$LEFT" >&2
    exit 1
fi

# 7. The edited JSON must still parse
for f in "$SRC/ios.json"; do
    [ -f "$f" ] || continue
    if command -v python3 >/dev/null 2>&1; then
        python3 -c 'import json,sys; json.load(open(sys.argv[1]))' "$f"
    elif command -v node >/dev/null 2>&1; then
        node -e 'JSON.parse(require("fs").readFileSync(process.argv[1],"utf8"))' "$f"
    fi
done

echo "OneSignal removed from $SRC"
