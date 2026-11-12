<?php
/**
 * The FTP fallback of the module installer was removed (sunset): nothing may call it again.
 */

function t_ftp_files()
{
    $root = dirname(__DIR__, 3) . '/siberian';
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/app/sae', FilesystemIterator::SKIP_DOTS));
    $files = [];
    foreach ($it as $f) {
        $p = $f->getPathname();
        $isCode = substr($p, -4) === '.php' || substr($p, -6) === '.phtml' || substr($p, -3) === '.js';
        if ($isCode && strpos($p, '/node_modules/') === false && strpos($p, '/libraries/') === false) {
            $files[] = $p;
        }
    }
    return $files;
}

return [
    'the Siberian_Ftp class is gone' => function () {
        t_true(!is_file(dirname(__DIR__, 3) . '/siberian/lib/Siberian/Ftp.php'));
    },
    'nothing in the app refers to Siberian_Ftp, saveftp or the ftp config keys' => function () {
        $hits = [];
        foreach (t_ftp_files() as $p) {
            if (preg_match('/Siberian_Ftp|saveftp|saveFtp|ftp_host|ftp_username/', file_get_contents($p))) {
                $hits[] = $p;
            }
        }
        t_equal([], $hits);
    },
    'the module page keeps the list of paths to fix and the Retry button' => function () {
        $tpl = file_get_contents(dirname(__DIR__, 3) . '/siberian/app/sae/design/desktop/backoffice/template/installer/module.phtml');
        t_true(strpos($tpl, 'permissions.error_message') !== false);
        t_true(strpos($tpl, 'ng-click="checkPermissions();"') !== false);
        t_true(stripos($tpl, 'ftp') === false, 'no FTP form left');
    },
];
