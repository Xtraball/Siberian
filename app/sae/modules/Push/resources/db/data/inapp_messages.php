<?php
$name = "In-App Messages";
$category = "contact";

# Install icons
$icons = array(
    '/inapp_messages/inapp1.png'
);

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    'library_id'    => $result["library_id"],
    'icon_id'       => $result["icon_id"],
    'code'          => "inapp_messages",
    'name'          => $name,
    'model'         => "Push_Model_Message",
    'desktop_uri'   => "push/application/",
    'mobile_uri'    => "push/mobile_list/",
    'only_once'     => 1,
    'is_ajax'       => 1,
    'position'      => 130,
    "use_my_account" => 0,
);

$option = Siberian_Feature::install($category, $data, array('code'));

# Icons Flat
$icons = array(
    '/inapp_messages/inapp1-flat.png',
    '/inapp_messages/inapp2-flat.png',
    '/inapp_messages/inapp3-flat.png',
);

Siberian_Feature::installIcons("{$name}-flat", $icons);