<?php
/**
 * The generated Android apps target API 36. Three things the Siberian-owned plugins did crash or silently fail from
 * Android 12/14 on:
 *  - MusicControls registered its broadcast receivers without saying if they are exported (SecurityException on
 *    Android 14 with target 34+), so the lock screen / notification controls of the media player never started;
 *  - MusicControls built mutable PendingIntents around implicit intents, which Android 14 refuses;
 *  - LocalNotification scheduled with setExact(), which throws without the SCHEDULE_EXACT_ALARM permission; the
 *    exception was swallowed, so the notification was lost.
 * Source-level checks, the plugins are Java.
 */
$root = dirname(__DIR__, 3);
$trees = ['siberian/var/apps/ionic/android', 'ionic/platforms/android'];
$mc = 'app/src/main/java/com/xtraball/musiccontrols';
$notification = 'app/src/main/java/de/appplant/cordova/plugin/notification/Notification.java';

$read = function ($path) use ($root) {
    return file_get_contents("$root/$path");
};

return [
    'MusicControls receivers are registered as not exported' => function () use ($trees, $mc, $read) {
        foreach ($trees as $tree) {
            $src = $read("$tree/$mc/MusicControls.java");
            t_true(strpos($src, 'RECEIVER_NOT_EXPORTED') !== false, "$tree: flag");
            t_equal(0, preg_match('/context\.registerReceiver\(\(BroadcastReceiver\) mMessageReceiver/', $src), "$tree: bare registerReceiver");
            t_equal(7, preg_match_all('/registerNotExported\(context, mMessageReceiver/', $src), "$tree: seven receivers");
        }
    },

    'MusicControls PendingIntents are immutable' => function () use ($trees, $mc, $read) {
        foreach ($trees as $tree) {
            foreach (['MusicControls.java', 'MusicControlsNotification.java'] as $file) {
                $src = $read("$tree/$mc/$file");
                t_equal(0, preg_match('/FLAG_MUTABLE/', $src), "$tree/$file: FLAG_MUTABLE");
                t_true(strpos($src, 'FLAG_IMMUTABLE') !== false, "$tree/$file: FLAG_IMMUTABLE");
            }
        }
    },

    'every music-controls intent is limited to the app package' => function () use ($trees, $mc, $read) {
        foreach ($trees as $tree) {
            foreach (['MusicControls.java', 'MusicControlsNotification.java'] as $file) {
                $src = $read("$tree/$mc/$file");
                $intents = preg_match_all('/Intent (\w+) = new Intent\("music-controls-[a-z-]+"\);/', $src, $m);
                t_true($intents > 0, "$tree/$file: has intents");
                foreach ($m[1] as $name) {
                    t_true(strpos($src, $name . '.setPackage(context.getPackageName());') !== false, "$tree/$file: $name");
                }
            }
        }
    },

    'local notifications fall back to an inexact alarm without the exact alarm permission' => function () use ($trees, $notification, $read) {
        foreach ($trees as $tree) {
            $src = $read("$tree/$notification");
            t_true(strpos($src, 'canScheduleExactAlarms()') !== false, "$tree: check");
            t_true(strpos($src, 'setAndAllowWhileIdle(') !== false, "$tree: fallback");
            t_equal(0, preg_match('/mgr\.setExact\(RTC/', $src), "$tree: direct setExact");
        }
    },
];
