<?php
/**
 * #952: warnings and deprecations from null and false values that reached foreach and string functions.
 * Behaviour is tested where the class loads without the application, the other places are checked in the source
 * (the value is turned into the empty string or array first, which is what PHP did with it anyway).
 */
$root = dirname(__DIR__, 3) . '/siberian';

/** Runs $fn and returns [result, what PHP complained about while it ran], without failing the test. */
$complaints = function (callable $fn) {
    $seen = [];
    set_error_handler(function ($no, $msg) use (&$seen) {
        if (!(error_reporting() & $no)) {
            return false; // @-suppressed
        }
        $seen[] = $msg;

        return true;
    });
    try {
        $result = $fn();
    } finally {
        restore_error_handler();
    }

    return [$result, $seen];
};

/** The text of a source file. */
$source = function (string $relative) use ($root) {
    return file_get_contents($root . '/' . $relative);
};

return [
    '952 the size reader of the module page understands 128M, 2G, 8k and plain numbers' => function () use ($source, $complaints) {
        $code = $source('app/sae/modules/Installer/controllers/Backoffice/ModuleController.php');
        $start = strpos($code, 'function _return_bytes($val)');
        $end = strpos($code, 'return $val;', $start);
        $function = substr($code, $start, $end - $start) . "return \$val;\n        }";
        if (!function_exists('t952_bytes')) {
            eval(str_replace('_return_bytes', 't952_bytes', $function));
        }

        [$values, $seen] = $complaints(function () {
            return [t952_bytes('128M'), t952_bytes('2G'), t952_bytes('8k'), t952_bytes('1024'), t952_bytes(''), t952_bytes(null), t952_bytes(' 64M ')];
        });
        t_equal([], $seen, 'nothing is reported');
        t_equal([134217728, 2147483648, 8192, '1024', 0, 0, 67108864], $values, 'bytes');
    },

    '952 an HTTP response without transfer or content encoding decodes without complaint' => function () use ($root, $complaints) {
        require_once $root . '/lib/Zend/Http/Response.php';

        [$body, $seen] = $complaints(function () {
            return (new Zend_Http_Response(200, [], 'hello'))->getBody();
        });
        t_equal([], $seen, 'nothing is reported');
        t_equal('hello', $body, 'body');
    },

    '952 Zend_Uri takes a null uri without a deprecation' => function () use ($root, $complaints) {
        require_once $root . '/lib/Zend/Uri.php';

        [$thrown, $seen] = $complaints(function () {
            try {
                Zend_Uri::factory(null);
            } catch (Exception $e) {
                return get_class($e);
            }

            return null;
        });
        // loading Zend_Exception for the first time may report its own signature, which is not what is tested here
        $seen = array_values(array_filter($seen, function ($message) {
            return strpos($message, 'Implicitly marking') === false;
        }));
        t_equal([], $seen, 'nothing is reported');
        t_true($thrown !== null, 'still refused as before');
    },

    '952 the base url of a request without REQUEST_URI is detected without complaint' => function () use ($root, $complaints) {
        require_once $root . '/lib/Zend/Controller/Request/Http.php';

        $saved = [$_SERVER['REQUEST_URI'] ?? null, $_SERVER['SCRIPT_NAME'] ?? null, $_SERVER['SCRIPT_FILENAME'] ?? null];
        unset($_SERVER['REQUEST_URI'], $_SERVER['HTTP_X_REWRITE_URL'], $_SERVER['IIS_WasUrlRewritten'], $_SERVER['UNENCODED_URL'], $_SERVER['ORIG_PATH_INFO']);
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['SCRIPT_FILENAME'] = '/var/www/html/index.php';
        try {
            [, $seen] = $complaints(function () {
                $request = new Zend_Controller_Request_Http();
                $request->setBaseUrl();

                return $request->getBaseUrl();
            });
        } finally {
            foreach (['REQUEST_URI', 'SCRIPT_NAME', 'SCRIPT_FILENAME'] as $i => $key) {
                if ($saved[$i] === null) {
                    unset($_SERVER[$key]);
                } else {
                    $_SERVER[$key] = $saved[$i];
                }
            }
        }
        t_equal([], $seen, 'nothing is reported');
    },

    '952 places that are checked in the source' => function () use ($source) {
        $expect = [
            ['app/sae/design/desktop/backoffice/template/page/layout/header.phtml', 'Siberian_Module::getMenus() ?: []'],
            ['app/sae/modules/Application/Model/Option.php', "json_decode((string) \$this->getData('custom_fields')"],
            ['lib/Siberian/Layout.php', '->xpath($path) ?: [];'],
            ['app/sae/modules/Core/Model/Directory.php', '$path = (string) $path;'],
            ['app/sae/modules/Core/Controller/Default/Abstract.php', "str_replace('#', '', \$color ?? '')"],
            ['app/sae/modules/Core/Controller/Default/Abstract.php', 'stripos((string) $image_id, Core_Model_Directory::getBasePathTo())'],
            ['app/sae/modules/Application/Model/Application/Abstract.php', "(string) \$this->getData('privacy_policy_gdpr')"],
            ['app/sae/modules/Backoffice/Model/Notification.php', 'strpos((string) $link,'],
            ['app/sae/modules/Front/Controller/Api/Base.php', 'trim((string) $application->getPrivacyPolicy())'],
            ['app/sae/modules/Front/Controller/Api/Base.php', 'trim((string) $application->getPrivacyPolicyTitle())'],
            ['lib/Zend/Controller/Request/Http.php', '$requestUri = (string) $this->getRequestUri();'],
        ];
        $missing = [];
        foreach ($expect as [$file, $needle]) {
            if (strpos($source($file), $needle) === false) {
                $missing[] = $file . ' :: ' . $needle;
            }
        }
        t_equal([], $missing, 'places still on the old form');
        t_equal(3, substr_count($source('lib/Siberian/Layout.php'), '->xpath($path) ?: [];'), 'three xpath loops in Layout');
    },
];
