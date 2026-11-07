<?php
/**
 * #964: on Android 15+ (targetSdk 36) the exported apps showed a blank strip under the content, and with the keyboard
 * open the WebView shrank to a few pixels. Two mechanisms subtracted the system bars: StatusBarViewHelper.assistActivity()
 * shrank the root layout to the visible frame, and CordovaActivity applies the system bar, cutout and keyboard insets to
 * the WebView as margins. The StatusBar plugin no longer calls the helper; CordovaActivity keeps doing the job.
 * Java cannot run here, so the sources are checked.
 */
$root = dirname(__DIR__, 3);
$trees = ['siberian/var/apps/ionic/android', 'ionic/platforms/android'];

/** Source without comments, so a commented-out call does not count. */
$code = function ($path) use ($root) {
    $src = file_get_contents("$root/$path");
    $src = preg_replace('#/\*.*?\*/#s', '', $src);
    return preg_replace('#//[^\n]*#', '', $src);
};

return [
    '964 the StatusBar plugin no longer calls StatusBarViewHelper.assistActivity' => function () use ($trees, $code) {
        foreach ($trees as $tree) {
            $java = $code("$tree/app/src/main/java/org/apache/cordova/statusbar/StatusBar.java");
            t_equal(0, preg_match('/StatusBarViewHelper\s*\.\s*assistActivity\s*\(/', $java), "$tree: no assistActivity call");
            t_true(strpos($java, 'setStatusBarBackgroundColor(preferences.getString("StatusBarBackgroundColor"') !== false, "$tree: the rest of initialize() is kept");
        }
    },

    '964 CordovaActivity still applies the system bar and keyboard insets to the WebView' => function () use ($trees, $code) {
        foreach ($trees as $tree) {
            $java = $code("$tree/CordovaLib/src/org/apache/cordova/CordovaActivity.java");
            t_true(strpos($java, 'setOnApplyWindowInsetsListener') !== false, "$tree: insets listener");
            t_true(strpos($java, 'WindowInsetsCompat.Type.systemBars()') !== false, "$tree: system bars");
            t_true(strpos($java, 'WindowInsetsCompat.Type.ime()') !== false, "$tree: keyboard");
        }
    },

    '964 both trees carry the same StatusBar plugin' => function () use ($root, $trees) {
        $path = 'app/src/main/java/org/apache/cordova/statusbar/StatusBar.java';
        t_equal(md5_file("$root/{$trees[0]}/$path"), md5_file("$root/{$trees[1]}/$path"), 'StatusBar.java identical');
    },
];
