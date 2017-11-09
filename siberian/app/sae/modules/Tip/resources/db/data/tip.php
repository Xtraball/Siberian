<?php
$name = "Tip";
$category = "misc";

# Install icons
$icons = array(
    "/tip/tip1.png",
    "/tip/tip2.png"
);

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    'library_id'    => $result["library_id"],
    'icon_id'       => $result["icon_id"],
    'code' => 'tip',
    'name' => 'Tips calculator',
    'model' => 'Tip_Model_Tip',
    'desktop_uri' => 'tip/application/',
    'mobile_uri' => 'tip/mobile_view/',
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 240,
    'social_sharing_is_available' => 0
);

Siberian_Feature::install($category, $data, array('code'));

# Icons Flat
$icons = array(
    '/tip/tip1-flat.png',
    '/tip/tip2-flat.png',
    '/tip/tip3-flat.png',
);

Siberian_Feature::installIcons("{$name}-flat", $icons);