<?php

include('../autoload.php');

use Gregwar\Cache\Cache;

$cache = new Cache;

$data = $cache->getOrCreate('uppercase.txt', array('younger-than' => 'original.txt'), function() {
    echo "Generating file...\n";
    return strtoupper(file_get_contents('original.txt'));
});

echo $data;
