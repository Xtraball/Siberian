<?php

use Siberian\Exporter;
use Siberian\Security;

$init = function($bootstrap) {
    # Exporter
    Exporter::register("source_code", "Sourcecode_Model_Sourcecode");

    Security::whitelistRoute("sourcecode/application/editpost");
};
