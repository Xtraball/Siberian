<?php

$data = array(
    "name"      => "Sources builder queue",
    "command"   => "sources",
    "minute"    => "-1",
    "hour"      => "-1",
    "month_day" => "-1",
    "month"     => "-1",
    "week_day"  => "-1",
    "priority"  => 50,
    "is_active" => true,
);

$sources = new Cron_Model_Cron();
$sources
    ->setData($data)
    ->insertOrUpdate(array("command"));