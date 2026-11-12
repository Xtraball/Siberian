<?php
/**
 * PHP 8.2 is the lowest supported version (it carries the security fix), PHP 8.5 is the recommended one.
 */
require_once dirname(__DIR__, 3) . '/siberian/lib/Siberian/PhpSupport.php';

use Siberian\PhpSupport;

return [
    'the versions below 8.2 are not supported' => function () {
        foreach (['5.6.40', '7.4.33', '8.0.30', '8.1.31'] as $v) {
            t_true(!PhpSupport::isSupported($v), "$v is not supported");
        }
    },

    '8.2 and above are supported' => function () {
        foreach (['8.2.0', '8.2.35', '8.3.1', '8.4.0', '8.5.0'] as $v) {
            t_true(PhpSupport::isSupported($v), "$v is supported");
        }
    },

    'only 8.5 and above are recommended' => function () {
        foreach (['8.2.35', '8.3.20', '8.4.9'] as $v) {
            t_true(!PhpSupport::isRecommended($v), "$v is not the recommended one");
        }
        foreach (['8.5.0', '8.5.2', '8.6.0'] as $v) {
            t_true(PhpSupport::isRecommended($v), "$v is recommended");
        }
    },

    'a distribution suffix does not change the answer' => function () {
        t_true(PhpSupport::isSupported('8.2.35-1+deb13u1'), 'suffix, supported');
        t_true(!PhpSupport::isRecommended('8.4.9-1+deb13u1'), 'suffix, not recommended');
    },

    'the installer refuses an old PHP' => function () {
        $src = file_get_contents(dirname(__DIR__, 3) . '/siberian/app/sae/modules/Installer/Model/Installer.php');
        t_true(strpos($src, 'PhpSupport::isSupported()') !== false, 'hasRequiredPhpVersion uses PhpSupport');
        t_true(strpos($src, "'5.6'") === false, 'the 5.6 check is gone');
    },
];
