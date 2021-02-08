<?php

// Update 4.20.3
$query = "UPDATE `application` SET `disable_updates` = 1;";

$disablePatch = __get('force_lock_update');
if ($disablePatch !== 'fixed') {
    $this->query($query);
    __set('force_lock_update', 'fixed');
}
