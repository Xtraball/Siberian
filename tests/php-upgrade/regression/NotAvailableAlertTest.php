<?php
/**
 * $rootScope.isNotAvailableOffline(with_alert) and $rootScope.isNotAvailableInOverview(with_alert), as documented on
 * developers.siberiancms.com/stack/utilities: the alert is opened by default, with_alert = false only answers.
 * They used to ignore the argument and always open the alert.
 */
$root = dirname(__DIR__, 3);
$read = function ($path) use ($root) {
    return file_get_contents("$root/$path");
};

$bundles = [
    'ionic/www/dist/app.min.js',
    'ionic/platforms/android/app/src/main/assets/www/dist/app.min.js',
    'ionic/platforms/ios/www/dist/app.min.js',
    'ionic/platforms/browser/www/dist/app.min.js',
    'siberian/var/apps/browser/dist/app.min.js',
    'siberian/var/apps/overview/dist/app.min.js',
    'siberian/var/apps/ionic/android/app/src/main/assets/www/dist/app.min.js',
    'siberian/var/apps/ionic/ios/www/dist/app.min.js',
    'siberian/var/apps/ionic/ios-noads/www/dist/app.min.js',
];

return [
    'the helpers take with_alert, and only an explicit false silences the alert' => function () use ($read) {
        $src = $read('ionic/www/js/app.js');
        foreach (['isNotAvailableOffline', 'isNotAvailableInOverview'] as $name) {
            $ok = preg_match('/\$rootScope\.' . $name . ' = function \(withAlert\) \{\s*if \([^)]*\) \{\s*if \(withAlert !== false\) \{\s*Dialog\.alert\(/', $src);
            t_equal(1, $ok, "$name: alert only when withAlert is not false");
        }
    },

    'the packed app of every copy has the same behaviour' => function () use ($read, $bundles) {
        foreach ($bundles as $bundle) {
            $src = $read($bundle);
            t_true(strpos($src, 'isNotAvailableOffline=function(e){return!!$rootScope.isOffline&&(!1!==e&&Dialog.alert(') !== false, "$bundle: offline");
            t_true(strpos($src, 'isNotAvailableInOverview=function(e){return!!isOverview&&(!1!==e&&Dialog.alert(') !== false, "$bundle: overview");
        }
    },
];
