<?php

include('../autoload.php'); // If using composer

use Gregwar\Cache\GarbageCollect;

if (!is_dir('cache')) {
    `mkdir cache`;
}
`touch -t 9901010101 cache/foo`;
`touch cache/bar`;

GarbageCollect::dropOldFiles(__DIR__.'/cache', 30, true);

