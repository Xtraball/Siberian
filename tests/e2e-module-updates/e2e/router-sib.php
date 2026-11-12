<?php

// php -S router for Siberian: real files are served, everything else goes to index.php (htaccess.txt rules).
$path = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = '/srv/sib' . $path;
if ($path !== '/' && is_file($file) && substr($file, -4) !== '.php') {
    return false;
}
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = '/srv/sib/index.php';
chdir('/srv/sib');
require '/srv/sib/index.php';
