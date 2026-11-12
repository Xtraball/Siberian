<?php
/**
 * Follow-up to the Navigator fix (#1051): the other iOS plugin calls of the single-argument -[UIApplication openURL:]
 * (deprecated since iOS 10, returns NO with the iOS 18 SDK, nothing opens):
 *
 * - Camera: the "Settings" button of the "access to photos denied" alert
 * - SocialSharing: the WhatsApp share
 *
 * Objective-C cannot run here, so every copy of the plugin sources is checked.
 */
$root = dirname(__DIR__, 3);
$camera = [
    'siberian/var/apps/ionic/ios/AppsMobileCompany/Plugins/Camera/CDVCamera.m',
    'siberian/var/apps/ionic/ios-noads/AppsMobileCompany/Plugins/Camera/CDVCamera.m',
    'ionic/platforms/ios/AppsMobileCompany/Plugins/Camera/CDVCamera.m',
];
$sharing = [
    'siberian/var/apps/ionic/ios/AppsMobileCompany/Plugins/SocialSharing/SocialSharing.m',
    'siberian/var/apps/ionic/ios-noads/AppsMobileCompany/Plugins/SocialSharing/SocialSharing.m',
    'ionic/platforms/ios/AppsMobileCompany/Plugins/SocialSharing/SocialSharing.m',
];

/** Every openURL: in the code (comments removed) carries options: */
$onlyWithOptions = function ($file) use ($root) {
    $code = preg_replace('#//[^\n]*#', '', file_get_contents("$root/$file"));
    $all = preg_match_all('/\bopenURL: ?[\[\w]/', $code);
    $withOptions = preg_match_all('/\bopenURL: ?[^;]*? options:@\{\} completionHandler:nil\]/', $code);
    return [$all, $withOptions];
};

return [
    'Camera and SocialSharing no longer use the single-argument openURL:' => function () use ($camera, $sharing, $onlyWithOptions) {
        // The plugin source kept for the browser platform is an older copy of the camera plugin: checked as well.
        $browserCamera = 'ionic/platforms/browser/cordova/plugins/Camera/src/ios/CDVCamera.m';
        foreach (array_merge($camera, $sharing, [$browserCamera]) as $file) {
            [$all, $withOptions] = $onlyWithOptions($file);
            t_true($all >= 1, "$file: has an openURL: call");
            t_equal($all, $withOptions, "$file: every openURL: call passes options");
        }
    },

    'the WhatsApp URL is checked before it is opened' => function () use ($sharing, $root) {
        foreach ($sharing as $file) {
            $code = file_get_contents("$root/$file");
            t_true(strpos($code, 'if (whatsappURL != nil)') !== false, "$file: nil URL guard");
        }
    },

    'the copies of each plugin stay identical' => function () use ($camera, $sharing, $root) {
        foreach ([$camera, $sharing] as $group) {
            $first = file_get_contents("$root/{$group[0]}");
            foreach (array_slice($group, 1) as $copy) {
                t_equal(md5($first), md5(file_get_contents("$root/$copy")), "$copy same as {$group[0]}");
            }
        }
    },
];
