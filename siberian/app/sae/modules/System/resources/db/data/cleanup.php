<?php

$hotfix4148 = Core_Model_Directory::getBasePathTo('/app/local/modules/Hotfix-4.14.8');
if (is_dir($hotfix4148)) {
    exec("rm -R '$hotfix4148'");
}

$hotfix41819 = __get('woocommerce_folder_fix');
if ($hotfix41819 !== 'done') {
    $libWooPath = path('/lib/WooCommerce');
    $libWooPathClient = path('/lib/WooCommerce/Client.php');
    if (file_exists($libWooPath) && !file_exists($libWooPathClient)) {
        // Clean-up WooCommerce
        exec('rm -rf ' . $libWooPath);
    }

    __set('woocommerce_folder_fix', 'done');
}
