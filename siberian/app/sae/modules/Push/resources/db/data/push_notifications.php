<?php
$name = "Push Notifications";
$category = "contact";

# Install icons
$icons = array(
    '/push_notifications/push1.png',
    '/push_notifications/push2.png',
    '/push_notifications/push3.png',
    '/push_notifications/push4.png',
    '/push_notifications/push5.png',
);

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    'library_id'    => $result["library_id"],
    'icon_id'       => $result["icon_id"],
    'code'          => "push_notification",
    'name'          => $name,
    'model'         => "Push_Model_Message",
    'desktop_uri'   => "push/application/",
    'mobile_uri'    => "push/mobile_list/",
    'only_once'     => 1,
    'is_ajax'       => 1,
    'position'      => 130,
    'use_my_account' => 0,
);

$option = Siberian_Feature::install($category, $data, array('code'));

# Icons Flat
$icons = array(
    '/push_notifications/push1-flat.png',
    '/push_notifications/push2-flat.png',
    '/push_notifications/push3-flat.png',
);

Siberian_Feature::installIcons("{$name}-flat", $icons);