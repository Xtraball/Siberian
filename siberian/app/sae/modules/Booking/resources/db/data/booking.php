<?php
$name = 'Booking';
$category = 'contact';

# Install icons
$icons = [
    [
        'path' => '/booking/booking1.png',
        'keywords' => 'paper,letter',
    ],
    [
        'path' => '/booking/booking2.png',
        'keywords' => 'carnet',
    ],
    [
        'path' => '/booking/booking3.png',
        'keywords' => '',
    ],
    [
        'path' => '/booking/booking4.png',
        'keywords' => '',
    ],
    [
        'path' => '/booking/booking5.png',
        'keywords' => 'enveloppe,letter',
    ],
    [
        'path' => '/booking/booking6.png',
        'keywords' => 'enveloppe,letter',
    ],
    [
        'path' => '/booking/booking7.png',
        'keywords' => 'phone,mobile',
    ],
    [
        'path' => '/booking/booking8.png',
        'keywords' => 'phone,mobile',
    ],
    [
        'path' => '/booking/booking9.png',
        'keywords' => 'phone,mobile',
    ],
    [
        'path' => '/booking/booking10.png',
        'keywords' => 'calendar',
    ],
    [
        'path' => '/booking/booking11.png',
        'keywords' => 'notes',
    ]
];

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = [
    'library_id' => $result['library_id'],
    'icon_id' => $result['icon_id'],
    'code' => 'booking',
    'name' => $name,
    'model' => 'Booking_Model_Booking',
    'desktop_uri' => 'booking/application/',
    'mobile_uri' => 'booking/mobile_view/',
    'mobile_view_uri' => 'booking/mobile_view/',
    'mobile_view_uri_parameter' => null,
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 140
];

$option = Siberian_Feature::install($category, $data, ['code']);

# Icons Flat
$icons = [
    [
        'path' => '/booking/booking1-flat.png',
        'keywords' => 'carnet',
    ],
    [
        'path' => '/booking/booking2-flat.png',
        'keywords' => 'carnet',
    ],
    [
        'path' => '/booking/booking3-flat.png',
        'keywords' => 'carnet',
    ],
];

Siberian_Feature::installIcons("{$name}-flat", $icons);
