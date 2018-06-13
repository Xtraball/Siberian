<?php
Siberian_Feature::installCronjob(
    "Push Message queue",
    "pushinstant",
    -1,
    -1,
    -1,
    -1,
    -1,
    true,
    100
);