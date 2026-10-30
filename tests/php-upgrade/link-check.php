<?php
/**
 * Loads one first-party PHP file the way the application would, so that a class whose methods do not match its
 * parent (a fatal error since PHP 8, only a warning before) is found. `php -l` cannot see it: the check happens
 * when the class is linked to its parent, which needs the parent to be autoloaded.
 *
 * Usage: php linkcheck.php <root> <edition sae|mae|pe> <file>
 * Exit code 0 when the file loads, anything else (255 for a fatal error) when it does not.
 */
[, $root, $edition, $file] = $argv;

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', '1');
chdir($root);

define('APPLICATION_PATH', $root . '/app');
define('APPLICATION_ENV', 'production');

$paths = [$root . '/app/local/modules'];
foreach (['pe' => ['pe', 'mae', 'sae'], 'mae' => ['mae', 'sae'], 'sae' => ['sae']][$edition] as $e) {
    $paths[] = $root . '/app/' . $e . '/modules';
}
$paths[] = $root . '/lib';
$paths[] = get_include_path();
set_include_path(implode(PATH_SEPARATOR, $paths));

require_once $root . '/lib/System/polyfills.php';
require_once $root . '/lib/Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->setFallbackAutoloader(true);
foreach (['Core', 'Symfony', 'Plesk', 'PListEditor'] as $namespace) {
    $loader->registerNamespace($namespace);
}
require_once $root . '/lib/vendor/autoload.php';

// The Siberian\ namespace is psr-4 on lib/Siberian
spl_autoload_register(function ($class) use ($root) {
    if (strpos($class, 'Siberian\\') === 0) {
        $path = $root . '/lib/Siberian/' . str_replace('\\', '/', substr($class, 9)) . '.php';
        if (is_file($path)) {
            require_once $path;
        }
    }
});

// What Bootstrap::_initPaths loads before anything else
foreach (['lib/Siberian/Pure.php', 'lib/Siberian/Stubs.php'] as $helper) {
    if (is_readable($root . '/' . $helper)) {
        include_once $root . '/' . $helper;
    }
}
if (class_exists('Core_Model_Directory')) {
    Core_Model_Directory::setBasePath($root);
}
if (class_exists('Siberian\Stubs', false)) {
    \Siberian\Stubs::loadAliases();
}

ob_start();
include_once $file;
ob_end_clean();
exit(0);
