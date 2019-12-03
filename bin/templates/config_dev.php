<?php
/**
 * Siberian
 *
 * @version 4.18.3
 * @author Xtraball SAS <dev@xtraball.com>
 *
 * @configuration
 *
 */

$_config = [];
$_config['environment'] = 'development';

try {
    if (is_file(__DIR__ . '/config.user.php')) {
        require __DIR__ . '/config.user.php';
    }
} catch (\Exception $e) {
    // Skip user config!
}