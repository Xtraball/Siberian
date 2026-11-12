<?php
/**
 * #876 / #902: the Android Navigator plugin asks PackageManager whether Waze and Google Maps are installed. Since
 * Android 11 that only works for packages declared in the manifest <queries> block, otherwise the answer is always
 * "not installed" and the plugin sends the user to the Play Store. Java cannot run here, so the plugin source and
 * the manifest are checked against each other.
 */
$android = dirname(__DIR__, 3) . '/siberian/var/apps/ionic/android';
$java = $android . '/app/src/main/java/com/xtraball/cordova/plugin/Navigator.java';
$manifest = $android . '/app/src/main/AndroidManifest.xml';

/** Package names the plugin looks for. */
$pluginPackages = function () use ($java) {
    $code = file_get_contents($java);
    preg_match_all('/String (?:waze|maps)Package = "([^"]+)";/', $code, $m);
    sort($m[1]);
    return $m[1];
};

return [
    '876 the plugin looks for Waze and Google Maps' => function () use ($pluginPackages) {
        t_equal(['com.google.android.apps.maps', 'com.waze'], $pluginPackages(), 'packages');
    },

    '876 the manifest declares both packages in <queries>' => function () use ($manifest, $pluginPackages) {
        $dom = new DOMDocument();
        t_true($dom->loadXML(file_get_contents($manifest)), 'manifest is well-formed');
        $declared = [];
        foreach ($dom->getElementsByTagName('queries') as $queries) {
            foreach ($queries->getElementsByTagName('package') as $package) {
                $declared[] = $package->getAttribute('android:name');
            }
        }
        sort($declared);
        t_equal($pluginPackages(), $declared, 'declared packages');
    },

    '876 the existing <queries> intents are kept' => function () use ($manifest) {
        $dom = new DOMDocument();
        $dom->loadXML(file_get_contents($manifest));
        $actions = [];
        foreach ($dom->getElementsByTagName('queries') as $queries) {
            foreach ($queries->getElementsByTagName('action') as $action) {
                $actions[] = $action->getAttribute('android:name');
            }
        }
        sort($actions);
        t_equal([
            'android.intent.action.GET_CONTENT',
            'android.intent.action.PICK',
            'android.media.action.IMAGE_CAPTURE',
            'com.android.camera.action.CROP',
        ], $actions, 'intents');
    },

    '876 the plugin metadata (android.json) lists the same packages' => function () use ($android, $pluginPackages) {
        $json = json_decode(file_get_contents($android . '/android.json'), true);
        t_true(is_array($json), 'android.json is valid');
        $found = [];
        array_walk_recursive($json, function ($value, $key) use (&$found) {
            if ($key === 'xml' && preg_match('/^<package android:name="([^"]+)" \/>$/', $value, $m)) {
                $found[] = $m[1];
            }
        });
        sort($found);
        t_equal($pluginPackages(), $found, 'android.json packages');
    },

    '876 the plugin never sends the user to the store when a maps app is installed' => function () use ($java) {
        $code = file_get_contents($java);
        // The installed branch must launch the app directly, the store intent is only for the "nothing installed" case.
        t_true(strpos($code, 'if (isWazeInstalled || isMapsInstalled) {') !== false, 'installed branch');
        $installed = substr($code, strpos($code, 'if (isWazeInstalled || isMapsInstalled) {'), strpos($code, '} else {', strpos($code, 'chooserIntent = intentGoogleNav;')) - strpos($code, 'if (isWazeInstalled || isMapsInstalled) {'));
        t_true(strpos($installed, 'market://') === false, 'no store link in the installed branch');
    },

    '876 with no maps app the plugin tries a geo: intent before the store' => function () use ($java) {
        $code = file_get_contents($java);
        $geo = strpos($code, '"geo:"');
        $market = strpos($code, 'market://details?id=" + mapsPackage');
        t_true($geo !== false && $market !== false && $geo < $market, 'geo first, store as the last resort');
    },
];
