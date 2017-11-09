<?php
Siberian_Feature::installCronjob(
    "System alert watcher",
    "alertswatcher",
    -1,
    -1,
    -1,
    -1,
    -1,
    true,
    1000,
    true
);