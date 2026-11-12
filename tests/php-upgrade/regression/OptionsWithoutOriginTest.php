<?php
/**
 * An OPTIONS request without an Origin header (a health check, a scanner, curl -X OPTIONS) logged
 * "Undefined array key HTTP_ORIGIN" from index.php on PHP 8. The header value is now read with ?? like the rest.
 */
$root = dirname(__DIR__, 3) . '/siberian';

return [
    'index.php reads HTTP_ORIGIN without assuming it is set' => function () use ($root) {
        $code = file_get_contents($root . '/index.php');
        t_true(strpos($code, "(\$_SERVER['HTTP_ORIGIN'] ?? '')") !== false, 'guarded with ??');
        // the other read of HTTP_ORIGIN is already inside if (!empty(...))
        t_equal(1, substr_count($code, "' . \$_SERVER['HTTP_ORIGIN']"), 'one unguarded-looking read left, the one under !empty()');
    },
];
