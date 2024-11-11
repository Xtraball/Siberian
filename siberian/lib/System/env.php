<?php

/**
 * @version 5.0.13
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