<?php
/**
 * #965: with the "disable_media_permissions" option the manifest has no READ_MEDIA_* permission. CameraLauncher used to
 * request them anyway, the system refuses at once (PERMISSION_DENIED_ERROR) and the library picker never opened.
 * It now asks only for the permissions the manifest declares, so the call is unchanged when they are declared.
 */
$file = dirname(__DIR__, 3) . '/siberian/var/apps/ionic/android/app/src/main/java/org/apache/cordova/camera/CameraLauncher.java';

return [
    '965 the three request sites filter on the declared permissions' => function () use ($file) {
        $code = file_get_contents($file);
        t_true(strpos($code, 'declaredOnly(getPermissions(true, mediaType))') !== false, 'storage permissions filtered');
        t_equal(2, substr_count($code, 'declaredOnly(getPermissions(true, mediaType))'), 'library pick and take picture');
        t_true(strpos($code, 'declaredOnly(getPermissions(false, mediaType))') !== false, 'camera + storage request filtered');
    },

    '965 no permission request is built from the unfiltered list' => function () use ($file) {
        $code = file_get_contents($file);
        preg_match_all('/requestPermissions\([^;]*;/', $code, $all);
        foreach ($all[0] as $call) {
            t_true(!(preg_match('/getPermissions\(/', $call) && strpos($call, 'declaredOnly(') === false), $call);
        }
    },

    '965 declaredOnly keeps the old behaviour when the manifest cannot be read' => function () use ($file) {
        $code = file_get_contents($file);
        t_true((bool) preg_match('/if \(declared == null\) \{\s*return permissions;/', $code), 'null manifest list means no filtering');
    },

    '965 the camera permission is still part of the camera request' => function () use ($file) {
        $code = file_get_contents($file);
        t_true(strpos($code, 'permissions.add(Manifest.permission.CAMERA);') !== false, 'CAMERA still added when not storageOnly');
    },
];
