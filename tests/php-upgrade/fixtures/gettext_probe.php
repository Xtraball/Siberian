<?php
/**
 * Child process of LocaleTranslationTest: the real gettext helpers (__, p__, gettext_vsprintf) and the JS escapes of
 * Pure.php, with a translator holding a few catalogs. A separate process because the test suites stub p__ for the
 * others and the real one cannot be declared next to a stub. Prints a JSON object of results.
 */
$root = dirname(__DIR__, 3) . '/siberian';
set_include_path($root . '/lib' . PATH_SEPARATOR . get_include_path());
require_once $root . '/lib/vendor/autoload.php';
require_once $root . '/lib/Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()->registerNamespace(['Siberian_']);
define('APPLICATION_ENV', 'production');
Zend_Registry::set('_config', []);
require_once $root . '/lib/Siberian/Pure.php';

$translator = new Gettext\Translator();
$translator->loadTranslations(['messages' => [
    '' => [
        'Hello %s' => ['Bonjour %s'],
        'The file \'%s\' does not exist' => ['Súbor % neexistuje'],
        'Say "hi"' => ['Dis "salut"'],
        'Line' => ["a\\b"],
        'Plain' => ['Simple'],
        'Hi :name' => ['Salut :name'],
    ],
    'admin' => [
        'Hello %s' => ['Bonjour admin %s'],
        'Only here' => ['Ici'],
    ],
]]);
$translator->register();

$out = [
    'filled' => __('Hello %s', 'Bob'),
    'array' => __('Hi :name', [':name' => 'Bob']),
    'broken' => __('The file \'%s\' does not exist', 'a.txt'),
    'context' => p__('admin', 'Hello %s', 'Bob'),
    'context_plain' => p__('admin', 'Only here'),
    'context_missing' => p__('admin', 'Not translated %s', 'x'),
    'plain' => __('Plain'),
    'unknown' => __('Unknown text'),
    'js' => __js('Say "hi"'),
    'jss' => __jss('Say "hi" it\'s'),
    'jsd' => __jsd('Say "hi" it\'s'),
    'js_args' => __js('Hello %s', '"', '"Bob"'),
    'pjs' => p__js('admin', 'Hello %s', '"', '"Bob"'),
    'pjs_default' => p__js('admin', 'Only here'),
    'title' => __title('it\'s "ok"'),
    'vsprintf_ok' => gettext_vsprintf('File %s', ['a.txt'], 'File %s'),
    'vsprintf_fallback' => gettext_vsprintf('Súbor %', ['a.txt'], 'File %s'),
    'vsprintf_raw' => gettext_vsprintf('Súbor %', ['a.txt'], null),
    'vsprintf_both' => gettext_vsprintf('Broken %', ['a'], 'Broken %'),
    'vsprintf_percent' => gettext_vsprintf('100%%', []),
];

echo json_encode($out);
