<?php
// Following all latest major fcebook changes, this feature is now deprecated/removed for the core!
$facebook = (new Application_Model_Option())->find('facebook', 'code');
if ($facebook && $facebook->getId()) {
    // Disable facebook if installed*
    $facebook
        ->setIsEnabled(0)
        ->save();
}
