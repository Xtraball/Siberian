<?php

use Siberian\Feature;

try {
    $module = (new Installer_Model_Installer_Module())
        ->prepare('Application');

    Feature::installCronjob(
        p__('application', 'Application cache pre-init.'),
        'Application_Model_Cron::run',
        -1,
        -1,
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
