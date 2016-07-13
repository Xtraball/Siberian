<?php
$name = "Topics";
$category = "contact";

# Install icons
$icons = array(
    '/topic/topics1.png',
    '/topic/topics2.png',
    '/topic/topics3.png',
    '/topic/topics4.png',
);

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    'library_id'    => $result["library_id"],
    'icon_id'       => $result["icon_id"],
    'code'          => "topic",
    'name'          => "Topics",
    'model'         => "Topic_Model_Topic",
    'desktop_uri'   => "topic/application/",
    'mobile_uri'    => "topic/mobile_list/",
    'only_once'     => 1,
    'is_ajax'       => 1,
    'position'      => 241
);

Siberian_Feature::install($category, $data, array('code'));

# Icons Flat
$icons = array(
    '/topic/topics1-flat.png',
    '/topic/topics2-flat.png',
    '/topic/topics3-flat.png',
);

Siberian_Feature::installIcons("{$name}-flat", $icons);