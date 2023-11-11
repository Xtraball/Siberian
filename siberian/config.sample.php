<?php
/**
 * Siberian
 *
 * @version 5.0.8
 * @author Xtraball SAS <dev@xtraball.com>
 *
 * @configuration
 *
 */

$_config = [];
$_config['environment'] = 'production';
$_config['redirect_https'] = false;

try {
    if (is_file(__DIR__ . '/config.user.php')) {
        require __DIR__ . '/config.user.php';
    }
} catch (\Exception $e) {
    // Skip user config!
}