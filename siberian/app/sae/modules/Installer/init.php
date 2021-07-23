<?php

use Siberian\Security;

/**
 * @param $bootstrap
 */
$init = static function ($bootstrap) {
    Security::allowExtension('zip');
    Security::allowExtension('sib');
    Security::allowExtension('json');
};