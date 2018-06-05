<?php
Siberian_Feature::installCronjob(
    "Sources builder queue",
    "sources",
    -1,
    -1,
    -1,
    -1,
    -1,
    true,
    50
);