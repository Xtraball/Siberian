<?php

// php -S router for the downloader: /api/v1/* goes to the API front controller, the rest is served as usual.
$path = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (strpos($path, '/api/v1/') === 0) {
    require '/app/public/api/index.php';

    return true;
}

return false;
