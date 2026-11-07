<?php
/**
 * #964: on Android 15+ (targetSdk 36) the system bars, and the keyboard, were subtracted twice.
 *
 * - CordovaActivity (cordova-android 14) keeps the WebView clear of the bars and of the keyboard with margins
 *   from the window insets
 * - the statusbar plugin also called StatusBarViewHelper.assistActivity (CB-13300), which resized the same root
 *   layout to the visible display frame: a blank area under the tab bar, and with the keyboard open the WebView
 *   shrunk to a strip (measured on a Pixel 9a, Android 17)
 *
 * The plugin must not call the helper any more, in both Android shells, and CordovaActivity must keep handling
 * the bars and the keyboard, otherwise removing the helper would not be safe.
 */
$root = dirname(__DIR__, 3);
$read = function ($path) use ($root) {
    return file_get_contents("$root/$path");
};
$trees = ['siberian/var/apps/ionic/android', 'ionic/platforms/android'];

return [
    '964 the statusbar plugin does not resize the root layout (both Android shells)' => function () use ($read, $trees) {
        foreach ($trees as $tree) {
            $src = $read("$tree/app/src/main/java/org/apache/cordova/statusbar/StatusBar.java");
            // Strip comments, the explanation may name the helper.
            $code = preg_replace('#//[^\n]*|/\*.*?\*/#s', '', $src);
            t_true(strpos($code, 'public void initialize(') !== false, "$tree: StatusBar.initialize found");
            t_equal(0, substr_count($code, 'assistActivity('), "$tree: no StatusBarViewHelper.assistActivity call");
        }
    },

    '964 CordovaActivity keeps the WebView clear of the bars and of the keyboard (both Android shells)' => function () use ($read, $trees) {
        foreach ($trees as $tree) {
            $src = $read("$tree/CordovaLib/src/org/apache/cordova/CordovaActivity.java");
            t_true(strpos($src, 'insets.getInsets(WindowInsetsCompat.Type.ime())') !== false, "$tree: reads the keyboard inset");
            t_true(strpos($src, 'Math.max(bars.bottom, imeInsets.bottom)') !== false, "$tree: bottom margin covers bars and keyboard");
            t_true(strpos($src, 'webViewParams.setMargins(left, top, right, bottom)') !== false, "$tree: applies the margins to the WebView");
        }
    },
];
