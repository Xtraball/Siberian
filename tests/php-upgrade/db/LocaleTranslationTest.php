<?php
/**
 * Tier 1: the translation plumbing on the real core classes. Which catalog files a module contributes (first one
 * wins), how Core_Model_Translator reads csv and po files and layers default over base over user, the locale tables,
 * and what the placeholders and the JS escapes of the gettext helpers do to a translated text.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
Core_Model_Directory::setBasePath($siberian);

require_once $siberian . '/lib/Siberian/Json.php';
require_once $siberian . '/lib/Siberian/Locale.php';
require_once $siberian . '/lib/Siberian/Locale/Catalogs.php';
require_once $siberian . '/lib/Siberian/Translation.php';
require_once $siberian . '/app/sae/modules/Core/Model/Translator.php';

use Siberian\Locale\Catalogs;

/** A scratch directory that the test removes itself. */
$scratch = static function (callable $body) {
    $dir = sys_get_temp_dir() . '/loctr-' . bin2hex(random_bytes(4));
    mkdir($dir, 0777, true);
    try {
        return $body($dir);
    } finally {
        $rm = static function ($path) use (&$rm) {
            if (is_dir($path) && !is_link($path)) {
                foreach (scandir($path) as $entry) {
                    if ($entry !== '.' && $entry !== '..') {
                        $rm($path . '/' . $entry);
                    }
                }
                rmdir($path);
            } else {
                unlink($path);
            }
        };
        $rm($dir);
    }
};

/** The gettext helpers in a child process (see the fixture), decoded once. */
$probe = static function () use ($siberian) {
    static $result = null;
    if ($result === null) {
        $script = $siberian . "/../tests/php-upgrade/fixtures/gettext_probe.php";
        $out = shell_exec(escapeshellarg(PHP_BINARY) . " " . escapeshellarg($script) . " 2>&1");
        $result = json_decode((string) $out, true);
        if (!is_array($result)) {
            throw new RuntimeException("gettext probe failed: " . substr((string) $out, 0, 300));
        }
    }
    return $result;
};

$write = static function ($path, $content) {
    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }
    file_put_contents($path, $content);
};

$po = static function (array $entries) {
    $out = "msgid \"\"\nmsgstr \"\"\n\"Language: en\\n\"\n\n";
    foreach ($entries as $id => $str) {
        $out .= "msgid \"" . $id . "\"\nmsgstr \"" . $str . "\"\n\n";
    }
    return $out;
};

return [
    // Catalogs::indexModule
    'a module without translations adds nothing' => function () use ($scratch) {
        $scratch(function ($dir) {
            t_equal([], Catalogs::indexModule($dir . '/Nope', []));
            mkdir($dir . '/Empty');
            t_equal(['x' => ['a.po' => '/a']], Catalogs::indexModule($dir . '/Empty', ['x' => ['a.po' => '/a']]));
        });
    },
    'a module registers its po and csv files by language, and its lists apart' => function () use ($scratch, $write) {
        $scratch(function ($dir) use ($write) {
            $t = $dir . '/Mod/resources/translations';
            $write($t . '/fr/c_mod.po', '');
            $write($t . '/fr/old.csv', '');
            $write($t . '/fr/notes.txt', '');
            $write($t . '/base/c_mod.po', '');
            $write($t . '/mobile/app.list', '');
            $write($t . '/README', '');
            $cache = Catalogs::indexModule($dir . '/Mod', []);
            $names = array_keys($cache['fr']);
            sort($names);
            t_equal(['c_mod.po', 'old.csv'], $names, 'only po and csv');
            t_equal($t . '/fr/c_mod.po', $cache['fr']['c_mod.po']);
            t_equal($t . '/base/c_mod.po', $cache['base']['c_mod.po']);
            t_equal([$t . '/mobile/app.list'], $cache['mobile_list']);
            t_true(!isset($cache['README']), 'a file is not a language');
        });
    },
    'the first file registered for a language and a name wins' => function () use ($scratch, $write) {
        $scratch(function ($dir) use ($write) {
            $write($dir . '/A/resources/translations/fr/c.po', '');
            $write($dir . '/B/resources/translations/fr/c.po', '');
            $write($dir . '/B/resources/translations/fr/d.po', '');
            $cache = Catalogs::indexModule($dir . '/A', []);
            $cache = Catalogs::indexModule($dir . '/B', $cache);
            t_equal($dir . '/A/resources/translations/fr/c.po', $cache['fr']['c.po']);
            t_equal($dir . '/B/resources/translations/fr/d.po', $cache['fr']['d.po']);
            $again = Catalogs::indexModule($dir . '/A', $cache);
            t_equal($cache, $again, 'indexing twice changes nothing but the lists');
        });
    },

    // Core_Model_Translator
    'cachedFile gives the path, or an empty string for anything missing or odd' => function () {
        $cache = ['fr' => ['a.po' => '/x/a.po', 'bad.po' => ['no']]];
        t_equal('/x/a.po', Core_Model_Translator::cachedFile($cache, 'fr', 'a.po'));
        t_equal('', Core_Model_Translator::cachedFile($cache, 'fr', 'b.po'));
        t_equal('', Core_Model_Translator::cachedFile($cache, 'de', 'a.po'));
        t_equal('', Core_Model_Translator::cachedFile($cache, 'fr', 'bad.po'));
        t_equal('', Core_Model_Translator::cachedFile([], 'fr', 'a.po'));
    },
    'parseType reads a po file: translated entries only, with context and flags' => function () use ($scratch, $write) {
        $scratch(function ($dir) use ($write) {
            $write($dir . '/c.po',
                "msgid \"\"\nmsgstr \"\"\n\"Language: fr\\n\"\n\n"
                . "msgid \"Save\"\nmsgstr \"Enregistrer\"\n\n"
                . "msgid \"Empty\"\nmsgstr \"\"\n\n"
                . "msgid \"Blank\"\nmsgstr \"   \"\n\n"
                . "msgctxt \"admin\"\nmsgid \"Hello\"\nmsgstr \"Bonjour\"\n\n"
                . "#, fuzzy\nmsgid \"Fuzzy\"\nmsgstr \"Flou\"\n\n"
                . "msgid \"Say \\\"hi\\\"\"\nmsgstr \"Dis \\\"salut\\\"\"\n");
            $out = Core_Model_Translator::parseType([], 'c.po', $dir . '/c.po', 'po');
            $e = $out['c.po'];
            t_equal('Enregistrer', $e['Save']['value']);
            t_true(empty($e['Save']['context']), 'no context');
            t_true(!isset($e['Empty']) && !isset($e['Blank']), 'empty and blank translations are not kept');
            t_equal('Bonjour', $e['Hello']['value']);
            t_equal('admin', $e['Hello']['context']);
            t_true(in_array('fuzzy', $e['Fuzzy']['flags'], true), 'flags');
            t_equal('Dis "salut"', $e['Say "hi"']['value'], 'quotes');
        });
    },
    'parseType reads a csv dictionary, with ; and escaped quotes, and a later file wins' => function () use ($scratch, $write) {
        $scratch(function ($dir) use ($write) {
            $write($dir . '/a.csv', "\"Save\";\"Enregistrer\"\n\"Cancel\";\"\"\n\"One\";\"Un\"\n");
            $write($dir . '/b.csv', "\"Save\";\"Sauvegarder\"\n");
            $out = Core_Model_Translator::parseType([], 'f.csv', $dir . '/a.csv', 'csv');
            t_equal('Enregistrer', $out['f.csv']['Save']['value']);
            t_true(!isset($out['f.csv']['Cancel']), 'empty is not kept');
            $out = Core_Model_Translator::parseType($out, 'f.csv', $dir . '/b.csv', 'csv');
            t_equal('Sauvegarder', $out['f.csv']['Save']['value'], 'the later file wins');
            t_equal('Un', $out['f.csv']['One']['value'], 'what it does not redefine stays');
        });
    },
    'parseType with a type it does not know changes nothing, and drops empty files' => function () use ($scratch, $write) {
        $scratch(function ($dir) use ($write) {
            $write($dir . '/a.txt', 'x');
            t_equal([], Core_Model_Translator::parseType([], 'a.txt', $dir . '/a.txt', 'txt'));
            t_equal(['k.po' => ['A' => 1]], Core_Model_Translator::parseType(['k.po' => ['A' => 1]], 'z', $dir . '/a.txt', 'txt'));
        });
    },
    'readCsvKeys lists the first column, without the %s lines, and survives a missing file' => function () use ($scratch, $write) {
        $scratch(function ($dir) use ($write) {
            $write($dir . '/k.csv', "\"Save\";\"x\"\n\"File %s missing\";\"y\"\n\n\"Cancel\";\"z\"\n\"\";\"w\"\n");
            t_equal(['Save', 'Cancel'], Core_Model_Translator::readCsvKeys($dir . '/k.csv'));
            t_equal([], Core_Model_Translator::readCsvKeys($dir . '/nope.csv'));
        });
    },
    'readCsvKeys reads a line longer than 1024 characters as one line' => function () use ($scratch, $write) {
        $scratch(function ($dir) use ($write) {
            $long = str_repeat('word ', 300);
            $write($dir . '/k.csv', "\"First\";\"{$long}\"\n\"Second\";\"b\"\n");
            t_equal(['First', 'Second'], Core_Model_Translator::readCsvKeys($dir . '/k.csv'));
        });
    },

    // Locale
    'the locale table is read once and lists real locales' => function () {
        $all = Siberian_Locale::getAllLocales();
        t_true(is_array($all) && count($all) > 50, 'many locales');
        t_true(Siberian_Locale::getAllLocales() === $all);
    },
    'regions come by territory, empty for an unknown one' => function () {
        t_true(count(Siberian_Locale::getRegions()) > 0);
        $fr = Siberian_Locale::getRegionByTerritory('FR');
        t_true(is_array($fr) && count($fr) > 0, 'France has regions');
        t_equal([], Siberian_Locale::getRegionByTerritory('ZZ'));
        t_equal([], Siberian_Locale::getRegionByTerritory(''));
        t_true(is_array(Siberian_Locale::getEuroZoneTerritoryDatas()));
    },


    // Translation::extractAll
    'extractAll prints the texts of the code that no default catalog has' => function () use ($scratch, $write, $siberian) {
        $scratch(function ($dir) use ($write, $siberian) {
            $write($dir . '/languages/default/c.csv', "\"Known text\",\"Known text\"\n");
            $write($dir . '/app/local/modules/M/a.php', "<?php echo __(\"Known text\"); echo __(\"Brand new\"); echo \$x->_('Single new');");
            $write($dir . '/app/local/modules/M/skip.txt', '__("Not scanned")');
            Core_Model_Directory::setBasePath($dir);
            try {
                ob_start();
                Siberian\Translation::extractAll('M');
                $out = ob_get_clean();
            } finally {
                Core_Model_Directory::setBasePath($siberian);
            }
            t_true(strpos($out, 'msgid "Brand new"') !== false, 'double quoted');
            t_true(strpos($out, 'msgid "Single new"') !== false, 'single quoted, ->_()');
            t_true(strpos($out, 'Known text') === false, 'already in a catalog');
            t_true(strpos($out, 'Not scanned') === false, 'only php, phtml and csv');
            t_true(strpos($out, 'msgctxt') === false, 'no context asked');
        });
    },
    'extractAll with a context prints msgctxt and looks at p__ only' => function () use ($scratch, $write, $siberian) {
        $scratch(function ($dir) use ($write, $siberian) {
            $write($dir . '/languages/default/c.csv', "\"x\",\"x\"\n");
            $write($dir . '/app/local/modules/M/a.php', "<?php echo p__(\"ctx\", \"Alpha\"); echo __(\"Beta\");");
            Core_Model_Directory::setBasePath($dir);
            try {
                ob_start();
                Siberian\Translation::extractAll('M', 'ctx');
                $out = ob_get_clean();
            } finally {
                Core_Model_Directory::setBasePath($siberian);
            }
            t_true(strpos($out, 'msgctxt "ctx"') !== false, 'context');
            t_true(strpos($out, 'Beta') === false, '__ is not scanned with a context');
        });
    },
    'registerExtractor records the module and path by context' => function () {
        global $extractModules;
        Siberian\Translation::registerExtractor('ctxA', 'ModA', '/p');
        Siberian\Translation::registerExtractor('ctxB', 'ModB');
        t_equal(['module' => 'ModA', 'path' => '/p'], $extractModules['ctxA']);
        t_equal(['module' => 'ModB', 'path' => null], $extractModules['ctxB']);
    },
    // placeholders and JS escapes of the gettext helpers (the real functions, in a child process)
    'placeholders: a value fills %s, an array fills named ones, a broken translation falls back to the original' => function () use ($probe) {
        $r = $probe();
        t_equal('Bonjour Bob', $r['filled']);
        t_equal('Salut Bob', $r['array']);
        t_equal("The file 'a.txt' does not exist", $r['broken'], 'a ValueError would stop the page');
        t_equal('Simple', $r['plain']);
        t_equal('Unknown text', $r['unknown'], 'an untranslated text is itself');
    },
    'a context picks its own translation, and falls back to the original' => function () use ($probe) {
        $r = $probe();
        t_equal('Bonjour admin Bob', $r['context']);
        t_equal('Ici', $r['context_plain']);
        t_equal('Not translated x', $r['context_missing']);
    },
    'gettext_vsprintf: the original when the translation is broken, the raw text as last resort' => function () use ($probe) {
        $r = $probe();
        t_equal('File a.txt', $r['vsprintf_ok']);
        t_equal('File a.txt', $r['vsprintf_fallback']);
        t_equal('Súbor %', $r['vsprintf_raw']);
        t_equal('Broken %', $r['vsprintf_both']);
        t_equal('100%', $r['vsprintf_percent']);
    },
    'the JS escapes protect the quote of their destination, after the placeholders are filled' => function () use ($probe) {
        $r = $probe();
        t_equal('Dis \"salut\"', $r['js'], 'double quote by default');
        t_equal('Say "hi" it\\\'s', $r['jss'], 'single quote');
        t_equal('Say \"hi\" it\'s', $r['jsd'], 'double quote');
        t_equal('Bonjour \"Bob\"', $r['js_args'], 'a value with a quote is escaped too');
        t_equal('Bonjour admin \"Bob\"', $r['pjs']);
        t_equal('Ici', $r['pjs_default']);
    },
    '__title makes a text safe for an html attribute' => function () use ($probe) {
        t_equal('it&acute;s &quot;ok&quot;', $probe()['title']);
    },
];
