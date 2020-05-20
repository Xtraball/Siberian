<?php

// Ionic colors
$ionicColors = [
    'header' => [
        'color' => '#ffffff',
        'background_color' => '#ee4b63',
        'border_color' => '#ffffff'
    ],
    'buttons_positive' => [
        'color' => '#ffffff',
        'background_color' => '#ee4b63',
        'border_color' => '#ffffff'
    ],
    'homepage' => [
        'color' => '#0faca4',
        'background_color' => '#0faca4',
        'border_color' => '#0faca4',
        'image_color' => '#ffffff'
    ],
    'background' => [
        'background_color' => '#0faca4'
    ],
    'list_item_divider' => [
        'color' => '#ffffff',
        'background_color' => '#0faca4'
    ],
    'list_item' => [
        'color' => '#000222',
        'background_color' => '#ffffff'
    ],
    'card_item_divider' => [
        'color' => '#ffffff',
        'background_color' => '#0faca4'
    ],
    'checkbox_on' => [
        'color' => '#ffffff',
        'background_color' => '#0faca4'
    ],
    'toggle_on' => [
        'background_color' => '#0faca4'
    ],
    'spinner_android_text' => [
        'background_color' => '#0faca4'
    ],
];

$features = [
    'music_gallery' => ['icon' => '/musics/music1-flat.png'],
    'image_gallery' => ['icon' => '/images/image1-flat.png'],
    'video_gallery' => ['icon' => '/videos/video1-flat.png'],
    'fanwall2' => ['icon' => '/fanwall/fanwall1-flat.png'],
    'radio' => ['icon' => '/radio/radio1-flat.png'],
    'calendar' => ['icon' => '/calendar/calendar1-flat.png'],
    'code_scan' => ['icon' => '/code_scan/scan1-flat.png']
];

\Siberian\Template::installOrUpdate(
    'TemplateColors',
    'Colors',
    'colors',
    'layout_4',
    ['Arts'],
    $ionicColors,
    $features,
    30
);
