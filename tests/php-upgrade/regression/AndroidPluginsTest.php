<?php
/**
 * Plugins with no Siberian change that are brought to the current upstream release in both Android trees:
 * cordova-plugin-device 3.0.0 and cordova-plugin-geolocation 5.0.0. The version recorded in cordova_plugins.js must
 * be the one of the Java and JS that are in the tree.
 */
$root = dirname(__DIR__, 3);
$trees = ['siberian/var/apps/ionic/android', 'ionic/platforms/android'];

$read = function ($path) use ($root) {
    return file_get_contents("$root/$path");
};

return [
    'device is 3.0.0' => function () use ($trees, $read) {
        foreach ($trees as $tree) {
            t_true(strpos($read("$tree/app/src/main/assets/www/cordova_plugins.js"), '"Device": "3.0.0"') !== false, "$tree: metadata");
            $java = $read("$tree/app/src/main/java/org/apache/cordova/device/Device.java");
            t_equal(0, preg_match('/AMAZON_PLATFORM|getProductName/', $java), "$tree: removed upstream code");
            t_true(strpos($java, 'getSDKVersion') !== false, "$tree: sdkVersion");
            t_true(strpos($read("$tree/app/src/main/assets/www/plugins/Device/www/device.js"), 'isiOSAppOnMac') !== false, "$tree: device.js");
        }
    },

    'geolocation is 5.0.0' => function () use ($trees, $read) {
        foreach ($trees as $tree) {
            t_true(strpos($read("$tree/app/src/main/assets/www/cordova_plugins.js"), '"Geolocation": "5.0.0"') !== false, "$tree: metadata");
            $java = $read("$tree/app/src/main/java/org/apache/cordova/geolocation/Geolocation.java");
            // 5.0.0 asks for the location permissions that match the requested accuracy on Android 12+
            t_true(strpos($java, 'ACCESS_COARSE_LOCATION') !== false, "$tree: coarse location");
        }
    },

    'every cordova.define wrapper is still in place' => function () use ($trees, $read) {
        foreach ($trees as $tree) {
            foreach (['Device/www/device.js', 'Geolocation/www/android/geolocation.js', 'Geolocation/www/PositionError.js'] as $file) {
                $src = $read("$tree/app/src/main/assets/www/plugins/$file");
                t_equal(1, preg_match('/^cordova\.define\("[A-Za-z]+\.[A-Za-z]+", function\(require, exports, module\) \{/', $src), "$tree/$file: wrapper start");
                t_equal(1, preg_match('/\}\);\s*$/', $src), "$tree/$file: wrapper end");
            }
        }
    },
];
