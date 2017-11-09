<?php
$name = "Booking";
$category = "contact";

# Install icons
$icons = array(
    '/booking/booking1.png',
    '/booking/booking2.png',
    '/booking/booking3.png',
    '/booking/booking4.png',
    '/booking/booking5.png',
    '/booking/booking6.png',
    '/booking/booking7.png',
    '/booking/booking8.png',
    '/booking/booking9.png',
    '/booking/booking10.png',
    '/booking/booking11.png'
);

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    'library_id'                    => $result["library_id"],
    'icon_id'                       => $result["icon_id"],
    'code'                          => 'booking',
    'name'                          => $name,
    'model'                         => 'Booking_Model_Booking',
    'desktop_uri'                   => 'booking/application/',
    'mobile_uri'                    => 'booking/mobile_view/',
    "mobile_view_uri"               => "booking/mobile_view/",
    "mobile_view_uri_parameter"     => null,
    'only_once'                     => 0,
    'is_ajax'                       => 1,
    'position'                      => 140
);

$option = Siberian_Feature::install($category, $data, array('code'));

# Icons Flat
$icons = array(
    '/booking/booking1-flat.png',
    '/booking/booking2-flat.png',
    '/booking/booking3-flat.png',
);

Siberian_Feature::installIcons("{$name}-flat", $icons);
