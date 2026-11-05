<?php
/**
 * The generated iOS apps need iOS 15 or later. The minimum is written in several places that must agree: the Podfile
 * (platform and post_install), the Cordova config, the Xcode projects of the app, of CordovaLib and of the committed
 * Pods, and the script that patches old Pods. OneSignal iOS 5.7.0 and later need iOS 15, which is the minimum now: the pod
 * is pinned to the current release, 5.8.0.
 */
$root = dirname(__DIR__, 3);
$trees = ['siberian/var/apps/ionic/ios', 'siberian/var/apps/ionic/ios-noads', 'ionic/platforms/ios'];
$min = '15.0';

$read = function ($path) use ($root) {
    return file_get_contents("$root/$path");
};

return [
    'the Podfile of every iOS tree declares the minimum twice' => function () use ($trees, $min, $read) {
        foreach ($trees as $tree) {
            $podfile = $read("$tree/Podfile");
            t_true(strpos($podfile, "platform :ios, '$min'") !== false, "$tree: platform");
            t_true(strpos($podfile, "IPHONEOS_DEPLOYMENT_TARGET'] = '$min'") !== false, "$tree: post_install");
        }
    },

    'the Cordova config of every iOS tree asks for the same minimum' => function () use ($trees, $min, $read) {
        foreach ($trees as $tree) {
            foreach (["$tree/AppsMobileCompany/config.xml", "$tree/config.bck.xml"] as $file) {
                t_true(preg_match('/deployment-target" value="' . preg_quote($min, '/') . '"/', $read($file)) === 1, $file);
            }
        }
        t_true(preg_match('/deployment-target" value="15\.0"/', $read('ionic/config.xml')) === 1, 'ionic/config.xml');
    },

    'no Xcode project of the apps or of the Pods still targets iOS 12' => function () use ($trees, $read) {
        foreach ($trees as $tree) {
            foreach ([
                "$tree/AppsMobileCompany.xcodeproj/project.pbxproj",
                "$tree/CordovaLib/CordovaLib.xcodeproj/project.pbxproj",
                "$tree/Pods/Pods.xcodeproj/project.pbxproj",
            ] as $file) {
                $project = $read($file);
                t_equal(0, preg_match('/IPHONEOS_DEPLOYMENT_TARGET = (9|10|11|12|13|14)\.0;/', $project), "$file: old target");
                t_true(strpos($project, 'IPHONEOS_DEPLOYMENT_TARGET = 15.0;') !== false, "$file: 15.0");
            }
        }
    },

    'the script that fixes old Pods raises them to the same minimum' => function () use ($read) {
        $script = $read('bin/scripts/Settings');
        t_equal(0, preg_match('/"IPHONEOS_DEPLOYMENT_TARGET = 11\.0;", "IPHONEOS_DEPLOYMENT_TARGET = 12\.0;"/', $script), 'no 12.0');
        t_equal(2, substr_count($script, '"IPHONEOS_DEPLOYMENT_TARGET = 11.0;", "IPHONEOS_DEPLOYMENT_TARGET = 15.0;"'), 'pods and project');
    },

    'OneSignal is the release built with the iOS 15 minimum' => function () use ($read) {
        foreach (['siberian/var/apps/ionic/ios-noads', 'ionic/platforms/ios'] as $tree) {
            t_true(strpos($read("$tree/Podfile"), "pod 'OneSignalXCFramework', '5.8.0'") !== false, "$tree: Podfile");
            t_true(preg_match('/^  - OneSignalXCFramework \(5\.8\.0\):/m', $read("$tree/Podfile.lock")) === 1, "$tree: Podfile.lock");
            t_true(preg_match('/OneSignalXCFramework \(5\.8\.0\)/', $read("$tree/Pods/Manifest.lock")) === 1, "$tree: Pods/Manifest.lock");
        }
    },
];
