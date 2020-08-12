<?php

use Siberian\Security;

$init = function($bootstrap) {
    Siberian_Cache_Design::overrideCoreDesign("Cms");

    Security::whitelistRoute("cms/application_page/editpostv2");
};
