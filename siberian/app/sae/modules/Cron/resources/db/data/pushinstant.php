<?php

$data = array(
    "name"      => "Push Message queue",
    "command"   => "pushinstant",
    "minute"    => "-1",
    "hour"      => "-1",
    "month_day" => "-1",
    "month"     => "-1",
    "week_day"  => "-1",
    "priority"  => 100,
    "is_active" => true,
);

$pushinstant = new Cron_Model_Cron();
$pushinstant
    ->setData($data)
    ->insertOrUpdate(array("command"));