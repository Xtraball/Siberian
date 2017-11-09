<?php
$name = "Facebook";
$category = "social";

# Install icons
$icons = array(
    '/social_facebook/facebook1.png'
);

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    'library_id'    => $result["library_id"],
    'icon_id'       => $result["icon_id"],
    'code'          => "facebook",
    'name'          => $name,
    'model'         => "Social_Model_Facebook",
    'desktop_uri'   => "social/application_facebook/",
    'mobile_uri'    => "social/mobile_facebook_list/",
    'only_once'     => 0,
    'is_ajax'       => 1,
    'position'      => 210
);

Siberian_Feature::install($category, $data, array('code'));

# Icons Flat
$icons = array(
    '/social_facebook/facebook1-flat.png',
    '/social_facebook/facebook2-flat.png',
    '/social_facebook/facebook3-flat.png',
);

Siberian_Feature::installIcons("{$name}-flat", $icons);