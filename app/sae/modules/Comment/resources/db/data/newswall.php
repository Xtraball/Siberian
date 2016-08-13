<?php
$name = "Newswall";
$category = "social";

# Install icons
$icons = array(
    "/newswall/newswall1.png",
    "/newswall/newswall2.png",
    "/newswall/newswall3.png",
    "/newswall/newswall4.png"
);

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    'library_id'                    => $result["library_id"],
    'icon_id'                       => $result["icon_id"],
    "code"                          => "newswall",
    "name"                          => $name,
    "model"                         => "Comment_Model_Comment",
    "desktop_uri"                   => "comment/application/",
    "mobile_uri"                    => "comment/mobile_list/",
    "mobile_view_uri"               => "comment/mobile_view/",
    "mobile_view_uri_parameter"     => "comment_id",
    "only_once"                     => 0,
    "is_ajax"                       => 1,
    "position"                      => 10,
    "social_sharing_is_available"   => 1,
    "use_my_account"                => 1,
);

$option = Siberian_Feature::install($category, $data, array('code'));

# Layouts
$layout_data = array(1, 2, 3, 4);
$slug = "newswall";

Siberian_Feature::installLayouts($option->getId(), $slug, $layout_data);

# Icons Flat
$icons = array(
    "/newswall/newswall1-flat.png",
    "/newswall/newswall2-flat.png",
    "/newswall/newswall3-flat.png",
);

Siberian_Feature::installIcons("{$name}-flat", $icons);
