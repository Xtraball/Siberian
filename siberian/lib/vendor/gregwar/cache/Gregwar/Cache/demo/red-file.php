<?php

include('../autoload.php'); // If using composer

use Gregwar\Cache\Cache;

$cache = new Cache;
$cache->setCacheDirectory('cache'); // This is the default

// If the cache exists, this will return it, else, the closure will be called
// to create this image
$file = $cache->getOrCreateFile('red-square.png', array(), function($filename) {
    $i = imagecreatetruecolor(100, 100);
    imagefill($i, 0, 0, 0xff0000);
    file_put_contents($filename, 'abc');
    imagepng($i, 'a.png');
    imagepng($i, $filename);
});

echo $file, "\n";
