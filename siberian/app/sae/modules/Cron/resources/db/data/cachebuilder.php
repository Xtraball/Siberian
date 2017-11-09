<?php

$data = array(
    "name"      => "Cache builder",
    "command"   => "cachebuilder",
    "minute"    => "-1",
    "hour"      => "-1",
    "month_day" => "-1",
    "month"     => "-1",
    "week_day"  => "-1",
    "priority"  => 10,
    "is_active" => true,
);

$androidtools = new Cron_Model_Cron();
$androidtools
    ->setData($data)
    ->insertOrUpdate(array("command"));