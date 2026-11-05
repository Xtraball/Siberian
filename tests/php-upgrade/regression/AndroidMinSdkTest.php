<?php
/**
 * The generated Android apps need Android 8.0 (API 26) or later. The minimum is written in several places that must
 * agree: the Cordova config (android-minSdkVersion) and cdv-gradle-config.json (MIN_SDK_VERSION, which the Gradle
 * build reads) in the template and in the dev tree, and the hint shown in the backoffice.
 */
$root = dirname(__DIR__, 3);
$trees = ['siberian/var/apps/ionic/android', 'ionic/platforms/android'];
$min = 26;

$read = function ($path) use ($root) {
    return file_get_contents("$root/$path");
};

return [
    'cdv-gradle-config.json of every Android tree has the minimum' => function () use ($trees, $min, $read) {
        foreach ($trees as $tree) {
            $json = json_decode($read("$tree/cdv-gradle-config.json"), true);
            t_equal($min, $json['MIN_SDK_VERSION'], "$tree: MIN_SDK_VERSION");
        }
    },

    'the Cordova configs ask for the same minimum' => function () use ($trees, $min, $read) {
        $files = ['ionic/config.xml'];
        foreach ($trees as $tree) {
            $files[] = "$tree/config.bck.xml";
            $files[] = "$tree/app/src/main/res/xml/config.xml";
        }
        foreach ($files as $file) {
            t_true(
                preg_match('/android-minSdkVersion" value="' . $min . '"/', $read($file)) === 1,
                $file
            );
        }
    },

    'the backoffice hint says Android 8.0+ and not 7.0+' => function () use ($read) {
        $view = $read('siberian/app/sae/modules/Application/resources/design/desktop/backoffice/template/application/view/source.phtml');
        t_true(strpos($view, 'Android 8.0+') !== false, 'Android 8.0+');
        t_true(strpos($view, 'Android 7.0+') === false, 'no Android 7.0+');
        t_true(strpos($view, 'Android Studio Koala') === false, 'no Koala');
    },
];
