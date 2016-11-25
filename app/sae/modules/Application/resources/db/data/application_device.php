<?php
/**
 * Backup all PKS in db
 */
$devices = new Application_Model_Device();
$android_devices = $devices->findAll(array(
    "type_id = ?" => 2
));

$pks_path = Core_Model_Directory::getBasePathTo("/var/apps/android/keystore");

foreach($android_devices as $android_device) {
    $pks = sprintf("%s/%s.pks", $pks_path, $android_device->getAppId());
    $pks_content = $android_device->getPks();
    if(empty($pks_content) && file_exists($pks)) {
        $pks_content = file_get_contents($pks, FILE_BINARY);
        $android_device->setPks(bin2hex($pks_content))->save();
    } else {
        /**
         * @var Restore PKS from DB
        */
        //$pks_content = hex2bin($android_device->getPks());
        //file_put_contents($pks, $pks_content, FILE_BINARY);
    }
}
