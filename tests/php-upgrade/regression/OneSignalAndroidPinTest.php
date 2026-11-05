<?php
/**
 * The OneSignal native SDK of the Android shell. The two trees that hold the Android app (the development platform and
 * the template the app generator copies) must pin the same version, in the Gradle dependency and in the Cordova
 * project.properties that mirrors it. The Cordova wrapper files (com/onesignal/cordova) are those of plugin 5.2.9 and
 * only use API that 5.11.0 still has: initWithContext(Context, String), login, logout and the get* managers.
 */
$root = dirname(__DIR__, 3);
$trees = [
    'ionic/platforms/android',
    'siberian/var/apps/ionic/android',
];
$expected = '5.11.0';

return [
    'every Android tree pins the same native SDK in Gradle' => function () use ($root, $trees, $expected) {
        foreach ($trees as $tree) {
            $gradle = file_get_contents("$root/$tree/app/build.gradle");
            t_true(preg_match_all('/implementation "com\.onesignal:OneSignal:([0-9.]+)"/', $gradle, $m) === 1, "$tree: one OneSignal dependency");
            t_equal($expected, $m[1][0], "$tree: app/build.gradle");
        }
    },

    'the Cordova project.properties mirrors the Gradle pin' => function () use ($root, $trees, $expected) {
        foreach ($trees as $tree) {
            $props = file_get_contents("$root/$tree/project.properties");
            t_true(strpos($props, "=com.onesignal:OneSignal:$expected\n") !== false, "$tree: project.properties");
            t_equal(0, preg_match('/com\.onesignal:OneSignal:5\.1\./', $props), "$tree: no old pin left");
        }
    },

    'the wrapper only calls API that 5.11.0 keeps' => function () use ($root, $trees) {
        foreach ($trees as $tree) {
            $code = file_get_contents("$root/$tree/app/src/main/java/com/onesignal/cordova/OneSignalPush.java");
            t_true(strpos($code, 'OneSignal.initWithContext(this.cordova.getActivity(), appId)') !== false, "$tree: two-argument init");
            t_equal(0, preg_match('/OneSignal\.initWithContext\(\s*[^,()]+\s*\)/', $code), "$tree: no one-argument init (not in 5.11.0)");
        }
    },
];
