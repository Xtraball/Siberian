<?php

use Siberian\Hook;

$init = static function ($bootstrap) {
    Hook::listen(
        'cache.clear.cache',
        'application_pre_init_cache',
        static function () {
            Application_Model_Cron::triggerRun();
        });
};
