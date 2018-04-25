<?php
/**
 * Automatically download the Android SDK
 */
if (is_file('./config.php')) {
    require './config.php';

    if (isset($config) && array_key_exists('disabled', $config)) {
        die('SDK Updater is disabled in ./config.php');
    }
}

$toolsPath = dirname(__FILE__);
chmod($toolsPath, 0777);
if (!@file_exists($toolsPath)) {
    mkdir($toolsPath, 0777, true);
}

$androidSdkPath = $toolsPath . '/android-sdk';

/** Test if the SDK is correctly installed */
function shouldupdate($androidSdkPath) {
    $required_folders = [
        $androidSdkPath . '/build-tools/23.0.2',
        $androidSdkPath . '/platforms/android-25',
        $androidSdkPath . '/extras/android/support',
        $androidSdkPath . '/extras/android/m2repository',
        $androidSdkPath . '/extras/google/m2repository',
        $androidSdkPath . '/extras/google/google_play_services',
    ];

    foreach ($required_folders as $folder) {
        if (!file_exists($folder)) {
            return true;
        }
    }
    return false;
}

$downloadUrls = [
    'http://updates02.siberiancms.com/tools/android-sdk.tar'
];

$size = sizeof($downloadUrls) - 1;
$rand = rand(0, $size);
$downloadUrl = $downloadUrls[$rand];

if (shouldupdate($androidSdkPath)) {
    rmdir($androidSdkPath);
    chdir($toolsPath);
    /** Clean-up  */
    exec('rm -rf ./android-sdk');
    exec('rm -rf ./android-sdk.t*');
    exec('wget -O android-sdk.tar ' . $downloadUrl . ' && tar --overwrite -xf android-sdk.tar && rm android-sdk.tar');
}

exec('chmod -R 777 ' . $androidSdkPath);