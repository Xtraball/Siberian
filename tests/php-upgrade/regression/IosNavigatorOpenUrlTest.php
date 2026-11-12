<?php
/**
 * The iOS Navigator plugin opened Waze, Google Maps, Apple Maps and their App Store pages with the single-argument
 * -[UIApplication openURL:], deprecated since iOS 10. Apps built with the iOS 18 SDK get NO from it: nothing opens
 * and UIKit logs "BUG IN CLIENT OF UIKIT". The plugin now uses openURL:options:completionHandler:. Objective-C cannot
 * run here, so the four copies of the plugin source are checked.
 */
$root = dirname(__DIR__, 3);
$copies = [
    'siberian/var/apps/ionic/ios/AppsMobileCompany/Plugins/Navigator/CDVNavigator.m',
    'siberian/var/apps/ionic/ios-noads/AppsMobileCompany/Plugins/Navigator/CDVNavigator.m',
    'ionic/platforms/ios/AppsMobileCompany/Plugins/Navigator/CDVNavigator.m',
    'ionic/platforms/browser/cordova/plugins/Navigator/src/ios/CDVNavigator.m',
];

return [
    'no copy uses the single-argument openURL:' => function () use ($root, $copies) {
        foreach ($copies as $copy) {
            $code = preg_replace('#//[^\n]*#', '', file_get_contents("$root/$copy"));
            $all =preg_match_all('/\bopenURL:/', $code);
            $withOptions = preg_match_all('/\bopenURL:\w+ options:/', $code);
            t_equal($withOptions, $all, "$copy: every openURL: call passes options");
        }
    },

    'every copy opens URLs with openURL:options:completionHandler:' => function () use ($root, $copies) {
        foreach ($copies as $copy) {
            $code = file_get_contents("$root/$copy");
            t_true(strpos($code, 'openURL:target options:@{} completionHandler:nil') !== false, "$copy: openURL:options:completionHandler:");
            t_true(strpos($code, 'if (target != nil)') !== false, "$copy: nil URL guard");
        }
    },

    'the four copies are identical' => function () use ($root, $copies) {
        $first = file_get_contents("$root/{$copies[0]}");
        foreach (array_slice($copies, 1) as $copy) {
            t_equal(md5($first), md5(file_get_contents("$root/$copy")), "$copy same as {$copies[0]}");
        }
    },
];
