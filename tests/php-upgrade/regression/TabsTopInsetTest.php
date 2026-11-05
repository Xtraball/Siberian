<?php
/**
 * `.has-header.has-subheader.has-tabs-top` sits below the status bar, header, subheader and tab bar: the status bar
 * part is the TOP safe-area inset. It used the right inset, so on a notched iPhone in portrait (right inset 0) the
 * content started about 47px too high, and in landscape it moved by the wrong amount.
 */
$root = dirname(__DIR__, 3);
$files = [
    'ionic/platforms/android/app/src/main/assets/www/css/ionic.app.min.css',
    'ionic/platforms/android/app/src/main/assets/www/lib/ionic/css/ionic.css',
    'ionic/platforms/android/app/src/main/assets/www/lib/ionic/css/ionic.min.css',
    'ionic/platforms/android/app/src/main/assets/www/lib/ionic/scss/_platform.scss',
    'ionic/platforms/browser/www/css/ionic.app.min.css',
    'ionic/platforms/browser/www/lib/ionic/css/ionic.css',
    'ionic/platforms/browser/www/lib/ionic/css/ionic.min.css',
    'ionic/platforms/browser/www/lib/ionic/scss/_platform.scss',
    'ionic/platforms/ios/www/css/ionic.app.min.css',
    'ionic/platforms/ios/www/lib/ionic/css/ionic.css',
    'ionic/platforms/ios/www/lib/ionic/css/ionic.min.css',
    'ionic/platforms/ios/www/lib/ionic/scss/_platform.scss',
    'ionic/www/css/ionic.app.min.css',
    'ionic/www/lib/ionic/css/ionic.css',
    'ionic/www/lib/ionic/css/ionic.min.css',
    'ionic/www/lib/ionic/scss/_platform.scss',
    'siberian/var/apps/browser/lib/ionic/css/ionic.min.css',
    'siberian/var/apps/browser/lib/ionic/scss/_platform.scss',
    'siberian/var/apps/ionic/android/app/src/main/assets/www/css/ionic.app.min.css',
    'siberian/var/apps/ionic/android/app/src/main/assets/www/lib/ionic/css/ionic.css',
    'siberian/var/apps/ionic/android/app/src/main/assets/www/lib/ionic/css/ionic.min.css',
    'siberian/var/apps/ionic/android/app/src/main/assets/www/lib/ionic/scss/_platform.scss',
    'siberian/var/apps/ionic/ios-noads/www/lib/ionic/css/ionic.min.css',
    'siberian/var/apps/ionic/ios/www/lib/ionic/css/ionic.min.css',
    'siberian/var/apps/overview/lib/ionic/css/ionic.min.css',
    'siberian/var/apps/overview/lib/ionic/scss/_platform.scss',
    'ionic/www/dist/app.bundle.min.css',
    'ionic/platforms/android/app/src/main/assets/www/dist/app.bundle.min.css',
    'ionic/platforms/browser/www/dist/app.bundle.min.css',
    'ionic/platforms/ios/www/dist/app.bundle.min.css',
    'siberian/var/apps/browser/dist/app.bundle.min.css',
    'siberian/var/apps/ionic/android/app/src/main/assets/www/dist/app.bundle.min.css',
    'siberian/var/apps/ionic/ios-noads/www/dist/app.bundle.min.css',
    'siberian/var/apps/ionic/ios/www/dist/app.bundle.min.css',
    'siberian/var/apps/overview/dist/app.bundle.min.css',
];

return [
    'the has-tabs-top rule with a subheader uses the top inset, in every copy' => function () use ($root, $files) {
        t_true(count($files) >= 30, 'found the copies (' . count($files) . ')');
        $checked = 0;
        foreach ($files as $file) {
            $src = file_get_contents("$root/$file");
            t_equal(0, preg_match('/137px ?\+ ?(constant|env|var)\(-{0,2}safe-area-inset-right/', $src), "$file: 137px + right inset");
            t_equal(0, preg_match('/\$tabs-height\} \+ var\(--safe-area-inset-right/', $src), "$file: scss right inset");
            if (preg_match('/137px ?\+ ?(constant|env|var)\(-{0,2}safe-area-inset-top/', $src)
                || strpos($src, '$tabs-height} + var(--safe-area-inset-top)') !== false) {
                $checked++;
            }
        }
        t_true($checked >= 30, "the rule exists with the top inset in $checked copies");
    },
];
