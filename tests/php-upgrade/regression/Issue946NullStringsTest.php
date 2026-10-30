<?php
/**
 * #946: null reaching string functions on PHP 8.5.
 *  1. Siberian\Security::checkKeyValue() scans every GET, POST and body value; an empty JSON value is null, and
 *     strpos(null) / preg_match(null) are deprecated on every request that carries one
 *  2. Mobile_Detect::isDevice() runs preg_match() on the User-Agent, which is null when the header is missing
 */
$siberian = dirname(__DIR__, 3) . '/siberian';

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

return [
    '946 Security looks at null, numbers and text without complaint' => function () use ($siberian, $complaints) {
        require_once $siberian . '/lib/Siberian/Security.php';

        [$hits, $seen] = $complaints(function () {
            return [
                Siberian\Security::triggeredBy('name', null),
                Siberian\Security::triggeredBy(null, null),
                Siberian\Security::triggeredBy(5, 12.5),
                Siberian\Security::triggeredBy('name', true),
                Siberian\Security::triggeredBy('name', ['not', 'a', 'scalar']),
            ];
        });
        t_equal([], $seen, 'nothing is reported');
        t_equal([[], [], [], [], []], $hits, 'none of them is suspicious');
    },

    '946 Security still reports what it reported before, key first and then value, trigger by trigger' => function () use ($siberian) {
        require_once $siberian . '/lib/Siberian/Security.php';

        $php = Siberian\Security::TRIGGERS[0];
        $sql = Siberian\Security::TRIGGERS[1];
        t_equal([[2, $php]], Siberian\Security::triggeredBy('comment', '<script>alert(1)</script>'), 'a script in the value');
        t_equal([[1, $php]], Siberian\Security::triggeredBy('<?php echo 1', 'fine'), 'php in the key');
        t_equal([[1, $php], [2, $php]], Siberian\Security::triggeredBy('<script>', '<?php'), 'key then value for the same trigger');
        t_equal([[2, $sql]], Siberian\Security::triggeredBy('q', 'DROP TABLE users '), 'sql in the value');
        t_equal([], Siberian\Security::triggeredBy('title', "It's a perfectly normal sentence."), 'text is left alone');
    },

    '946 checkKeyValue normalizes its input before any string function' => function () use ($siberian) {
        $code = file_get_contents($siberian . '/lib/Siberian/Security.php');
        $start = strpos($code, 'public static function checkKeyValue');
        $body = substr($code, $start, strpos($code, "\n    }\n", $start) - $start);
        t_true(strpos($body, '$value = self::text($value);') !== false, 'the value is normalized');
        t_true(strpos($body, '$key = self::text($key);') !== false, 'the key is normalized');
        t_true(strpos($body, '$value = self::text($value);') < strpos($body, 'strpos($value'), 'before the first strpos()');
        t_equal(0, preg_match('/preg_match\(\s*\'~\'/', $body), 'the preg_match calls live in triggeredBy()');
    },

    '946 Mobile_Detect without a User-Agent header is not mobile and says nothing' => function () use ($siberian, $complaints) {
        require_once $siberian . '/lib/Mobile/Detect.php';

        $saved = $_SERVER;
        unset($_SERVER['HTTP_USER_AGENT'], $_SERVER['HTTP_ACCEPT'], $_SERVER['HTTP_X_WAP_PROFILE'], $_SERVER['HTTP_PROFILE']);
        try {
            [$detect, $seen] = $complaints(function () {
                $d = new Mobile_Detect();
                $d->isIphone();

                return $d;
            });
        } finally {
            $_SERVER = $saved;
        }
        t_equal([], $seen, 'no deprecation');
        t_equal(false, $detect->isMobile(), 'not mobile');
        t_equal(null, $detect->userAgent, 'the user agent is still null');
    },

    '946 Mobile_Detect still recognizes a phone and a tablet' => function () use ($siberian, $complaints) {
        require_once $siberian . '/lib/Mobile/Detect.php';

        $saved = $_SERVER;
        try {
            $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148';
            [$phone, $seen] = $complaints(function () {
                return new Mobile_Detect();
            });
            $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Linux; Android 14; Pixel Tablet) AppleWebKit/537.36 Safari/537.36';
            $tablet = new Mobile_Detect();
        } finally {
            $_SERVER = $saved;
        }
        t_equal([], $seen, 'no deprecation');
        t_true($phone->isMobile() && $phone->isIphone(), 'an iPhone is a mobile iPhone');
        t_true($tablet->isAndroidtablet(), 'an Android without "mobile" is a tablet');
    },
];
