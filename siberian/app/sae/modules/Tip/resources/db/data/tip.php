<?php
$name = "Tip";
$category = "misc";

# Install icons
$icons = [
    [
        'path' => "/tip/tip1.png",
        'keywords' => 'thumb',
    ],
    [
        'path' => "/tip/tip2.png",
        'keywords' => 'gift,box,package',
    ],
];

$result = Siberian_Feature::installIcons('Tips calculator', $icons);

# Install the Feature
$data = [
    'library_id' => $result["library_id"],
    'icon_id' => $result["icon_id"],
    'code' => 'tip',
    'name' => 'Tips calculator',
    'model' => 'Tip_Model_Tip',
    'desktop_uri' => 'tip/application/',
    'mobile_uri' => 'tip/mobile_view/',
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 240,
    'social_sharing_is_available' => 0
];

Siberian_Feature::install($category, $data, ['code']);

# Icons Flat
$icons = [
    [
        'path' => "/tip/tip1-flat.png",
        'keywords' => 'thumb',
    ],
    [
        'path' => "/tip/tip2-flat.png",
        'keywords' => 'thumb',
    ],
    [
        'path' => "/tip/tip3-flat.png",
        'keywords' => 'thumb',
    ],
];

Siberian_Feature::installIcons("Tips calculator-flat", $icons);