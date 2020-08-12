<?php
$name = "Code Scan";
$category = "misc";

# Install icons
$icons = array(
    '/code_scan/scan1.png',
    '/code_scan/scan2.png',
    '/code_scan/scan3.png',
);

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    'library_id'    => $result["library_id"],
    'icon_id'       => $result["icon_id"],
    "code" => "code_scan",
    "name" => "Code Scan",
    "model" => "Codescan_Model_Codescan",
    "desktop_uri" => "codescan/application/",
    "mobile_uri" => "codescan/mobile_view/",
    "position" => 150
);

$option = Siberian_Feature::install($category, $data, array('code'));

# Icons Flat
$icons = array(
    '/code_scan/scan1-flat.png',
    '/code_scan/scan2-flat.png',
    '/code_scan/scan3-flat.png',
);

Siberian_Feature::installIcons("{$name}-flat", $icons);