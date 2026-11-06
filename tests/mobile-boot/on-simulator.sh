#!/bin/bash
# Installs the built .app in an iOS Simulator, starts it and waits until the app reports an AngularJS state to the
# test instance (tests/mobile-boot/beacon.js). Evidence goes to <out-dir>.
#
#   on-simulator.sh <App.app> <bundle-id> <requests.log> <out-dir>
set -u
APP=$1; BID=$2; RLOG=$3; OUT=$4
mkdir -p "$OUT"
fail=0

# first available iPhone, newest runtime first
UDID=$(xcrun simctl list devices available -j | node -e '
  const d = JSON.parse(require("fs").readFileSync(0, "utf8")).devices;
  const rt = Object.keys(d).filter((k) => /iOS/.test(k)).sort((a, b) => b.localeCompare(a, undefined, { numeric: true }));
  for (const k of rt) { const p = d[k].find((x) => /iPhone/.test(x.name)); if (p) { console.log(p.udid); break; } }')
[ -n "$UDID" ] || { echo "no iPhone simulator available"; xcrun simctl list devices available; exit 1; }
echo "simulator: $(xcrun simctl list devices available | grep "$UDID")"

xcrun simctl boot "$UDID"
xcrun simctl bootstatus "$UDID" -b >/dev/null 2>&1
xcrun simctl install "$UDID" "$APP" || { echo "FAIL: install"; exit 1; }
xcrun simctl launch "$UDID" "$BID" || { echo "FAIL: launch"; fail=1; }

echo "waiting for the app to ask for its content and report a state"
for i in $(seq 1 90); do
  grep -q "__boot/state/" "$RLOG" 2>/dev/null && break
  sleep 2
done

grep -m3 "front/mobile/load" "$RLOG" || { echo "FAIL: the app never called front/mobile/load"; fail=1; }
if grep -q "__boot/state/" "$RLOG"; then
  echo "state reported: $(grep -m1 '__boot/state/' "$RLOG")"
else
  echo "FAIL: the WebView never reached an AngularJS state"; fail=1
fi

xcrun simctl io "$UDID" screenshot "$OUT/screen.png" || true
xcrun simctl spawn "$UDID" launchctl list 2>/dev/null | grep -i "$BID" > "$OUT/launchctl.txt" || true
if [ -s "$OUT/launchctl.txt" ]; then echo "process: $(head -1 "$OUT/launchctl.txt")"; else echo "FAIL: the app process is gone (crash?)"; fail=1; fi
xcrun simctl spawn "$UDID" log show --last 5m --style compact --predicate 'process == "AppsMobileCompany" OR subsystem CONTAINS "cordova"' > "$OUT/app.log" 2>&1 || true
ls ~/Library/Logs/DiagnosticReports 2>/dev/null | grep -i AppsMobile > "$OUT/crash-reports.txt" || true
[ -s "$OUT/crash-reports.txt" ] && { echo "FAIL: crash report"; cat "$OUT/crash-reports.txt"; fail=1; }
exit $fail
