<?php
$name = "Fan Wall";
$category = "social";

# Install icons
$icons = array(
    "/fanwall/fanwall1.png",
    "/fanwall/fanwall2.png",
    "/fanwall/fanwall3.png",
    "/fanwall/fanwall4.png"
);

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    'library_id'                    => $result["library_id"],
    'icon_id'                       => $result["icon_id"],
    "code"                          => "fanwall",
    "name"                          => $name,
    "model"                         => "Comment_Model_Comment",
    "desktop_uri"                   => "comment/application/",
    "mobile_uri"                    => "comment/mobile_list/",
    "mobile_view_uri"               => "comment/mobile_view/",
    "mobile_view_uri_parameter"     => "comment_id",
    "only_once"                     => 0,
    "is_ajax"                       => 1,
    "position"                      => 15,
    "social_sharing_is_available"   => 1,
    "use_my_account"                => 1
);

$option = Siberian_Feature::install($category, $data, array('code'));

# Layouts
$layout_data = array(1);
$slug = "newswall";

Siberian_Feature::installLayouts($option->getId(), $slug, $layout_data);

# Icons Flat
$icons = array(
    "/fanwall/fanwall1-flat.png",
    "/fanwall/fanwall2-flat.png",
    "/fanwall/fanwall3-flat.png",
);

Siberian_Feature::installIcons("{$name}-flat", $icons);
