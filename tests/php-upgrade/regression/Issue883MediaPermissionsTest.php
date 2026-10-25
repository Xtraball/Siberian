<?php
/**
 * #883 / #852: the "disable_media_permissions" Android option removes READ_MEDIA_IMAGES and READ_MEDIA_VIDEO from
 * the generated manifest. Off by default, so existing apps keep every permission they have today.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
$manifestFile = $siberian . '/var/apps/ionic/android/app/src/main/AndroidManifest.xml';
$abstractFile = $siberian . '/app/sae/modules/Application/Model/Device/Ionic/Android/Abstract.php';

/** The replacement map of the option, read from the generator so the test follows the code. */
$mediaReplacements = function () use ($abstractFile) {
    $code = file_get_contents($abstractFile);
    $ok = preg_match('/if \(\$disableMedia\) \{\s*\$replacements = array_merge\(\$replacements, \[(.*?)\]\);/s', $code, $m);
    t_true($ok, 'the option block exists in androidManifest()');
    preg_match_all('/"(<uses-permission[^"\\\\]*(?:\\\\.[^"\\\\]*)*\/>)" => ""/', $m[1], $all);
    $out = [];
    foreach ($all[1] as $line) {
        $out[] = stripcslashes($line);
    }
    return $out;
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
    '883 the option targets exactly the two media permissions' => function () use ($mediaReplacements) {
        t_equal([
            '<uses-permission android:name="android.permission.READ_MEDIA_IMAGES" />',
            '<uses-permission android:name="android.permission.READ_MEDIA_VIDEO" />',
        ], $mediaReplacements(), 'replacements');
    },

    '883 each replaced line exists once in the template manifest' => function () use ($mediaReplacements, $manifestFile) {
        $manifest = file_get_contents($manifestFile);
        foreach ($mediaReplacements() as $line) {
            t_equal(1, substr_count($manifest, $line), $line);
        }
    },

    '883 with the option on only the two permissions go away' => function () use ($mediaReplacements, $manifestFile, $permissions) {
        $manifest = file_get_contents($manifestFile);
        $before = $permissions($manifest);
        $after = $permissions(strtr($manifest, array_fill_keys($mediaReplacements(), '')));
        t_equal(
            ['android.permission.READ_MEDIA_IMAGES', 'android.permission.READ_MEDIA_VIDEO'],
            array_values(array_diff($before, $after)),
            'removed'
        );
        t_equal([], array_values(array_diff($after, $before)), 'nothing added');
        t_true(in_array('android.permission.CAMERA', $after, true), 'camera kept');
        t_true(in_array('android.permission.ACCESS_FINE_LOCATION', $after, true), 'location kept');
    },

    '883 with the option off the manifest is left untouched' => function () use ($abstractFile) {
        $code = file_get_contents($abstractFile);
        t_true(strpos($code, 'filter_var($application->getDisableMediaPermissions(), FILTER_VALIDATE_BOOLEAN)') !== false, 'read as a boolean');
        t_equal(1, preg_match('/if \(\$disableMedia\) \{/', $code), 'replacements only inside the option block');
    },

    '883 the option is stored off by default and exposed in the Android form' => function () use ($siberian) {
        $schemas = [];
        require $siberian . '/app/sae/modules/Application/resources/db/schema/application.php';
        t_equal('tinyint(1)', $schemas['application']['disable_media_permissions']['type'], 'type');
        t_equal('0', $schemas['application']['disable_media_permissions']['default'], 'default off');
        $form = file_get_contents($siberian . '/app/sae/modules/Application/Form/Android.php');
        t_true(strpos($form, '"disable_media_permissions"') !== false, 'form field');
        $controller = file_get_contents($siberian . '/app/sae/modules/Application/controllers/Customization/Publication/InfosController.php');
        t_true(strpos($controller, "\$params['disable_media_permissions'] ?? false") !== false, 'missing value means off');
    },
];
