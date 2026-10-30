<?php
/**
 * #945: the translator is the noisiest place of the error reports (80% of what installations send on PHP 8.5).
 *  1. fgetcsv() without its $escape argument (deprecated in 8.4)
 *  2. `case "csv";` with a semicolon (deprecated in 8.5, a compile-time message on every request)
 *  3. is_file() on a path that is not in the cache: an "Undefined array key" warning, then is_file(null)
 *  4. the bundled gettext reads its dictionary with a null domain or context as the array offset (deprecated in 8.5)
 */
$siberian = dirname(__DIR__, 3) . '/siberian';

/** Runs $fn and returns what PHP complained about while it ran (deprecations and warnings), without failing the test. */
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
    '945 Translator.php compiles without a single deprecation' => function () use ($siberian) {
        $out = [];
        exec(escapeshellarg(PHP_BINARY) . ' -d error_reporting=-1 -d display_errors=1 -l '
            . escapeshellarg($siberian . '/app/sae/modules/Core/Model/Translator.php') . ' 2>&1', $out, $code);
        t_equal(0, $code, 'the file is valid');
        t_equal([], array_values(array_filter($out, function ($line) {
            return stripos($line, 'deprecated') !== false;
        })), 'no compile-time deprecation (case with a semicolon)');
        t_equal(0, preg_match('/case\s+"[^"]*"\s*;/', file_get_contents($siberian . '/app/sae/modules/Core/Model/Translator.php')), 'no case followed by a semicolon');
    },

    '945 the mobile list is read with an explicit escape character' => function () use ($siberian, $complaints) {
        require_once $siberian . '/app/sae/modules/Core/Model/Translator.php';

        $file = sys_get_temp_dir() . '/t945_' . bin2hex(random_bytes(4)) . '.list';
        file_put_contents($file, "\"Hello\";\"Ahoj\"\n\"Price %s\";\"Cena %s\"\n\"Say \\\"hi\\\"\";\"x\"\n;\"empty first column\"\n");
        try {
            [$keys, $seen] = $complaints(function () use ($file) {
                return Core_Model_Translator::readCsvKeys($file);
            });
        } finally {
            @unlink($file);
        }
        t_equal([], $seen, 'nothing is reported while reading');
        t_equal(['Hello', 'Say \\"hi\\"'], $keys, 'keys with a placeholder or without a name are skipped');
    },

    '945 a missing or unreadable mobile list is skipped, not fatal' => function () use ($siberian, $complaints) {
        require_once $siberian . '/app/sae/modules/Core/Model/Translator.php';

        [$keys, $seen] = $complaints(function () {
            return Core_Model_Translator::readCsvKeys(sys_get_temp_dir() . '/t945_does_not_exist.list');
        });
        t_equal([], $keys, 'no keys');
        t_equal([], $seen, 'no warning either');
    },

    '945 a translation file that is not in the cache is an empty path, with no warning' => function () use ($siberian, $complaints) {
        require_once $siberian . '/app/sae/modules/Core/Model/Translator.php';

        $cache = ['fr' => ['c_front.csv' => '/tmp/a/c_front.csv']];
        [$got, $seen] = $complaints(function () use ($cache) {
            return [
                Core_Model_Translator::cachedFile($cache, 'fr', 'c_front.csv'),
                Core_Model_Translator::cachedFile($cache, 'fr', 'c_front.po'),
                Core_Model_Translator::cachedFile($cache, 'de', 'c_front.po'),
            ];
        });
        t_equal(['/tmp/a/c_front.csv', '', ''], $got, 'the path when there is one, an empty string otherwise');
        t_equal([], $seen, 'no undefined key');
        t_equal(false, is_file(''), 'an empty path is not a file');
    },

    '945 the translation lookups of Translator.php go through the cache helper' => function () use ($siberian) {
        $code = file_get_contents($siberian . '/app/sae/modules/Core/Model/Translator.php');
        t_equal(0, preg_match('/\$cachedFiles\[\$langId\]\["\{\$fileBase\}\.(csv|po)"\]/', $code), 'no direct read of a cache entry that may be missing');
    },

    '945 the bundled gettext translator answers without null array offsets' => function () use ($complaints) {
        [$got, $seen] = $complaints(function () {
            $t = new Gettext\Translator();
            $t->loadTranslations(['messages' => ['' => ['Hello' => ['Ahoj']]]]);
            $none = new Gettext\Translator();

            return [
                $t->gettext('Hello'),
                $t->gettext('Unknown'),
                $none->gettext('Hello'),
                $none->ngettext('one', 'many', 1),
                $none->ngettext('one', 'many', 2),
                $none->pgettext('ctx', 'Hello'),
            ];
        });
        t_equal([], $seen, 'no deprecation, no warning');
        t_equal(['Ahoj', 'Unknown', 'Hello', 'one', 'many', 'Hello'], $got, 'translations and the fallbacks are unchanged');
    },
];
