<?php

use Siberian\Security;

$init = static function ($bootstrap) {
    Security::whitelistRoute('backoffice/advanced_configuration/save');
};