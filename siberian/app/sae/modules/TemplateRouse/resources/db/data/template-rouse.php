<?php

// Ionic colors
$ionicColors = [
    'header' => [
        'color' => '#ffffff',
        'background_color' => '#EE4B63',
        'border_color' => '#ffffff'
    ],
    'buttons_positive' => [
        'color' => '#ffffff',
        'background_color' => '#EE4B63',
        'border_color' => '#ffffff',
    ],
    'homepage' => [
        'color' => '#ffffff',
        'background_color' => '#ffffff',
        'background_opacity' => 20,
        'border_color' => '#ffffff',
        'border_opacity' => 100,
        'image_color' => '#ffffff'
    ],
    'background' => [
        'background_color' => '#242037'
    ],
    'list_item_divider' => [
        'color' => '#ffffff',
        'background_color' => '#EE4B63'
    ],
    'list_item' => [
        'color' => '#000222',
        'background_color' => '#ffffff'
    ],
    'card_item_divider' => [
        'color' => '#000222',
        'background_color' => '#ee4b63'
    ],
    'checkbox_on' => [
        'color' => '#ffffff',
        'background_color' => '#ee4b63'
    ],
    'toggle_on' => [
        'background_color' => '#ee4b63'
    ],
    'spinner_android_text' => [
        'background_color' => '#ee4b63'
    ],
];

$features = [
    'set_meal' => ['icon' => '/set_meal/meat1-flat.png'],
    'booking' => ['icon' => '/booking/booking1-flat.png'],
    'catalog' => ['icon' => '/catalog/catalog1-flat.png'],
    'discount' => ['icon' => '/discount/discount1-flat.png'],
    'loyalty' => ['icon' => '/loyalty/loyalty1-flat.png']
];

\Siberian\Template::installOrUpdate(
    'TemplateRouse',
    'Red&Co',
    'rouse',
    'layout_6',
    ['Design'],
    $ionicColors,
    $features,
    10
);