<?php
/**
 * The iOS Navigator plugin asks canOpenURL: whether Waze, Google Maps and Apple Maps can be opened. Since iOS 9
 * canOpenURL: answers NO for every scheme that is not listed in LSApplicationQueriesSchemes, so a missing scheme sends
 * the user to the App Store even when the app is installed. "maps" was missing: the Apple Maps button always opened
 * the App Store page of Apple Maps, and App Review rejects apps for it (guideline 4.0). Objective-C cannot run here,
 * so the plugin source and every Info.plist are checked against each other.
 */
$root = dirname(__DIR__, 3);
$trees = ['siberian/var/apps/ionic/ios', 'siberian/var/apps/ionic/ios-noads', 'ionic/platforms/ios'];

/** Schemes passed to canOpenURL: in the plugin source of a tree. */
$queriedSchemes = function ($tree) use ($root) {
    $code = file_get_contents("$root/$tree/AppsMobileCompany/Plugins/Navigator/CDVNavigator.m");
    preg_match_all('/canOpenURL:\[NSURL URLWithString:@"([a-z0-9+.\-]+):\/\/"\]/i', $code, $m);
    $schemes = array_unique($m[1]);
    sort($schemes);
    return $schemes;
};

/** Strings in the LSApplicationQueriesSchemes array of an Info.plist. */
$declaredSchemes = function ($file) {
    $dom = new DOMDocument();
    t_true(@$dom->loadXML(file_get_contents($file)) === true, "$file is well-formed");
    $xpath = new DOMXPath($dom);
    $schemes = [];
    foreach ($xpath->query('//dict/key[.="LSApplicationQueriesSchemes"]/following-sibling::array[1]/string') as $node) {
        $schemes[] = $node->textContent;
    }
    return $schemes;
};

return [
    'the plugin asks for Waze, Google Maps and Apple Maps' => function () use ($trees, $queriedSchemes) {
        foreach ($trees as $tree) {
            t_equal(['comgooglemaps-x-callback', 'maps', 'waze'], $queriedSchemes($tree), "$tree: schemes");
        }
    },

    'every Info.plist declares the schemes the plugin asks for' => function () use ($root, $trees, $queriedSchemes, $declaredSchemes) {
        foreach ($trees as $tree) {
            $declared = $declaredSchemes("$root/$tree/AppsMobileCompany/AppsMobileCompany-Info.plist");
            foreach ($queriedSchemes($tree) as $scheme) {
                t_true(in_array($scheme, $declared, true), "$tree: $scheme in LSApplicationQueriesSchemes");
            }
        }
    },

    'the existing schemes are kept' => function () use ($root, $trees, $declaredSchemes) {
        foreach ($trees as $tree) {
            $declared = $declaredSchemes("$root/$tree/AppsMobileCompany/AppsMobileCompany-Info.plist");
            foreach (['waze', 'comgooglemaps', 'comgooglemaps-x-callback'] as $scheme) {
                t_true(in_array($scheme, $declared, true), "$tree: $scheme kept");
            }
        }
    },

    'the plugin.xml and ios.json copies declare the same schemes' => function () use ($root) {
        $plugin = file_get_contents("$root/ionic/platforms/browser/cordova/plugins/Navigator/plugin.xml");
        t_true(strpos($plugin, '<string>maps</string>') !== false, 'plugin.xml declares maps');
        $json = json_decode(file_get_contents("$root/ionic/platforms/ios/ios.json"), true);
        t_true(is_array($json), 'ios.json is valid JSON');
        t_true(strpos(json_encode($json), '<string>maps<\/string>') !== false, 'ios.json declares maps');
    },
];
