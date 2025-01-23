<?php

/**
 * @version 5.1.0
 * @author Xtraball SAS <dev@xtraball.com>
 *
 * This file is part of the new stateless Siberian
 * With the help of environment variables, we no longer require app/configs/* to be writeable
 */

# Load env from .env file
$env_path = realpath(__DIR__ . '/../../.env');
if (file_exists($env_path)) {
    $env_lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env_lines as $env_line) {
        putenv($env_line);
    }
    $_config['use_env'] = true;
}

$envs = [
    'IS_INSTALLED' => '0',
    'MYSQL_HOST' => 'localhost',
    'MYSQL_DATABASE' => 'siberian',
    'MYSQL_USER' => 'root',
    'MYSQL_PASS' => '',
];

foreach ($envs as $key => $default) {
    $value = getenv($key, true) ?: getenv($key) ?: $default;
    define($key, $value);
}

# Build # DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/app" for symfony
# with defined const previously
if (!defined("DATABASE_URL")) {
    putenv("DATABASE_URL=mysql://".MYSQL_USER.":".MYSQL_PASS."@".MYSQL_HOST."/".MYSQL_DATABASE);
}
