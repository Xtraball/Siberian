<?php

use Application\Model\Cron;
use Siberian\Hook;

class_alias(Cron::class, 'ApplicationCron');
$init = static function ($bootstrap) {
    Hook::listen(
        'cache.clear.cache',
        'application_pre_init_cache',
        static function () {
            Cron::triggerRun();
        });
};
