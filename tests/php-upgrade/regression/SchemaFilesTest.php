<?php
/**
 * Module install: only <table>.php files of resources/db/schema are table definitions.
 *
 * Installer_Model_Installer_Module used to take every file of the folder as a table, so a stray install.sql (or
 * .DS_Store, .gitkeep, a backup, a sub folder) became "CREATE TABLE `install.sql` ( )" and the install failed with
 * "Unable to create table 'install.sql' ... #99009 execSafe error".
 *
 * The real schemaFilesIn() is loaded out of the source file (the model needs the whole framework to be autoloaded).
 */
$source = file_get_contents(dirname(__DIR__, 3) . '/siberian/app/sae/modules/Installer/Model/Installer/Module.php');

$probe = function () use ($source) {
    if (!class_exists('SchemaFilesProbe', false)) {
        $start = strpos($source, 'public static function schemaFilesIn(');
        if ($start === false) {
            return null;
        }
        $open = strpos($source, '{', $start);
        $depth = 0;
        for ($i = $open, $n = strlen($source); $i < $n; $i++) {
            if ($source[$i] === '{') {
                $depth++;
            } elseif ($source[$i] === '}' && --$depth === 0) {
                break;
            }
        }
        eval('class SchemaFilesProbe { ' . substr($source, $start, $i - $start + 1) . ' }');
    }
    return 'SchemaFilesProbe';
};

$tmp = function () {
    $dir = sys_get_temp_dir() . '/schema-files-' . getmypid() . '-' . mt_rand();
    mkdir($dir);
    return $dir;
};

$clean = function ($dir) {
    foreach (glob($dir . '/{,.}*', GLOB_BRACE) as $f) {
        if (basename($f) === '.' || basename($f) === '..') {
            continue;
        }
        is_dir($f) ? rmdir($f) : unlink($f);
    }
    rmdir($dir);
};

return [
    'a stray install.sql is not a table' => function () use ($probe, $tmp, $clean) {
        $class = $probe();
        t_true($class !== null, 'Module::schemaFilesIn() exists');
        $dir = $tmp();
        file_put_contents($dir . '/phonebook.php', '<?php return [];');
        file_put_contents($dir . '/install.sql', 'CREATE TABLE x (id int);');
        $files = $class::schemaFilesIn($dir);
        t_equal(['phonebook'], array_keys($files), 'only the .php schema is kept');
        t_equal($dir . '/phonebook.php', $files['phonebook'], 'with its path');
        $clean($dir);
    },

    'hidden files, backups, sub folders and *.ec.php are not tables either' => function () use ($probe, $tmp, $clean) {
        $class = $probe();
        $dir = $tmp();
        file_put_contents($dir . '/a_table.php', '<?php return [];');
        foreach (['.DS_Store', '.gitkeep', 'a_table.php.bak', 'a_table.php~', 'readme.txt', 'b_table.ec.php', 'a_table.PHP.old'] as $name) {
            file_put_contents($dir . '/' . $name, 'x');
        }
        mkdir($dir . '/old.php');
        t_equal(['a_table'], array_keys($class::schemaFilesIn($dir)), 'one table');
        rmdir($dir . '/old.php');
        $clean($dir);
    },

    'a missing folder gives no schema, and the install loop goes through the helper' => function () use ($probe, $source) {
        $class = $probe();
        t_equal([], $class::schemaFilesIn('/nonexistent/schema/folder'), 'no schema, no error');
        t_true(strpos($source, 'self::schemaFilesIn($folder . "/resources/db/schema")') !== false, 'fetchModule() uses it');
        t_equal(0, preg_match('/new DirectoryIterator\(\$folder \. "\/resources\/db\/schema"\)/', $source), 'no second, unfiltered reader of the folder');
    },
];
