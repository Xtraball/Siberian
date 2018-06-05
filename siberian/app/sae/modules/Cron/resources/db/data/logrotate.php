<?php
Siberian_Feature::installCronjob(
    "Log rotation",
    "logrotate",
    5,
    0,
    -1,
    -1,
    -1,
    true,
    50
);