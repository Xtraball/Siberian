<?php

$data = array(
    "name"      => "APK Generator queue",
    "command"   => "apkgenerator",
    "minute"    => "-1",
    "hour"      => "-1",
    "month_day" => "-1",
    "month"     => "-1",
    "week_day"  => "-1",
    "priority"  => 80,
    "is_active" => true,
);

$apkgenerator = new Cron_Model_Cron();
$apkgenerator
    ->setData($data)
    ->insertOrUpdate(array("command"));