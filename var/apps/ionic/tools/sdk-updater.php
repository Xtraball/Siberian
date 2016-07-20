<?php
/**
 * Automatically download the Android SDK
 */
$log = array();

$tools_path = dirname(__FILE__);
chmod($tools_path, 0777);
if (!@file_exists($tools_path)) {
    mkdir($tools_path, 0777, true);
}

$android_sdk_path = "{$tools_path}/android-sdk";

/** Test if the SDK is correctly installed */
function shouldupdate($android_sdk_path)
{
    $required_folders = array(
    $android_sdk_path . "/build-tools/23.0.2",
    $android_sdk_path . "/platforms/android-22",
    $android_sdk_path . "/extras/android/support",
    $android_sdk_path . "/extras/android/m2repository",
    $android_sdk_path . "/extras/google/m2repository",
    $android_sdk_path . "/extras/google/google_play_services",
);
    foreach($required_folders as $folder) {
        if(!file_exists($folder)) {
            return true;
        }
    }
    return false;
}

$download_url = "http://91.121.77.120/tools/android-sdk.tgz";

if(shouldupdate($android_sdk_path)) {
    rmdir($android_sdk_path);
    chdir($tools_path);
    exec("wget {$download_url} && tar xzf android-sdk.tgz && rm android-sdk.tgz");
}

exec("chmod -R 777 $android_sdk_path");