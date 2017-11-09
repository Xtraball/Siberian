<?php
$name = "Contact";
$category = "contact";

# Install icons
$icons = array(
    '/contact/contact1.png',
    '/contact/contact2.png',
    '/contact/contact3.png',
    '/contact/contact4.png',
    '/contact/contact5.png',
    '/contact/contact6.png',
    '/contact/contact7.png',
    '/contact/contact8.png',
    '/contact/contact9.png',
    '/contact/contact10.png',
    '/contact/contact11.png'
);

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    'library_id'    => $result["library_id"],
    'icon_id'       => $result["icon_id"],
    'code'          => 'contact',
    'name'          => $name,
    'model'         => 'Contact_Model_Contact',
    'desktop_uri'   => 'contact/application/',
    'mobile_uri'    => 'contact/mobile_view/',
    'only_once'     => 0,
    'is_ajax'       => 1,
    'position'      => 120
);

$option = Siberian_Feature::install($category, $data, array('code'));


# Icons Flat
$icons = array(
    '/contact/contact1-flat.png',
    '/contact/contact2-flat.png',
    '/contact/contact3-flat.png',
);

Siberian_Feature::installIcons("{$name}-flat", $icons);
