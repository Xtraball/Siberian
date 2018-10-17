<?php

// Ionic colors
$ionicColors = [
    'header' => [
        'color' => '#ffffff',
        'background_color' => '#1374CE',
        'border_color' => '#ffffff',
    ],
    'buttons_positive' => [
        'color' => '#ffffff',
        'background_color' => '#1374CE',
        'border_color' => '#ffffff',
    ],
    'homepage' => [
        'color' => '#ffffff',
        'background_color' => '#1374CE',
        'background_opacity' => 60,
        'border_color' => '#ffffff',
        'border_opacity' => 0,
        'image_color' => '#ffffff'
    ],
    'background' => [
        'background_color' => '#ffffff'
    ],
    'list_item_divider' => [
        'color' => '#ffffff',
        'background_color' => '#1374CE'
    ],
    'list_item' => [
        'color' => '#000222',
        'background_color' => '#ffffff'
    ],
    'card_item_divider' => [
        'color' => '#000222',
        'background_color' => '#1374CE'
    ],
    'checkbox_on' => [
        'color' => '#ffffff',
        'background_color' => '#1374CE'
    ],
    'toggle_on' => [
        'background_color' => '#1374CE'
    ],
    'spinner_android_text' => [
        'background_color' => '#1374CE'
    ],
];

$features = [
    'facebook' => ['icon' => '/social_facebook/facebook1-flat.png'],
    'weblink_multi' => ['name' => 'Links', 'icon' => '/weblink/link1-flat.png'],
    'push_notification' => ['icon' => '/push_notifications/push1-flat.png'],
    'tip' => ['icon' => '/tip/tip1-flat.png']
];

\Siberian\Template::installOrUpdate(
    'TemplateBleuc',
    'Blutility',
    'bleuc',
    'layout_5',
    ['Corporate'],
    $ionicColors,
    $features,
    20
);