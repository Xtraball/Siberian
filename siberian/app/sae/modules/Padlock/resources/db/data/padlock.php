<?php
$name = "Padlock";
$category = "misc";

# Install icons
$icons = array(
    "/padlock/padlock.png",
);

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    'library_id'     => $result["library_id"],
    'icon_id'        => $result["icon_id"],
    'code'           => "padlock",
    'name'           => $name,
    'model'          => "Padlock_Model_Padlock",
    'desktop_uri'    => "padlock/application/",
    'mobile_uri'     => "padlock/mobile_view/",
    'only_once'      => 0,
    'is_ajax'        => 1,
    'position'       => 220,
    'use_my_account' => 1
);

$option = Siberian_Feature::install($category, $data, array('code'));


# Icons Flat
$icons = array(
    "/padlock/padlock-flat.png",
    "/padlock/padlock1-flat.png",
    "/padlock/padlock2-flat.png",
);

Siberian_Feature::installIcons("{$name}-flat", $icons);
