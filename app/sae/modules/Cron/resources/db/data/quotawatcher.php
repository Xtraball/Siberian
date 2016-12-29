<?php
Siberian_Feature::installCronjob(
    "Disk quota watcher",
    "quotawatcher",
    -1,
    -1,
    -1,
    -1,
    -1,
    true,
    1000,
    true
);