<?php
$name = "Places";
$category = "misc";

# Install icons
$icons = array(
    "/places/places1.png",
    "/places/places2.png",
    "/places/places3.png",
    "/places/places4.png"
);

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    'library_id'                    => $result["library_id"],
    'icon_id'                       => $result["icon_id"],
    'code'                          => 'places',
    'name'                          => $name,
    'model'                         => 'Cms_Model_Application_Page',
    'desktop_uri'                   => 'places/application/',
    'mobile_uri'                    => 'places/mobile_list/',
    'only_once'                     => 0,
    'is_ajax'                       => 1,
    'position'                      => 240,
    'social_sharing_is_available'   => 1
);

$option = Siberian_Feature::install($category, $data, array('code'));

# Icons Flat
$icons = array(
    "/places/places-flat.png",
    "/places/places1-flat.png",
    "/places/places2-flat.png",
);

Siberian_Feature::installIcons("{$name}-flat", $icons);