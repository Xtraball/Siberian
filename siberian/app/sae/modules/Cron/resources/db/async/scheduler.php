<?php

set_time_limit(9);

$root_path = Core_Model_Directory::getBasePathTo("");

# Installing (re-installing) cron
$crontab_path = preg_replace("#(\/){2,}#", "/", "$root_path/var/tmp/crontab");
$cron_path = preg_replace("#(\/){2,}#", "/", "$root_path/cron.php");
$cron_log_path = preg_replace("#(\/){2,}#", "/", "$root_path/var/log/cron.log");

if(is_writable(dirname($crontab_path))) {

    # Guessing for php binary
    $possible_paths = array(
        "/opt/plesk/php/5.6/bin/php",
        "/usr/local/bin/php",
        "/usr/local/bin/php-cli",
        "/usr/bin/php",
        "/usr/bin/php-cli",
    );

    $guessed_path = "php";

    foreach($possible_paths as $possible_path) {
        $output = array();
        exec("{$possible_path} -f {$cron_path} test", $output);
        if(isset($output[0]) && ($output[0] == "OK")) {
            $guessed_path = $possible_path;
            break;
        }
    }

    # Trying with which at last
    if($guessed_path == "php") {
        $output = array();
        exec("which php", $output);
        if(isset($output[0]) && (strpos($output[0], "/php") !== false)) {
            exec("$output[0] -f {$cron_path} test", $output2);
            if(isset($output2[0]) && ($output2[0] == "OK")) {
                $guessed_path = $possible_path;
            }
        }
    }

    exec("crontab -l > $crontab_path");
    $crontab = file_get_contents($crontab_path);

    # Remove old crontab if exists, avoiding conflict
    $crontab_lines = array_filter(explode("\n", $crontab));
    $new_lines = array();
    foreach($crontab_lines as $crontab_line) {
        if(!preg_match("@^\*/5.*$cron_path@i", $crontab_line)) {
            $new_lines[] = $crontab_line;
        }
    }

    # Install crontab if not
    $crontab = implode("\n", $new_lines);
    if(strpos($crontab, $cron_path) === false) {
        $new_lines[] = "* * * * * $guessed_path -d memory_limit=512M -f $cron_path >> $cron_log_path 2>&1\n";
    }

    # Append new line at the end
    $new_lines[] = "\n";

    file_put_contents($crontab_path, implode("\n", $new_lines));

    exec("crontab $crontab_path");
} else {
    $errors[] = "Unable to automatically install the cron job, Please add it manually under the server user

crontab -u <web_user> -e

Then paste this line

* * * * * $guessed_path -f $cron_path >> $cron_log_path 2>&1

";
}