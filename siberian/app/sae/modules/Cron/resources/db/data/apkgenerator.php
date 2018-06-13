<?php
Siberian_Feature::installCronjob(
    "APK Generator queue",
    "apkgenerator",
    -1,
    -1,
    -1,
    -1,
    -1,
    true,
    80
);