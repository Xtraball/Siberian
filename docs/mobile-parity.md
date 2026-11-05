# Android and iOS parity

The Android and iOS apps are built from the same app code (`ionic/www`, shared by both), so a feature written once works on both. What can differ is native: the plugins, the permissions, the minimum OS version, the shell settings. **Keep them at parity as far as possible** (rule 7 of [AGENTS.md](../AGENTS.md)).

## The rule, in practice

- A change to a native plugin, a permission, a minimum OS version or a shell setting on one platform is made on the other one in the same pull request. If it cannot be (the platform does not have it, or it needs a device nobody has), the pull request says so and the difference is added to the table below.
- The shared JavaScript must work with the plugin versions of **both** shells. When a plugin changes its API or the format of what it returns, the shared code accepts the old and the new one (for example `Picture` accepts the bare base64 of camera plugin 7 and the data URI of plugin 8), so a shell on either version works while the other catches up.
- A pull request that touches a shell tree is built on both platforms before it is merged: Actions > **android-build** and **ios-build** > Run workflow (or push a branch named `ci/build-android-*` / `ci/build-ios-*`). The iOS ads template does not build today, it is off unless asked.
- `tests/php-upgrade/regression/MobileParityTest.php` checks what can be checked from the files: the same set of plugins on both platforms (except the ones in the first table), the same version where a plugin is shared on purpose, and that this page exists.

## Different on purpose

| What | Android | iOS | Why |
|---|---|---|---|
| Web view plugin | `IonicWebview` | `IosXhr` | each platform's own way to serve the app and its requests |
| `Permissions` plugin | native | a dummy JavaScript file | runtime permissions are an Android notion, the services guard on `cordova.plugins.permissions` |
| Minimum OS | Android 8.0 (API 26) | iOS 15 | Google Play and OneSignal 5.7 respectively |
| Write to shared storage | `WRITE_EXTERNAL_STORAGE` up to Android 9 (`maxSdkVersion` 28) | not applicable | scoped storage from Android 10 |
| Ads | in the Android shell | separate template (`ios`, AdMob, StartApp, Facebook) | the iOS ads pods are a different build |

## Known gaps to close

Versions below come from comparing the sources of the shells with the published releases of each plugin (the closest match), they are not recorded anywhere in the shell. Update this table when one is closed.

| What | Android | iOS | Note |
|---|---|---|---|
| `cordova-plugin-device` | 3.0.0 | about 1.1.2 | same JavaScript API |
| `cordova-plugin-geolocation` | 5.0.0 | about 2.4.1 | same `navigator.geolocation` API |
| `cordova-plugin-x-socialsharing` | 6.0.4 | 6.0.1 | |
| `cordova-plugin-x-toast` | 2.7.3 | about 2.6.1 | |
| `cordova-clipboard` | about 1.0.0 | about 1.1.1 | latest is 1.3.0 |
| `cordova-plugin-statusbar` | about 2.3.0 | 3.0.0 | |
| OneSignal native SDK | 5.11.0 | 5.8.0 | the Cordova wrapper is 5.2.9 on both |
| OneSignal in the iOS ads template | not applicable | 3.12.3 (v3 JavaScript) | the Push2 service uses the v5 API |
| Recorded plugin versions (`cordova_plugins.js`) | up to date | not updated: some labels do not match the native code | |
| iOS ads template build in CI | not applicable | fails on trunk (Copy XCFrameworks) | |
| `media`, `music-controls2`, `local-notification`, `browsertab` | customized, not updated | customized, not updated | merge the upstream releases one at a time, keeping our changes |
| `file`, `inappbrowser`, `statusbar` | not updated | not updated | behavior changes upstream, need a device test |
| `cordova-ios` / `CordovaLib` | 15.1.0 (`cordova-android`) | 6.1.1 | latest `cordova-ios` is 8.1.1 |

Nothing in this page has been checked on a physical device.
