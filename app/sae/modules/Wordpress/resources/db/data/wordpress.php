<?php
$name = "Wordpress";

# Icons
$icons = array(
    '/wordpress/wordpress1.png',
);

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    'library_id'    => $result["library_id"],
    'icon_id'       => $result["icon_id"],
    'code'          => "wordpress",
    'name'          => $name,
    'model'         => "Wordpress_Model_Wordpress",
    'desktop_uri'   => "wordpress/application/",
    'mobile_uri'    => "wordpress/mobile_list/",
    'only_once'     => 0,
    'is_ajax'       => 1,
    'position'      => 170,
    'social_sharing_is_available' => 1
);

$option = Siberian_Feature::install("integration", $data, array('code'));

# Layouts
$layout_data = array(1, 2, 3);
$slug = "wordpress";

Siberian_Feature::installLayouts($option->getId(), $slug, $layout_data);

# Icons Flat
$icons = array(
    '/wordpress/wordpress1-flat.png',
    '/wordpress/wordpress2-flat.png',
);

Siberian_Feature::installIcons("{$name}-flat", $icons);