<?php
Siberian_Feature::installCronjob(
    "Disk usage watcher",
    "diskusage",
    30,
    3,
    -1,
    -1,
    -1,
    true,
    2000,
    false
);