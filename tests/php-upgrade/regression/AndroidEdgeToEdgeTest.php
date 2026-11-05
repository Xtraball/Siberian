<?php
/**
 * Android layout mode "edge-to-edge" (application option android_edge_to_edge, off by default).
 *
 * - the option exists (column, form field, translated text) and is OFF by default: classic mode is unchanged
 * - the generator writes the AndroidEdgeToEdge preference only when the option is on
 * - both Android shells carry the SiberianInsets plugin, registered in config.xml, identical in both trees
 * - the CSS that makes headers and tab bars clear the system bars only applies under html.edge-to-edge, and the
 *   iOS-style safe-area variables keep a tunable --safe-area-extra
 */
$root = dirname(__DIR__, 3);
$read = function ($path) use ($root) {
    return file_get_contents("$root/$path");
};
$trees = ['siberian/var/apps/ionic/android', 'ionic/platforms/android'];
$module = 'siberian/app/sae/modules/Application';

return [
    'the option is a column, off by default, with a form field and translated texts' => function () use ($read, $module) {
        $schema = $read("$module/resources/db/schema/application.php");
        t_equal(1, preg_match("/'android_edge_to_edge' => \[\s*'type' => 'tinyint\(1\)',\s*'is_null' => true,\s*'default' => '0',/", $schema), 'column default 0');
        $form = $read("$module/Form/Behavior.php");
        t_true(strpos($form, '"android_edge_to_edge"') !== false, 'form field');
        $po = $read('siberian/languages/base/c_application.po');
        t_true(strpos($po, 'msgid "Android: draw the app behind the system bars (edge-to-edge)"') !== false, 'label in c_application.po');
    },

    'the generator adds the preference only when the option is on' => function () use ($read, $module) {
        $src = $read("$module/Model/Device/Ionic/Android/Abstract.php");
        $pos = strpos($src, 'if ((bool) $application->getAndroidEdgeToEdge())');
        t_true($pos !== false, 'conditional on the option');
        $block = substr($src, (int) $pos, 260);
        t_true(strpos($block, '<preference name=\"AndroidEdgeToEdge\" value=\"true\" />') !== false, 'preference written inside the condition');
        t_equal(1, substr_count($src, '<preference name=\"AndroidEdgeToEdge\"'), 'written in one place only');
    },

    'the shells never ship the preference themselves (classic stays the default)' => function () use ($read, $trees) {
        foreach ($trees as $tree) {
            $config = $read("$tree/app/src/main/res/xml/config.xml");
            t_equal(0, preg_match('/AndroidEdgeToEdge/', $config), "$tree: config.xml");
        }
    },

    'both Android shells carry the SiberianInsets plugin, registered at startup' => function () use ($read, $trees) {
        $reference = $read("$trees[0]/app/src/main/java/com/siberian/insets/SiberianInsets.java");
        foreach ($trees as $tree) {
            $config = $read("$tree/app/src/main/res/xml/config.xml");
            t_equal(1, preg_match('/<feature name="SiberianInsets">\s*<param name="android-package" onload="true" value="com\.siberian\.insets\.SiberianInsets" \/>/', $config), "$tree: feature");
            t_equal($reference, $read("$tree/app/src/main/java/com/siberian/insets/SiberianInsets.java"), "$tree: same source");
        }
        t_true(strpos($reference, 'preferences.getBoolean("AndroidEdgeToEdge", false)') !== false, 'does nothing in classic mode');
        t_true(strpos($reference, 'return;') !== false, 'returns early when disabled');
        foreach (['--sb-top', '--sb-right', '--sb-bottom', '--sb-left', "classList.add('edge-to-edge')"] as $needle) {
            t_true(strpos($reference, $needle) !== false, "writes $needle");
        }
    },

    'the CSS applies only under html.edge-to-edge, in every copy' => function () use ($read) {
        $copies = [
            'ionic/www/css/ionic.app.min.css',
            'ionic/platforms/android/app/src/main/assets/www/css/ionic.app.min.css',
            'ionic/platforms/ios/www/css/ionic.app.min.css',
            'ionic/platforms/browser/www/css/ionic.app.min.css',
            'siberian/var/apps/ionic/android/app/src/main/assets/www/css/ionic.app.min.css',
            'ionic/www/lib/ionic/scss/_platform.scss',
            'siberian/var/apps/browser/lib/ionic/scss/_platform.scss',
            'siberian/var/apps/overview/lib/ionic/scss/_platform.scss',
        ];
        foreach ($copies as $file) {
            $css = $read($file);
            t_true(strpos($css, '--safe-area-extra: 10px') !== false, "$file: tunable extra space");
            t_equal(0, preg_match('/env\(safe-area-inset-(top|right|bottom|left)\) \+ 10px\)/', $css), "$file: no hard-coded 10px left");
            t_true(strpos($css, 'html.edge-to-edge body.platform-android.platform-cordova:not(.fullscreen) .bar-header:not(.bar-subheader)') !== false, "$file: header rule");
            t_true(strpos($css, 'html.edge-to-edge body.platform-android.platform-cordova:not(.fullscreen) .tabs {') !== false, "$file: tab bar rule");
            // Every android rule is gated by html.edge-to-edge: each body.platform-android selector follows it.
            t_equal(substr_count($css, 'body.platform-android'), substr_count($css, 'html.edge-to-edge body.platform-android'), "$file: ungated android rule");
        }
    },
];
