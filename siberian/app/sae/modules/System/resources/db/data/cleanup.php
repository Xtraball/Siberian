<?php

$hotfix4148 = Core_Model_Directory::getBasePathTo('/app/local/modules/Hotfix-4.14.8');
if (is_dir($hotfix4148)) {
    exec("rm -R '$hotfix4148'");
}