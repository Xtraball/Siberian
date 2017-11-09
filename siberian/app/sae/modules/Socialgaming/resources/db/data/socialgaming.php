<?php
# Social Gaming
$name = "Contest";
$category = "social";

# Install icons
$icons = array(
    '/contest/contest1.png',
    '/contest/contest2.png',
    '/contest/contest3.png',
    '/contest/contest4.png',
    '/contest/contest5.png',
);

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    'library_id'     => $result["library_id"],
    'icon_id'        => $result["icon_id"],
    'code'           => "social_gaming",
    'name'           => $name,
    'model'          => "Socialgaming_Model_Game",
    'desktop_uri'    => "socialgaming/application/",
    'mobile_uri'     => "socialgaming/mobile_view/",
    'only_once'      => 1,
    'is_ajax'        => 1,
    'position'       => 60,
    'use_my_account' => 1,
    'use_ranking'    => 1
);

Siberian_Feature::install($category, $data, array('code'));

# Icons Flat
$icons = array(
    '/contest/contest1-flat.png',
    '/contest/contest2-flat.png',
    '/contest/contest3-flat.png',
);

Siberian_Feature::installIcons("{$name}-flat", $icons);
