<?php

$queries = [
    'UPDATE `push_delivered_message` SET is_displayed = 1 WHERE is_read = 1 and is_displayed = 0;',
    'ALTER TABLE `push_messages` MODIFY `push_messages`.`title` TEXT NOT NULL;',
    'ALTER TABLE `push_messages` MODIFY `push_messages`.`text` TEXT NOT NULL;',
    'ALTER TABLE `push_message_global` MODIFY `push_message_global`.`title` TEXT NOT NULL;',
    'ALTER TABLE `push_message_global` MODIFY `push_message_global`.`message` TEXT NOT NULL;',
    'ALTER TABLE `push_apns_devices` CHANGE `push_badge` `push_badge` ENUM(\'disabled\',\'enabled\') CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT \'enabled\';',
    'ALTER TABLE `push_apns_devices` CHANGE `push_alert` `push_alert` ENUM(\'disabled\',\'enabled\') CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT \'enabled\';',
    'ALTER TABLE `push_apns_devices` CHANGE `push_sound` `push_sound` ENUM(\'disabled\',\'enabled\') CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT \'enabled\';',
];

# 4.12.2 fixes iphone disabled push
foreach($queries as $query) {
    try {
        $this->query($query);
    } catch(Exception $e) {}
}
