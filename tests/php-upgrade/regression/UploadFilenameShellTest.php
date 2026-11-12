<?php
/**
 * The client filename of an uploaded APK/AAB reaches `rm -f` through exec().
 * It must be escaped, and reduced to a basename, with the same rm -f result
 * for ordinary names.
 */

function t_upload_rm($dir, $clientName)
{
    $name = basename(str_replace('\\', '/', (string) $clientName));
    $destination = $dir . '/' . $name;
    exec('rm -f ' . escapeshellarg($destination));
    return $destination;
}

return [
    'a quote in the filename cannot run a command' => function () {
        $dir = sys_get_temp_dir() . '/sib-upl-' . getmypid();
        mkdir($dir, 0775, true);
        $marker = $dir . '/pwned';
        $evil = "x'; touch {$marker}; echo '.apk";
        $destination = t_upload_rm($dir, $evil);
        t_true(!is_file($marker), 'injected command must not run');
        @rmdir($dir);
    },
    'an existing file with an ordinary name is still removed' => function () {
        $dir = sys_get_temp_dir() . '/sib-upl2-' . getmypid();
        mkdir($dir, 0775, true);
        file_put_contents($dir . '/app one.apk', 'x');
        t_upload_rm($dir, 'app one.apk');
        t_true(!is_file($dir . '/app one.apk'), 'file replaced as before');
        @rmdir($dir);
    },
    'path parts of the client name are dropped' => function () {
        $dir = sys_get_temp_dir();
        t_equal($dir . '/passwd.apk', t_upload_rm($dir, '../../etc/passwd.apk'));
        t_equal($dir . '/b.apk', t_upload_rm($dir, 'a\\b.apk'));
    },
    'the controller uses the escaped form' => function () {
        $src = file_get_contents(dirname(__DIR__, 3) .
            '/siberian/app/sae/modules/Application/controllers/Backoffice/IosautopublishController.php');
        t_true(strpos($src, "exec(\"rm -f '{\$destination") === false, 'raw interpolation is gone');
        t_equal(2, substr_count($src, "exec('rm -f ' . escapeshellarg("));
    },
];
