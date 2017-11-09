<?php

$data = array(
    "name"      => "Analytics",
    "command"   => "agregateanalytics",
    "minute"    => "10",
    "hour"      => "0",
    "month_day" => "-1",
    "month"     => "-1",
    "week_day"  => "-1",
    "priority"  => 50,
    "is_active" => true,
);

$agregateanalytics = new Cron_Model_Cron();
$agregateanalytics
    ->setData($data)
    ->insertOrUpdate(array("command"));