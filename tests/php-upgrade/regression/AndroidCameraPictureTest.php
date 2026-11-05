<?php
/**
 * Camera: cordova-plugin-camera 8.0.0 in the Android shells, and the Picture service of the app made to match it.
 *
 * - 8.0.0 no longer declares or asks for READ_MEDIA_IMAGES / READ_MEDIA_VIDEO / WRITE_EXTERNAL_STORAGE (the system
 *   picker needs none; Google Play restricts them). The "remove the photos & videos permissions" option of the
 *   application then has nothing to strip, and the plugin cannot fail on an undeclared permission.
 * - 8.0.0 returns DATA_URL as a full data URI where 7.x returned the bare base64: Picture added the prefix itself and
 *   would have doubled it.
 */
$root = dirname(__DIR__, 3);
$trees = ['siberian/var/apps/ionic/android', 'ionic/platforms/android'];

$read = function ($path) use ($root) {
    return file_get_contents("$root/$path");
};

return [
    'the Android shells carry camera 8.0.0' => function () use ($trees, $read) {
        foreach ($trees as $tree) {
            $plugins = $read("$tree/app/src/main/assets/www/cordova_plugins.js");
            t_true(strpos($plugins, '"Camera": "8.0.0"') !== false, "$tree: metadata");
            $java = $read("$tree/app/src/main/java/org/apache/cordova/camera/CameraLauncher.java");
            t_equal(0, preg_match('/READ_MEDIA_(IMAGES|VIDEO)/', $java), "$tree: CameraLauncher asks for media permissions");
            t_equal(0, preg_match('/declaredOnly/', $java), "$tree: the 7.x workaround is gone");
            $paths = $read("$tree/app/src/main/res/xml/camera_provider_paths.xml");
            t_true(strpos($paths, 'org.apache.cordova.camera/') !== false, "$tree: provider isolated to a subdirectory");
        }
    },

    'the manifests and android.json no longer declare the plugin permissions' => function () use ($trees, $read) {
        foreach ($trees as $tree) {
            foreach (["$tree/app/src/main/AndroidManifest.xml", "$tree/android.json"] as $file) {
                $src = $read($file);
                t_equal(0, preg_match('/READ_MEDIA_(IMAGES|VIDEO)/', $src), "$file: READ_MEDIA");
                t_equal(0, preg_match('/WRITE_EXTERNAL_STORAGE/', $src), "$file: WRITE_EXTERNAL_STORAGE");
                t_true(strpos($src, 'android.permission.CAMERA') !== false, "$file: CAMERA is kept");
            }
        }
    },

    'Picture accepts a bare base64 and a data URI, and a cancel is not an error' => function () use ($read) {
        $src = $read('ionic/www/js/services/picture.js');
        t_true(strpos($src, 'service.toDataUrl') !== false, 'toDataUrl');
        t_true(strpos($src, "indexOf('data:') === 0") !== false, 'keeps a data URI as is');
        t_true(strpos($src, 'service.isCancellation') !== false, 'isCancellation');
        t_equal(0, preg_match('/Camera === undefined/', $src), 'ReferenceError when the plugin is missing');
        t_equal(0, preg_match("/'data:image\\/jpeg;base64,' \\+ imageData/", $src), 'unconditional prefix');
    },

    'the packed services bundles carry the new Picture' => function () use ($read) {
        foreach (['ionic/www/dist/services.bundle.min.js', 'siberian/var/apps/browser/dist/services.bundle.min.js', 'siberian/var/apps/overview/dist/services.bundle.min.js'] as $bundle) {
            $src = $read($bundle);
            t_true(strpos($src, 'isCancellation') !== false, "$bundle: isCancellation");
            t_true(strpos($src, 'toDataUrl') !== false, "$bundle: toDataUrl");
        }
    },
];
