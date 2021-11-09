<?php

use Siberian\Feature;

try {
    $module = (new Installer_Model_Installer_Module())
        ->prepare('Backoffice');

    try {
        $jobs = (new \Cron_Model_Cron())->findAll(['module_id' => $module->getId()]);
        foreach ($jobs as $job) {
            $job->delete();
        }
    } catch (\Exception $e) {
        // Clean up for services!
    }

    Feature::installCronjob(
        p__('backoffice', 'Backoffice, restoreapps'),
        '\\\\Backoffice\\\\Model\\\\Tools::watch',
        -1,
        -1,
        -1,
        -1,
        -1,
        true,
        50,
        true,
        $module->getId()
    );
} catch (\Exception $e) {
    // noop!
}