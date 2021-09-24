<?php

use Siberian\Security;

$init = static function($bootstrap) {
    Security::whitelistRoute("system/backoffice_config_email/save");
};
