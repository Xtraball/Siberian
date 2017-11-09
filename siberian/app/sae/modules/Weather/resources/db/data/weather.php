<?php
$name = "Weather";
$category = "misc";

# Install icons
$icons = array(
    "/weather/weather1.png",
    "/weather/weather2.png",
    "/weather/weather3.png"
);

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    'library_id'    => $result["library_id"],
    'icon_id'       => $result["icon_id"],
    'code'          => 'weather',
    'name'          => $name,
    'model'         => 'Weather_Model_Weather',
    'desktop_uri'   => 'weather/application/',
    'mobile_uri'    => 'weather/mobile_view/',
    'only_once'     => 0,
    'is_ajax'       => 1,
    'position'      => 240,
);

Siberian_Feature::install($category, $data, array('code'));

# Icons Flat
$icons = array(
    "/weather/weather-flat.png",
    "/weather/weather1-flat.png",
    "/weather/weather2-flat.png",
);

Siberian_Feature::installIcons("{$name}-flat", $icons);