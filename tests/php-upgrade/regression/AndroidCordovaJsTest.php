<?php
/**
 * The native side of the Android apps is cordova-android 15.1.0 (CordovaLib) while cordova.js, the JS half of the
 * platform, was still 14.0.1. cordova.js 15 adds window.statusbar and androidExec.pollOnce, which the 15.x native
 * SystemBarPlugin and exec bridge go with. The two halves must be the same version.
 */
$root = dirname(__DIR__, 3);
$files = [
    'ionic/platforms/android/app/src/main/assets/www/cordova.js',
    'ionic/platforms/android/platform_www/cordova.js',
    'siberian/var/apps/ionic/android/app/src/main/assets/www/cordova.js',
];

$read = function ($path) use ($root) {
    return file_get_contents("$root/$path");
};

return [
    'cordova.js of the Android apps has the version of CordovaLib' => function () use ($files, $read) {
        foreach (['ionic/platforms/android', 'siberian/var/apps/ionic/android'] as $tree) {
            preg_match('/CORDOVA_VERSION = "([^"]+)"/', $read("$tree/CordovaLib/src/org/apache/cordova/CordovaWebView.java"), $native);
            t_true(isset($native[1]), "$tree: native version");
            foreach ($files as $file) {
                if (strpos($file, $tree) !== 0) {
                    continue;
                }
                preg_match("/PLATFORM_VERSION_BUILD_LABEL = '([^']+)'/", $read($file), $js);
                t_equal($native[1], $js[1] ?? null, $file);
            }
        }
    },

    'cordova.js of the Android apps has the 15.x bridge additions' => function () use ($files, $read) {
        foreach ($files as $file) {
            $src = $read($file);
            t_true(strpos($src, 'androidExec.pollOnce = pollOnce;') !== false, "$file: pollOnce");
            t_true(strpos($src, "cordova/plugin/android/statusbar") !== false, "$file: statusbar");
        }
    },
];
