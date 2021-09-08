<?php

use Siberian\Feature;

# Install icons
$icons = [
    [
        'path' => '/home/home-15.png',
        'colorize' => true,
        'keywords' => 'outline,home,house,start',
    ],
    [
        'path' => '/home/home-14.png',
        'colorize' => true,
        'keywords' => 'outline,home,house,start',
    ],
    [
        'path' => '/home/home-13.png',
        'colorize' => true,
        'keywords' => 'outline,home,house,start',
    ],
    [
        'path' => '/home/home-12.png',
        'colorize' => true,
        'keywords' => 'filled,home,house,start',
    ],
    [
        'path' => '/home/home-11.png',
        'colorize' => true,
        'keywords' => 'filled,home,house,start',
    ],
    [
        'path' => '/home/home-10.png',
        'colorize' => true,
        'keywords' => 'filled,home,house,start',
    ],
    [
        'path' => '/home/home-9.png',
        'colorize' => false,
        'keywords' => 'flat,home,house,start',
    ],
    [
        'path' => '/home/home-8.png',
        'colorize' => false,
        'keywords' => 'flat,home,house,start',
    ],
    [
        'path' => '/home/home-7.png',
        'colorize' => false,
        'keywords' => 'flat,home,house,start',
    ],
    [
        'path' => '/home/home-6.png',
        'colorize' => false,
        'keywords' => 'flat,home,house,start',
    ],
    [
        'path' => '/home/home-5.png',
        'colorize' => false,
        'keywords' => 'flat,home,house,start',
    ],
    [
        'path' => '/home/home-4.png',
        'colorize' => false,
        'keywords' => 'flat,home,house,start',
    ],
    [
        'path' => '/home/home-3.png',
        'colorize' => true,
        'keywords' => 'outline,home,house,start',
    ],
    [
        'path' => '/home/home-2.png',
        'colorize' => false,
        'keywords' => 'flat,home,house,start',
    ],
    [
        'path' => '/home/home-1.png',
        'colorize' => true,
        'keywords' => 'outline,home,house,start',
    ],
];

$result = Feature::installIcons('icons-home', $icons);