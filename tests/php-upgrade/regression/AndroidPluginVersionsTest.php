<?php
/**
 * cordova_plugins.js records the version of each plugin of the Android shells. Three of them still said an older
 * version than the code that is in the tree (the code had been updated, the record had not). The record must follow
 * the code, a marker of the newer release is checked for each.
 */
$root = dirname(__DIR__, 3);
$trees = ['siberian/var/apps/ionic/android', 'ionic/platforms/android'];

$read = function ($path) use ($root) {
    return file_get_contents("$root/$path");
};

return [
    'toast 2.7.3: Android 11+ has no custom toast view' => function () use ($trees, $read) {
        foreach ($trees as $tree) {
            t_true(strpos($read("$tree/app/src/main/assets/www/cordova_plugins.js"), '"Toast": "2.7.3"') !== false, "$tree: record");
            t_true(strpos($read("$tree/app/src/main/java/nl/xservices/plugins/Toast.java"), 'IS_AT_LEAST_R') !== false, "$tree: code");
        }
    },

    'ionic webview 5.0.1: service worker requests can be resolved' => function () use ($trees, $read) {
        foreach ($trees as $tree) {
            t_true(strpos($read("$tree/app/src/main/assets/www/cordova_plugins.js"), '"IonicWebview": "5.0.1"') !== false, "$tree: record");
            t_true(strpos($read("$tree/app/src/main/java/com/ionicframework/cordova/webview/IonicWebViewEngine.java"), 'ResolveServiceWorkerRequests') !== false, "$tree: code");
        }
    },

    'barcode scanner 8.1.0: the request code fits 16 bits' => function () use ($trees, $read) {
        foreach ($trees as $tree) {
            t_true(strpos($read("$tree/app/src/main/assets/www/cordova_plugins.js"), '"BarcodeScanner": "8.1.0"') !== false, "$tree: record");
            $java = $read("$tree/app/src/main/java/com/phonegap/plugins/barcodescanner/BarcodeScanner.java");
            t_true(strpos($java, 'REQUEST_CODE = 0x0ba7c;') !== false, "$tree: code");
        }
    },
];
