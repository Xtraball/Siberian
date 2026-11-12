<?php

use Siberian\Feature;

// Once a day, at a time that differs per installation so the downloader never gets a thundering herd.
try {
    $module = (new Installer_Model_Installer_Module())
        ->prepare('Installer');

    $seed = crc32((string) (__get('siberiancms_key') ?: ($_SERVER['HTTP_HOST'] ?? 'siberian')));

    Feature::installCronjob(
        p__('backoffice', 'Module updates check.'),
        'Installer_Model_ModuleUpdates::cron',
        $seed % 60,
        2 + ($seed % 4),
        -1,
        -1,
        -1,
        true,
        100,
        true,
        $module->getId()
    );
} catch (\Exception $e) {
    // Silent!
}
