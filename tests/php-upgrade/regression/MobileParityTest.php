<?php
/**
 * Android and iOS parity (rule 7 of AGENTS.md, docs/mobile-parity.md): what can be checked from the files.
 *
 * - the two platforms carry the same plugins, except the ones that are different on purpose;
 * - a plugin that must be the same on both is at the same version;
 * - each shell tree and its dev copy (ionic/platforms) agree;
 * - the page that records the differences exists and the rule points to it.
 */
$root = dirname(__DIR__, 3);

/** Plugin id => version, from `module.exports.metadata` of a cordova_plugins.js. */
$metadata = function ($path) use ($root) {
    $src = file_get_contents("$root/$path");
    t_true(preg_match('/module\.exports\.metadata = \{(.*?)\n  \};/s', $src, $m) === 1, "$path: metadata block");
    preg_match_all('/"([A-Za-z0-9_.-]+)":\s*"([^"]+)"/', $m[1], $all, PREG_SET_ORDER);
    $out = [];
    foreach ($all as $pair) {
        $out[$pair[1]] = $pair[2];
    }
    return $out;
};

$android = 'siberian/var/apps/ionic/android/app/src/main/assets/www/cordova_plugins.js';
$androidDev = 'ionic/platforms/android/app/src/main/assets/www/cordova_plugins.js';
$ios = 'siberian/var/apps/ionic/ios-noads/www/cordova_plugins.js';
$iosDev = 'ionic/platforms/ios/www/cordova_plugins.js';
$iosAds = 'siberian/var/apps/ionic/ios/www/cordova_plugins.js';

// Different on purpose, see docs/mobile-parity.md
$androidOnly = ['IonicWebview'];
$iosOnly = ['IosXhr'];
$adsOnly = ['Admob', 'InAppPurchase', 'Mediation'];

// Shared on purpose, the same version on both
$sameVersion = ['Camera'];

return [
    'Android and iOS carry the same plugins, except the ones that differ on purpose' => function () use ($metadata, $android, $ios, $androidOnly, $iosOnly) {
        $a = array_keys($metadata($android));
        $i = array_keys($metadata($ios));
        $missingOnIos = array_values(array_diff($a, $i, $androidOnly));
        $missingOnAndroid = array_values(array_diff($i, $a, $iosOnly));
        t_equal([], $missingOnIos, 'a plugin of the Android shell the iOS shell does not have (add it, or list it in docs/mobile-parity.md and in this test)');
        t_equal([], $missingOnAndroid, 'a plugin of the iOS shell the Android shell does not have (add it, or list it in docs/mobile-parity.md and in this test)');
    },

    'the iOS ads template has the plugins of the one without ads, plus the ads' => function () use ($metadata, $ios, $iosAds, $adsOnly) {
        $noAds = array_keys($metadata($ios));
        $ads = array_keys($metadata($iosAds));
        t_equal([], array_values(array_diff($noAds, $ads)), 'plugins the ads template lacks');
        t_equal([], array_values(array_diff($ads, $noAds, $adsOnly)), 'plugins only the ads template has');
    },

    'plugins shared on purpose are at the same version on both platforms' => function () use ($metadata, $android, $ios, $sameVersion) {
        $a = $metadata($android);
        $i = $metadata($ios);
        foreach ($sameVersion as $plugin) {
            t_equal($a[$plugin] ?? null, $i[$plugin] ?? null, "$plugin version");
        }
    },

    'each shell tree and its dev copy record the same plugins' => function () use ($metadata, $android, $androidDev, $ios, $iosDev) {
        t_equal($metadata($android), $metadata($androidDev), 'Android: siberian/var/apps/ionic/android vs ionic/platforms/android');
        t_equal($metadata($ios), $metadata($iosDev), 'iOS: ios-noads vs ionic/platforms/ios');
    },

    'the parity rule and its page exist' => function () use ($root) {
        $page = file_get_contents("$root/docs/mobile-parity.md");
        t_true(strpos($page, 'Different on purpose') !== false, 'differences on purpose');
        t_true(strpos($page, 'Known gaps to close') !== false, 'known gaps');
        $agents = file_get_contents("$root/AGENTS.md");
        t_true(strpos($agents, 'docs/mobile-parity.md') !== false, 'AGENTS.md points to the page');
    },
];
