<?php

use Siberian\Feature;

$module = (new Installer_Model_Installer_Module())
    ->prepare('Session');

Feature::installCronjob(
    __('Session, garbage collector'),
    'Session_Model_Service::gc',
    0,
    12,
    -1,
    -1,
    -1,
    true,
    100,
    false,
    $module->getId()
);

// Set it once to default!
$sessionLifetime = __get('session_lifetime');
if (empty($sessionLifetime)) {
    __set('session_lifetime', 604800);
}
