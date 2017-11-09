<?php

$data = array(
    "name"      => "Log rotation",
    "command"   => "logrotate",
    "minute"    => "5",
    "hour"      => "0",
    "month_day" => "-1",
    "month"     => "-1",
    "week_day"  => "-1",
    "priority"  => 50,
    "is_active" => true,
);

$logrotate = new Cron_Model_Cron();
$logrotate
    ->setData($data)
    ->insertOrUpdate(array("command"));