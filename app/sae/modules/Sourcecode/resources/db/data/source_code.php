<?php
$name = "Source code";
$category = "customization";

# Install icons
$icons = array(
    '/source_code/source_code.png',
    '/custom_page/custom1.png',
    '/newswall/newswall2.png',
    '/catalog/catalog6.png',
    '/booking/booking4.png'
);

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    'library_id'    => $result["library_id"],
    'icon_id'       => $result["icon_id"],
    'code'          => 'source_code',
    'name'          => $name,
    'model'         => 'Sourcecode_Model_Sourcecode',
    'desktop_uri'   => 'sourcecode/application/',
    'mobile_uri'    => 'sourcecode/mobile_view/',
    'only_once'     => 0,
    'is_ajax'       => 1,
    'position'      => 75
);

$option = Siberian_Feature::install($category, $data, array('code'));

# Icons Flat
$icons = array(
    '/source_code/source_code1-flat.png',
    '/source_code/source_code2-flat.png',
    '/source_code/source_code3-flat.png',
);

Siberian_Feature::installIcons("{$name}-flat", $icons);