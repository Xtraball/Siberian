<?php
$name = "Radio";
$category = "media";

# Install icons
$icons = array(
    '/radio/radio1.png',
    '/radio/radio2.png',
    '/radio/radio3.png',
    '/radio/radio4.png',
    '/radio/radio5.png',
    '/radio/radio6.png',
    '/radio/radio7.png',
    '/radio/radio8.png'
);

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    'library_id'    => $result["library_id"],
    'icon_id'       => $result["icon_id"],
    'code'          => "radio",
    'name'          => $name,
    'model'         => "Radio_Model_Radio",
    'desktop_uri'   => "radio/application/",
    'mobile_uri'    => "radio/mobile_radio/",
    'only_once'     => 0,
    'is_ajax'       => 1,
    'position'      => 105
);

$option = Siberian_Feature::install($category, $data, array('code'));

# Icons Flat
$icons = array(
    '/radio/radio1-flat.png',
    '/radio/radio2-flat.png',
    '/radio/radio3-flat.png',
);

Siberian_Feature::installIcons("{$name}-flat", $icons);