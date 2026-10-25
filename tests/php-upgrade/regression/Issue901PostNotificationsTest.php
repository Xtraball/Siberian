<?php
/**
 * #901: Android 13+ needs POST_NOTIFICATIONS to show notifications. The generator adds it to the manifest only for
 * apps that have the Push feature, other apps get exactly the manifest they get today.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
$manifestFile = $siberian . '/var/apps/ionic/android/app/src/main/AndroidManifest.xml';
$abstractFile = $siberian . '/app/sae/modules/Application/Model/Device/Ionic/Android/Abstract.php';

/** The replacement map of the option, read from the generator so the test follows the code. */
$pushReplacements = function () use ($abstractFile) {
    $code = file_get_contents($abstractFile);
    $ok = preg_match('/if \(\$hasPush[^{]*\) \{\s*\$replacements = array_merge\(\$replacements, (\[.*?\])\);/s', $code, $m);
    t_true($ok, 'the push block exists in androidManifest()');
    return eval('return ' . $m[1] . ';');
};

$permissions = function (string $xml) {
    $dom = new DOMDocument();
    t_true($dom->loadXML($xml), 'manifest is well-formed');
    $names = [];
    foreach ($dom->getElementsByTagName('uses-permission') as $node) {
        $names[] = $node->getAttribute('android:name');
    }
    sort($names);
    return $names;
};

return [
    '901 the generator looks for the Push2 feature by its code' => function () use ($siberian, $abstractFile) {
        $feature = json_decode(file_get_contents($siberian . '/app/sae/modules/Push2/features/push2/feature.json'), true);
        t_equal('push2', $feature['code'], 'feature code');
        t_true(strpos(file_get_contents($abstractFile), "find('push2', 'code')") !== false, 'generator uses that code');
    },

    '901 apps without push get today\'s manifest' => function () use ($manifestFile, $permissions) {
        $manifest = file_get_contents($manifestFile);
        t_true(!in_array('android.permission.POST_NOTIFICATIONS', $permissions($manifest), true), 'not in the template');
    },

    '901 removing another permission does not matter' => function () use ($manifestFile, $permissions, $pushReplacements) {
        $manifest = str_replace('<uses-permission android:name="android.permission.CAMERA" />', '', file_get_contents($manifestFile));
        $after = $permissions(strtr($manifest, $pushReplacements()));
        t_true(in_array('android.permission.POST_NOTIFICATIONS', $after, true), 'still added without CAMERA');
    },

    '901 apps with push get exactly one more permission' => function () use ($manifestFile, $permissions, $pushReplacements) {
        $manifest = file_get_contents($manifestFile);
        $before = $permissions($manifest);
        $after = $permissions(strtr($manifest, $pushReplacements()));
        t_equal(['android.permission.POST_NOTIFICATIONS'], array_values(array_diff($after, $before)), 'added');
        t_equal([], array_values(array_diff($before, $after)), 'nothing removed');
        t_equal(count($before) + 1, count($after), 'once');
    },

    '901 the anchor is the closing tag, not another permission' => function () use ($pushReplacements) {
        t_equal(['</manifest>'], array_keys($pushReplacements()), 'anchor');
    },

    '901 the anchor exists once in the template' => function () use ($manifestFile, $pushReplacements) {
        $manifest = file_get_contents($manifestFile);
        foreach (array_keys($pushReplacements()) as $anchor) {
            t_equal(1, substr_count($manifest, $anchor), $anchor);
        }
    },

    '901 the permission is only added inside the push block' => function () use ($abstractFile) {
        $code = file_get_contents($abstractFile);
        t_equal(1, substr_count($code, 'android.permission.POST_NOTIFICATIONS'), 'mentioned once');
        t_true(strpos($code, 'if ($hasPush') < strpos($code, 'android.permission.POST_NOTIFICATIONS'), 'after the guard');
        t_true(strpos($code, "stripos(file_get_contents(\$manifestFile), 'POST_NOTIFICATIONS') === false") !== false, 'skipped when the template has it');
    },
];
