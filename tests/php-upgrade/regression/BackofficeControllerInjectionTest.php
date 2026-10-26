<?php
/**
 * Backoffice AngularJS controllers: a controller that uses an AngularJS service must declare it. A controller that
 * used $window without declaring it passed every source test and ended every save in "ReferenceError: $window is not
 * defined". The scan covers every `.controller('Name', function (args) { ... })` of the backoffice (core and modules).
 */
$sae = dirname(__DIR__, 3) . '/siberian/app/sae';

$services = ['$window', '$timeout', '$interval', '$location', '$routeParams', '$route', '$q', '$http', '$filter', '$sce', '$compile', '$document', '$log', '$cacheFactory', '$rootScope'];

/**
 * @param string $source
 * @return array [name => [declared services, body]]
 */
$controllers = function ($source) {
    $found = [];
    $matches = [];
    preg_match_all('/\.controller\(\s*[\'"]([A-Za-z0-9_]+)[\'"]\s*,\s*function\s*\(([^)]*)\)\s*\{/', $source, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
    foreach ($matches as $i => $m) {
        $end = isset($matches[$i + 1]) ? $matches[$i + 1][0][1] : strlen($source);
        $body = substr($source, $m[0][1] + strlen($m[0][0]), $end - $m[0][1] - strlen($m[0][0]));
        // a controller ends where the next registration (App.filter, App.directive, ...) starts
        $body = preg_split('/\n\s*App\.(?:filter|directive|factory|service|config|run|controller)\(/', $body)[0];
        $declared = array_map('trim', explode(',', $m[2][0]));
        $found[$m[1][0]] = [$declared, $body];
    }
    return $found;
};

/**
 * @param string $body
 * @return string the body without comments and string literals, so that '$http' in a text is not a use
 */
$code = function ($body) {
    $body = preg_replace('#/\*.*?\*/#s', '', $body);
    $body = preg_replace('#(?<![:\'"])//[^\n]*#', '', $body);
    $body = preg_replace("/'(?:[^'\\\\\n]|\\\\.)*'/", "''", $body);
    return preg_replace('/"(?:[^"\\\\\n]|\\\\.)*"/', '""', $body);
};

$files = function () use ($sae) {
    $found = glob($sae . '/design/desktop/backoffice/js/controllers/*.js') ?: [];
    foreach (glob($sae . '/modules/*/resources/design/desktop/backoffice/js/controllers/*.js') ?: [] as $file) {
        $found[] = $file;
    }
    return $found;
};

return [
    'the scan sees the backoffice controllers' => function () use ($files, $controllers) {
        $count = 0;
        foreach ($files() as $file) {
            $count += count($controllers(file_get_contents($file)));
        }
        t_true($count >= 30, "controllers found: $count");
    },

    'a controller declares every AngularJS service it uses' => function () use ($files, $controllers, $code, $services, $sae) {
        $missing = [];
        foreach ($files() as $file) {
            foreach ($controllers(file_get_contents($file)) as $name => $controller) {
                list($declared, $body) = $controller;
                $body = $code($body);
                foreach ($services as $service) {
                    $used = preg_match('/(?<![\w$.])' . preg_quote($service, '/') . '(?![\w$])/', $body) === 1;
                    if ($used && !in_array($service, $declared, true)) {
                        $missing[] = $name . ' uses ' . $service . ' (' . str_replace($sae . '/', '', $file) . ')';
                    }
                }
            }
        }
        t_equal('', implode("\n", $missing), 'undeclared services');
    },
];
