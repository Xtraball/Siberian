<?php
/**
 * Tier 1 harness: tests that run real Siberian classes against a real MariaDB/MySQL.
 *
 * The server comes from the environment (tests/php-upgrade/ci.sh starts one):
 *   SIB_TEST_DB_HOST (required), SIB_TEST_DB_PORT (3306), SIB_TEST_DB_USER (root), SIB_TEST_DB_PASSWORD (root)
 *
 * Every test asks t_db_fresh() for its own empty database, so tests never see each other's tables. The databases are
 * dropped when the process ends. Nothing here touches a Siberian installation: the classes under test get an adapter
 * through Zend_Db_Table_Abstract::setDefaultAdapter(), as the application does.
 */
require_once dirname(__DIR__) . '/bootstrap.php';

$siberianRoot = dirname(__DIR__, 3) . '/siberian';

// Constants and namespaces the application sets up before any class runs
// The migration classes write exports under <APPLICATION_PATH>/../var/schema: that is a temporary tree, never the repo
$sandbox = sys_get_temp_dir() . '/sibtest-app-' . getmypid();
mkdir($sandbox . '/app', 0777, true);
register_shutdown_function(static function () use ($sandbox) {
    exec('rm -rf ' . escapeshellarg($sandbox));
});
defined('APPLICATION_PATH') || define('APPLICATION_PATH', $sandbox . '/app');
// Production behaviour: a failing data file throws instead of dumping and dying
defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'production');
define('SIB_TEST_SANDBOX', $sandbox);
spl_autoload_register(static function ($class) use ($siberianRoot) {
    if (strpos($class, 'Siberian\\') === 0) {
        $file = $siberianRoot . '/lib/' . str_replace('\\', '/', $class) . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
});

// Application classes are Module_Model_Name => app/sae/modules/Module/Model/Name.php (Zend's module autoloading)
spl_autoload_register(static function ($class) use ($siberianRoot) {
    if (preg_match('/^([A-Z][A-Za-z0-9]*)_((?:Model|Form|Controller)_.+)$/', $class, $m)) {
        $file = $siberianRoot . '/app/sae/modules/' . $m[1] . '/' . str_replace('_', '/', $m[2]) . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
});
spl_autoload_register(static function ($class) use ($siberianRoot) {
    if (strpos($class, 'Core\\') === 0) {
        $file = $siberianRoot . '/app/sae/modules/' . str_replace('\\', '/', $class) . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
});
require_once $siberianRoot . '/lib/Siberian/Pure.php';
// What Stubs::loadAliases() does for the base model, without loading the whole library
class_alias(\Core\Model\Base::class, 'Core_Model_Default');
// Stubs::loadAliases() would load the whole library; the classes under test only need these two legacy names
class_alias(\Siberian\Exception::class, 'Siberian_Exception');
// Version.php is written at install time; the repository only carries the sample
if (!class_exists(\Siberian\Version::class, false)) {
    require_once $siberianRoot . '/lib/Siberian/Version.sample.php';
}
class_alias(\Siberian\Version::class, 'Siberian_Version');
class_alias(\Siberian\Cache\Design::class, 'Siberian_Cache_Design');

// The helpers the application defines before anything else runs (__(), implode_polyfill(), ...)
require_once $siberianRoot . '/lib/System/polyfills.php';

if (!function_exists('__')) {
    /** Translation is not under test here: the text is returned as it is, sprintf'ed like the real one. */
    function __($text, ...$args)
    {
        return $args ? vsprintf($text, $args) : $text;
    }
}

/**
 * A logger that records instead of writing; the migration classes call ->info() on it.
 */
class T_Db_Logger
{
    /** @var array */
    public $lines = [];

    public function info($message, $name = null, $force = false)
    {
        $this->lines[] = (string) $message;
    }
}

/**
 * @return bool whether a database server is configured for this run
 */
function t_db_available(): bool
{
    return getenv('SIB_TEST_DB_HOST') !== false && getenv('SIB_TEST_DB_HOST') !== '';
}

/**
 * @return PDO a connection to the server itself (no database selected)
 */
function t_db_server(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', getenv('SIB_TEST_DB_HOST'), getenv('SIB_TEST_DB_PORT') ?: '3306');
        $pdo = new PDO($dsn, getenv('SIB_TEST_DB_USER') ?: 'root', getenv('SIB_TEST_DB_PASSWORD') ?: 'root', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    }

    return $pdo;
}

/**
 * Creates an empty database, makes it the default adapter of Zend_Db_Table and registers a recording logger.
 *
 * @return Zend_Db_Adapter_Pdo_Mysql
 */
function t_db_fresh(): Zend_Db_Adapter_Abstract
{
    static $created = [];
    static $registered = false;

    if (!t_db_available()) {
        throw new RuntimeException('No database: SIB_TEST_DB_HOST is not set');
    }

    $name = sprintf('sibtest_%d_%d', getmypid(), count($created) + 1);
    t_db_server()->exec("CREATE DATABASE `$name` CHARACTER SET utf8 COLLATE utf8_unicode_ci");
    $created[] = $name;

    if (!$registered) {
        $registered = true;
        register_shutdown_function(static function () use (&$created) {
            foreach ($created as $database) {
                try {
                    t_db_server()->exec("DROP DATABASE IF EXISTS `$database`");
                } catch (Throwable $e) {
                    // Best effort: the server is thrown away after the run anyway
                }
            }
        });
    }

    $adapter = Zend_Db::factory('Pdo_Mysql', [
        'host' => getenv('SIB_TEST_DB_HOST'),
        'port' => getenv('SIB_TEST_DB_PORT') ?: '3306',
        'username' => getenv('SIB_TEST_DB_USER') ?: 'root',
        'password' => getenv('SIB_TEST_DB_PASSWORD') ?: 'root',
        'dbname' => $name,
        'charset' => 'utf8',
    ]);
    // As app/Bootstrap.php does for every request
    $adapter->query("SET sql_mode = ''");
    Zend_Db_Table_Abstract::setDefaultAdapter($adapter);

    $logger = new T_Db_Logger();
    Zend_Registry::set('logger', $logger);
    Zend_Registry::set('_config', []);

    return $adapter;
}

/**
 * @return T_Db_Logger the logger of the current fresh database
 */
function t_db_logger(): T_Db_Logger
{
    return Zend_Registry::get('logger');
}

/**
 * Applies the schema file of a table to the current database the way the installer does.
 *
 * @param string $table
 * @param string $schemaFile
 * @return Siberian_Migration_Db_Table
 */
function t_db_sync(string $table, string $schemaFile): Siberian_Migration_Db_Table
{
    $migration = new Siberian_Migration_Db_Table($table);
    $migration->setSchemaPath($schemaFile);
    $migration->tableExists();

    return $migration;
}

/**
 * Writes a schema file in a temporary folder.
 *
 * @param string $table
 * @param array $columns the $schemas[$table] definition
 * @return string the path
 */
function t_schema_file(string $table, array $columns): string
{
    static $dir = null;
    if ($dir === null) {
        $dir = sys_get_temp_dir() . '/sibtest-schemas-' . getmypid();
        @mkdir($dir);
        register_shutdown_function(static function () use ($dir) {
            array_map('unlink', glob($dir . '/*.php') ?: []);
            @rmdir($dir);
        });
    }
    static $n = 0;
    $path = sprintf('%s/%d-%s.php', $dir, ++$n, $table);
    file_put_contents($path, '<?php $schemas = $schemas ?? []; $schemas[' . var_export($table, true) . '] = ' . var_export($columns, true) . ';');

    return $path;
}

/**
 * Makes the installer see the sandbox as the application root and gives it the module table, as a real install has.
 *
 * @return Zend_Db_Adapter_Abstract
 */
function t_installer_fresh(): Zend_Db_Adapter_Abstract
{
    $db = t_db_fresh();
    Zend_Registry::set('db', $db);
    // A new root per test: the installer plays data files with require_once, so a path must not repeat
    static $n = 0;
    $root = SIB_TEST_SANDBOX . '/inst' . ++$n;
    mkdir($root . '/app/sae/modules', 0777, true);
    \Core_Model_Directory::setBasePath($root);
    t_db_sync('module', dirname(__DIR__, 3) . '/siberian/app/sae/modules/Core/resources/db/schema/module.php');

    return $db;
}

/**
 * Writes a module folder under the sandbox, the way a module ships: package.json, resources/db/schema, resources/db/data.
 *
 * @param string $name
 * @param string $version
 * @param array $tables table => columns ($schemas[table] definition)
 * @param array $dataFiles file name => PHP code, run with $this = the installer table (as the real data files are)
 * @param array $package extra package.json keys (dependencies, type, code)
 */
function t_module_write(string $name, string $version, array $tables = [], array $dataFiles = [], array $package = []): void
{
    $dir = \Core_Model_Directory::getBasePathTo('app/sae/modules/' . $name);
    exec('rm -rf ' . escapeshellarg($dir));
    mkdir($dir . '/resources/db/schema', 0777, true);
    mkdir($dir . '/resources/db/data', 0777, true);
    file_put_contents($dir . '/package.json', json_encode(array_merge(['name' => $name, 'version' => $version], $package)));
    foreach ($tables as $table => $columns) {
        file_put_contents($dir . '/resources/db/schema/' . $table . '.php',
            '<?php $schemas = $schemas ?? []; $schemas[' . var_export($table, true) . '] = ' . var_export($columns, true) . ';');
    }
    foreach ($dataFiles as $file => $code) {
        file_put_contents($dir . '/resources/db/data/' . $file, "<?php\n" . $code);
    }
}

/**
 * What an update does for one module: prepare, then install (schema) and insertData if the version is newer.
 * Returns the installer model so the test can look at its state.
 */
function t_module_update(string $name, bool $force = false): Installer_Model_Installer_Module
{
    $module = new Installer_Model_Installer_Module();
    $module->prepare($name);
    $module->forceReinstall($force);
    if ($module->canUpdate()) {
        $module->install();
        $module->insertData();
    }

    return $module;
}
