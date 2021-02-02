<?php

use Siberian\Hook;
use Siberian\Security;

$init = static function ($bootstrap) {
    Hook::listen(
        'cache.clear.cache',
        'application_pre_init_cache',
        static function () {
            Application_Model_Cron::triggerRun();
        });

    // Ensure apk, aab & pks are always allowed!
    Security::allowExtension('apk');
    Security::allowExtension('aab');
    Security::allowExtension('pks');
};
