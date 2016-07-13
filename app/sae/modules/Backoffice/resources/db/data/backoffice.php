<?php
$message = 'Welcome! First of all, let\'s configure your platform. <a href="/system/backoffice_config_general"><u>Click here</u></a> to fill in your information or <a href="/system/backoffice_config_email"><u>here</u></a> to configure your email address';

$data = array(
    "title" => "Welcome!",
    "description" => $message,
    "original_notification_id" => 0,
);

$notif = new Backoffice_Model_Notification();
$notif
    ->setData($data)
    ->insertOnce(array("original_notification_id"));