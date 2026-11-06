<?php

// php -S router for Siberian (what htaccess.txt does on Apache): real files are served as they are, everything
// else goes to index.php. SIB_ROOT is the absolute path of the siberian/ directory.
$root = rtrim((string) getenv('SIB_ROOT'), '/');
$path = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = $root . $path;
if ($path !== '/' && is_file($file) && substr($file, -4) !== '.php') {
    return false;
}
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $root . '/index.php';
chdir($root);
require $root . '/index.php';
