<?php
$name = "Loyalty Card";
$category = "monetization";

# Install icons
$icons = array(
    '/loyalty/loyalty1.png',
    '/loyalty/loyalty2.png',
    '/loyalty/loyalty3.png',
    '/loyalty/loyalty4.png',
    '/loyalty/loyalty5.png',
    '/loyalty/loyalty6.png'
);

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    'library_id'     => $result["library_id"],
    'icon_id'        => $result["icon_id"],
    'code'           => "loyalty",
    'name'           => $name,
    'model'          => "LoyaltyCard_Model_LoyaltyCard",
    'desktop_uri'    => "loyaltycard/application/",
    'mobile_uri'     => "loyaltycard/mobile_view/",
    'only_once'      => 0,
    'is_ajax'        => 1,
    'position'       => 50,
    "use_my_account" => 1,
);

$option = Siberian_Feature::install($category, $data, array('code'));

# Icons Flat
$icons = array(
    '/loyalty/loyalty1-flat.png',
    '/loyalty/loyalty2-flat.png',
    '/loyalty/loyalty3-flat.png',
);

Siberian_Feature::installIcons("{$name}-flat", $icons);
