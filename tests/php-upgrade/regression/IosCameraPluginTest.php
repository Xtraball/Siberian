<?php
/**
 * Camera: cordova-plugin-camera 8.0.0 in the three iOS shells, the same version as the Android shells.
 *
 * - 8.0.0 returns DATA_URL as a full data URI ("data:image/jpeg;base64,..."), like Android, where 7.x returned the bare
 *   base64. The Picture service of the app accepts both (see AndroidCameraPictureTest), so it does not matter which
 *   shell a user has.
 * - Nothing else changes natively: the iOS part of plugin.xml (frameworks, source files) is the same as 7.0.0.
 */
$root = dirname(__DIR__, 3);
$trees = ['siberian/var/apps/ionic/ios', 'siberian/var/apps/ionic/ios-noads', 'ionic/platforms/ios'];

$read = function ($path) use ($root) {
    return file_get_contents("$root/$path");
};

return [
    'the iOS shells carry camera 8.0.0' => function () use ($trees, $read) {
        foreach ($trees as $tree) {
            $plugins = $read("$tree/www/cordova_plugins.js");
            t_true(strpos($plugins, '"Camera": "8.0.0"') !== false, "$tree: metadata");
            t_equal(0, preg_match('/"Camera": "7\./', $plugins), "$tree: no 7.x left");
        }
        $platformWww = $read('ionic/platforms/ios/platform_www/cordova_plugins.js');
        t_true(strpos($platformWww, '"Camera": "8.0.0"') !== false, 'platform_www metadata');
    },

    'CDVCamera.m returns a data URI for DATA_URL' => function () use ($trees, $read) {
        foreach ($trees as $tree) {
            $m = $read("$tree/AppsMobileCompany/Plugins/Camera/CDVCamera.m");
            t_true(strpos($m, 'processImageAsDataUri') !== false, "$tree: picker result");
            t_true(strpos($m, 'formatAsDataURI') !== false, "$tree: data URI helper");
            t_equal(0, preg_match('/messageAsString:\s*toBase64\(/', $m), "$tree: no bare base64 result");
        }
    },

    'the camera JavaScript of every iOS shell is the 8.0.0 one' => function () use ($trees, $read) {
        foreach ($trees as $tree) {
            $js = $read("$tree/www/plugins/Camera/www/Camera.js");
            t_true(strpos($js, 'allowEdit is deprecated') !== false, "$tree: Camera.js");
            t_equal(1, preg_match('/^cordova\.define\("Camera\.camera"/', $js), "$tree: module wrapper kept");
        }
        $js = $read('ionic/platforms/ios/platform_www/plugins/Camera/www/Camera.js');
        t_true(strpos($js, 'allowEdit is deprecated') !== false, 'platform_www Camera.js');
    },

    'no patch leftovers' => function () use ($trees, $root) {
        foreach ($trees as $tree) {
            t_equal([], glob("$root/$tree/www/plugins/Camera/www/*.orig"), "$tree: .orig files");
        }
    },
];
