<?php
$name = "Maps";
$category = "misc";

# Install icons
$icons = array(
    "/maps/maps1.png",
    "/maps/maps2.png",
    "/maps/maps3.png"
);


$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    'library_id'    => $result["library_id"],
    'icon_id'       => $result["icon_id"],
    'code'          => 'maps',
    'name'          => $name,
    'model'         => 'Maps_Model_Maps',
    'desktop_uri'   => 'maps/application/',
    'mobile_uri'    => 'maps/mobile_view/',
    'only_once'     => 0,
    'is_ajax'       => 1,
    'position'      => 240
);

$option = Siberian_Feature::install($category, $data, array('code'));



# Icons Flat
$icons = array(
    "/maps/maps1-flat.png",
    "/maps/maps2-flat.png",
    "/maps/maps3-flat.png",
);

Siberian_Feature::installIcons("{$name}-flat", $icons);