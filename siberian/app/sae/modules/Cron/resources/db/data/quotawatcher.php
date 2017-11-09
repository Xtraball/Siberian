<?php
Siberian_Feature::installCronjob(
    "Disk quota watcher",
    "quotawatcher",
    30,
    -1,
    -1,
    -1,
    -1,
    false,
    1000,
    true
);