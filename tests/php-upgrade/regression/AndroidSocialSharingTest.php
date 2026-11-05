<?php
/**
 * cordova-plugin-x-socialsharing 6.0.4 in the Android shells, as released upstream. 6.0.4 makes the share chooser
 * PendingIntent immutable itself (Android 12+ refuses one without a flag), which is what the local PendingIntentFlag
 * patch of 5.6.4 did, so the local patch is gone.
 */
$root = dirname(__DIR__, 3);
$trees = ['siberian/var/apps/ionic/android', 'ionic/platforms/android'];

$read = function ($path) use ($root) {
    return file_get_contents("$root/$path");
};

return [
    'social sharing is 6.0.4 with an immutable chooser PendingIntent' => function () use ($trees, $read) {
        foreach ($trees as $tree) {
            t_true(strpos($read("$tree/app/src/main/assets/www/cordova_plugins.js"), '"SocialSharing": "6.0.4"') !== false, "$tree: record");
            $java = $read("$tree/app/src/main/java/nl/xservices/plugins/SocialSharing.java");
            t_true(strpos($java, 'PendingIntent.FLAG_UPDATE_CURRENT|PendingIntent.FLAG_IMMUTABLE') !== false, "$tree: immutable");
            t_equal(0, preg_match('/PendingIntentFlag/', $java), "$tree: local patch left over");
        }
    },

    'the share provider is the androidx one' => function () use ($trees, $read) {
        foreach ($trees as $tree) {
            t_true(strpos($read("$tree/app/src/main/java/nl/xservices/plugins/FileProvider.java"), 'androidx.core.content.FileProvider') !== false, $tree);
        }
    },
];
