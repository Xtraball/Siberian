<?php
$name = "RSS Feed";
$category = "media";

# Install icons
$icons = array(
    "/rss_feed/rss1.png",
    "/rss_feed/rss2.png",
    "/rss_feed/rss3.png",
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
    "code"                          => "rss_feed",
    "name"                          => $name,
    "model"                         => "Rss_Model_Feed",
    "desktop_uri"                   => "rss/application_feed/",
    "mobile_uri"                    => "rss/mobile_feed_list/",
    "mobile_view_uri"               => "rss/mobile_feed_view/",
    "mobile_view_uri_parameter"     => "feed_id",
    "only_once"                     => 0,
    "is_ajax"                       => 1,
    "position"                      => 80,
    "social_sharing_is_available"   => 1
);

$rss_option = Siberian_Feature::install($category, $data, array('code'));

# Layouts
$layout_data = array(1, 2, 3);
$slug = "rss-feed";

Siberian_Feature::installLayouts($rss_option->getId(), $slug, $layout_data);

# Icons Flat
$icons = array(
    "/rss_feed/rss1-flat.png",
    "/rss_feed/rss2-flat.png",
    "/rss_feed/rss3-flat.png",
);

Siberian_Feature::installIcons("{$name}-flat", $icons);