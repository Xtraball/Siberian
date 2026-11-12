<?php
/**
 * Google Play's pre-review check reports "PendingIntent mutability not specified" for apps that target Android 12+
 * in BrowserActionsIntent$Builder.build, MediaSessionCompat.<init> and MediaButtonReceiver.buildMediaButtonPendingIntent.
 * They come from the old androidx.browser 1.0.0 and androidx.media 1.0.0 that com.android.support:customtabs:28.0.0
 * and androidx.legacy:legacy-support-v4:1.0.0 pull in. androidx.browser 1.4.0 and androidx.media 1.3.0 set
 * FLAG_IMMUTABLE, so both Android trees ask for newer versions, outside the section that Cordova rewrites.
 */
$root = dirname(__DIR__, 3);
$trees = ['siberian/var/apps/ionic/android', 'ionic/platforms/android'];
$minimums = [
    'androidx.browser:browser' => '1.4.0',
    'androidx.media:media' => '1.3.0',
];

// The dependencies declared after the Cordova managed section, as artifact => version.
$extraDependencies = function ($tree) use ($root) {
    $gradle = file_get_contents("$root/$tree/app/build.gradle");
    $end = strpos($gradle, '// SUB-PROJECT DEPENDENCIES END');
    t_true($end !== false, "$tree: the Cordova managed section is still marked");
    $after = substr($gradle, $end);
    preg_match_all('/implementation\s+"([a-z0-9_.\-]+:[a-z0-9_.\-]+):([0-9][0-9A-Za-z.\-]*)"/', $after, $matches, PREG_SET_ORDER);
    $found = [];
    foreach ($matches as $match) {
        $found[$match[1]] = $match[2];
    }
    return $found;
};

return [
    'both trees ask for androidx.browser and androidx.media with FLAG_IMMUTABLE' => function () use ($trees, $minimums, $extraDependencies) {
        foreach ($trees as $tree) {
            $found = $extraDependencies($tree);
            foreach ($minimums as $artifact => $minimum) {
                t_true(isset($found[$artifact]), "$tree: $artifact is declared outside the Cordova section");
                t_true(
                    isset($found[$artifact]) && version_compare($found[$artifact], $minimum, '>='),
                    "$tree: $artifact " . ($found[$artifact] ?? 'missing') . " >= $minimum"
                );
            }
        }
    },

    'both trees ask for the same versions' => function () use ($trees, $extraDependencies) {
        $first = $extraDependencies($trees[0]);
        foreach (array_slice($trees, 1) as $tree) {
            $found = $extraDependencies($tree);
            foreach (['androidx.browser:browser', 'androidx.media:media'] as $artifact) {
                t_equal($first[$artifact] ?? null, $found[$artifact] ?? null, "$tree: $artifact same as {$trees[0]}");
            }
        }
    },
];
