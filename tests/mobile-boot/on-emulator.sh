#!/bin/bash
# Runs inside the emulator job: installs the APK, starts the app, waits until it asked the backend for its content,
# then reads what the WebView rendered. Evidence goes to $OUT.
#
#   on-emulator.sh <apk> <package> <server-log> <out-dir>
set -u
APK=$1; PKG=$2; SLOG=$3; OUT=$4
mkdir -p "$OUT"
fail=0

adb logcat -c
adb install -r -g "$APK" || { echo "install failed"; exit 1; }
adb shell monkey -p "$PKG" -c android.intent.category.LAUNCHER 1 >/dev/null

# 1. the app must reach the backend: the first thing the shell does is ask for the application
echo "waiting for the app to call the backend"
seen=0
for i in $(seq 1 60); do
  if grep -q "front/mobile/load" "$SLOG" 2>/dev/null; then seen=1; break; fi
  sleep 2
done
if [ "$seen" = 1 ]; then echo "backend was called: $(grep -m1 'front/mobile/load' "$SLOG")"; else echo "FAIL: the app never called front/mobile/load"; fail=1; fi

# 2. what the WebView rendered
pid=""
for i in $(seq 1 20); do pid=$(adb shell pidof "$PKG" | tr -d '\r'); [ -n "$pid" ] && break; sleep 1; done
if [ -z "$pid" ]; then
  echo "FAIL: the app process is not running (crash?)"; fail=1
else
  adb forward tcp:9222 "localabstract:webview_devtools_remote_$pid"
  if ! node "$(dirname "$0")/cdp-check.js" "$OUT/cdp.json"; then echo "FAIL: the WebView did not reach an AngularJS state"; fail=1; fi
fi

adb exec-out screencap -p > "$OUT/screen.png" || true
adb logcat -d -v time > "$OUT/logcat.txt" || true
if grep -q "FATAL EXCEPTION" "$OUT/logcat.txt"; then echo "FAIL: FATAL EXCEPTION in logcat"; grep -m3 -A8 "FATAL EXCEPTION" "$OUT/logcat.txt"; fail=1; fi
grep -E "chromium|Console|Uncaught|cordova" "$OUT/logcat.txt" | grep -i -E "error|uncaught|refused|failed" | head -20 > "$OUT/console-errors.txt" || true
echo "console errors: $(wc -l < "$OUT/console-errors.txt")"
exit $fail
