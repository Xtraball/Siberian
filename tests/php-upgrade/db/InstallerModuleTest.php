<?php
/**
 * Tier 1: Installer_Model_Installer_Module against a real database and a module folder on disk.
 *
 * This is the gate every update goes through: a module's schema and data files play only when its package.json
 * version is newer than the one recorded in the database (AGENTS.md rule 9). The tests build small module folders in
 * a sandbox and run prepare / canUpdate / install / insertData the way Installer_Model_Installer_Update does.
 */

$items = [
    'item_id' => ['type' => 'int(11) unsigned', 'auto_increment' => true, 'primary' => true],
    'label' => ['type' => 'varchar(50)', 'charset' => 'utf8', 'collation' => 'utf8_unicode_ci'],
];
$itemsPlus = $items + ['extra' => ['type' => 'int(11)', 'default' => '0']];

/** Data file code that records its own name in the items table (the table must exist: schema runs first). */
$marker = static function (string $label): string {
    return '$this->query("INSERT INTO fx_items (label) VALUES (' . "'" . $label . "'" . ')");';
};

$columnNames = static function (Zend_Db_Adapter_Abstract $db, string $table): array {
    return array_keys($db->describeTable($table));
};

return [
    'a new module: not installed, version 0.0.1, an update is due' => function () use ($items) {
        t_installer_fresh();
        t_module_write('Fx', '1.0.0', ['fx_items' => $items]);

        $module = new Installer_Model_Installer_Module();
        $module->prepare('Fx');

        t_true(!$module->isInstalled(), 'not installed');
        t_equal('0.0.1', $module->getVersion(), 'starts at the default version');
        t_equal('1.0.0', $module->getLastVersion(), 'last version is the one of package.json');
        t_true($module->canUpdate(), 'an update is due');
    },

    'install + insertData create the tables, run the data files and record the version' => function () use ($items, $marker) {
        $db = t_installer_fresh();
        t_module_write('Fx', '1.2.0', ['fx_items' => $items], ['first.php' => $marker('one')]);

        t_module_update('Fx');

        t_equal(['one'], $db->fetchCol('SELECT label FROM fx_items'), 'table created and data file ran');
        t_equal('1.2.0', $db->fetchOne("SELECT version FROM module WHERE name = 'Fx'"), 'version recorded');

        $again = new Installer_Model_Installer_Module();
        $again->prepare('Fx');
        t_true($again->isInstalled(), 'now installed');
        t_true(!$again->canUpdate(), 'nothing to update at the same version');
    },

    'rule 9: a schema change without a version bump does not reach an installed module' => function () use ($items, $itemsPlus, $columnNames) {
        $db = t_installer_fresh();
        t_module_write('Fx', '1.0.0', ['fx_items' => $items]);
        t_module_update('Fx');

        // The release ships a new column but forgets to raise the version
        t_module_write('Fx', '1.0.0', ['fx_items' => $itemsPlus]);
        t_module_update('Fx');

        t_true(!in_array('extra', $columnNames($db, 'fx_items'), true), 'the column is silently not added: this is why check-pr.sh demands the bump');
    },

    'a version bump plays the new schema and keeps the rows' => function () use ($items, $itemsPlus, $columnNames, $marker) {
        $db = t_installer_fresh();
        t_module_write('Fx', '1.0.0', ['fx_items' => $items], ['first.php' => $marker('kept')]);
        t_module_update('Fx');

        t_module_write('Fx', '1.0.1', ['fx_items' => $itemsPlus], []);
        $module = t_module_update('Fx');

        t_true(in_array('extra', $columnNames($db, 'fx_items'), true), 'new column added');
        t_equal(['kept'], $db->fetchCol('SELECT label FROM fx_items'), 'rows kept');
        t_equal('1.0.1', $db->fetchOne("SELECT version FROM module WHERE name = 'Fx'"), 'version moved');
        t_equal(1, (int) $db->fetchOne("SELECT COUNT(*) FROM module WHERE name = 'Fx'"), 'one module row, not a duplicate');
    },

    'reinstall (opt-in) plays the files again at the same version, and twice is harmless' => function () use ($items, $itemsPlus, $columnNames) {
        $db = t_installer_fresh();
        t_module_write('Fx', '1.0.0', ['fx_items' => $items]);
        t_module_update('Fx');
        t_module_write('Fx', '1.0.0', ['fx_items' => $itemsPlus]);

        t_module_update('Fx', true);
        t_module_update('Fx', true);

        t_true(in_array('extra', $columnNames($db, 'fx_items'), true), 'column added by the forced reinstall');
        t_equal('1.0.0', $db->fetchOne("SELECT version FROM module WHERE name = 'Fx'"), 'version unchanged');
        t_equal(1, (int) $db->fetchOne("SELECT COUNT(*) FROM module WHERE name = 'Fx'"), 'still one module row');
    },

    'a package older than the database never downgrades' => function () use ($items) {
        $db = t_installer_fresh();
        t_module_write('Fx', '2.0.0', ['fx_items' => $items]);
        t_module_update('Fx');

        t_module_write('Fx', '1.5.0', ['fx_items' => $items]);
        $module = t_module_update('Fx');

        t_true(!$module->canUpdate(), 'no update when the package is older');
        t_equal('2.0.0', $db->fetchOne("SELECT version FROM module WHERE name = 'Fx'"), 'recorded version untouched');
    },

    'version compare is numeric, not alphabetical (1.10.0 is newer than 1.9.0)' => function () use ($items) {
        $db = t_installer_fresh();
        t_module_write('Fx', '1.9.0', ['fx_items' => $items]);
        t_module_update('Fx');

        t_module_write('Fx', '1.10.0', ['fx_items' => $items]);
        $module = new Installer_Model_Installer_Module();
        $module->prepare('Fx');

        t_true($module->canUpdate(), '1.10.0 > 1.9.0');
    },

    'data files: numeric-named and encrypted (.ec.php) files are skipped, .install.php runs on a first install only' => function () use ($items, $marker) {
        $db = t_installer_fresh();
        t_module_write('Fx', '1.0.0', ['fx_items' => $items], [
            'plain.php' => $marker('plain'),
            'feature1.php' => $marker('numeric'),
            'secret.ec.php' => $marker('encrypted'),
            'legacy.install.php' => $marker('install-once'),
        ]);
        t_module_update('Fx');
        $labels = $db->fetchCol('SELECT label FROM fx_items ORDER BY label');
        t_equal(['install-once', 'plain'], $labels, 'first install: plain and .install.php only');

        // Same files played by a later update: .install.php must not run again.
        // (The installer plays files with require_once, so a copy under a new name stands for "the next release".)
        $db->query('DELETE FROM fx_items');
        t_module_write('Fx', '1.0.1', ['fx_items' => $items], [
            'plain_b.php' => $marker('plain'),
            'legacy_b.install.php' => $marker('install-once'),
        ]);
        t_module_update('Fx');
        t_equal(['plain'], $db->fetchCol('SELECT label FROM fx_items'), 'update: .install.php does not run');
    },

    'a failing data file does not stop the install of the rest, and the version is still recorded' => function () use ($items, $marker) {
        // Characterization: _run() swallows the failure (it is logged). If this is made strict, rewrite this test on purpose.
        $db = t_installer_fresh();
        Zend_Registry::set('logger', new class extends T_Db_Logger {
            public function sendException($message) { $this->lines[] = 'EXCEPTION'; }
        });
        t_module_write('Fx', '1.0.0', ['fx_items' => $items], [
            'a_broken.php' => 'throw new Exception("boom");',
            'b_ok.php' => $marker('after'),
        ]);

        t_module_update('Fx');

        t_equal(['after'], $db->fetchCol('SELECT label FROM fx_items'), 'the next data file still ran');
        t_equal('1.0.0', $db->fetchOne("SELECT version FROM module WHERE name = 'Fx'"), 'version recorded');
        t_true(in_array('EXCEPTION', t_db_logger()->lines, true), 'the failure was reported to the logger');
    },

    'a module that needs another one installs it first' => function () use ($items) {
        $db = t_installer_fresh();
        t_module_write('FxBase', '1.0.0', ['fx_base' => $items]);
        t_module_write('FxApp', '1.0.0', ['fx_app' => $items], [], ['dependencies' => ['modules' => ['FxBase' => '1.0.0']]]);

        t_module_update('FxApp');

        t_true(in_array('fx_base', $db->listTables(), true), 'the dependency tables were created');
        t_true(in_array('fx_app', $db->listTables(), true), 'the module tables were created');
    },

    'a dependency that is too old aborts the install with a clear message' => function () use ($items) {
        $db = t_installer_fresh();
        t_module_write('FxBase', '1.0.0', ['fx_base' => $items]);
        t_module_update('FxBase');
        t_module_write('FxApp', '1.0.0', ['fx_app' => $items], [], ['dependencies' => ['modules' => ['FxBase' => '2.0.0']]]);

        $message = null;
        try {
            t_module_update('FxApp');
        } catch (Throwable $e) {
            $message = $e->getMessage();
        }

        t_true($message !== null && strpos($message, 'FxBase') !== false && strpos($message, '2.0.0') !== false, 'message names module and version: ' . var_export($message, true));
        t_equal(0, (int) $db->fetchOne("SELECT COUNT(*) FROM module WHERE name = 'FxApp' AND version = '1.0.0'"), 'the module is not recorded as installed at its version');
    },

    'package.json code marks the module as licensed; types icons/layout/template are stored' => function () use ($items) {
        $db = t_installer_fresh();
        t_module_write('Fx', '1.0.0', ['fx_items' => $items], [], ['code' => 'fx-license', 'type' => 'layout']);

        t_module_update('Fx');

        $row = $db->fetchRow("SELECT code, use_license, type FROM module WHERE name = 'Fx'");
        t_equal('fx-license', $row['code'], 'code');
        t_equal(1, (int) $row['use_license'], 'use_license');
        t_equal('layout', $row['type'], 'type');
    },

    'a module without package.json is not installable: no version to compare' => function () {
        t_installer_fresh();
        $module = new Installer_Model_Installer_Module();
        $module->prepare('DoesNotExist');

        // version_compare(null, ...) is a deprecation on PHP 8.1+ and a TypeError on 9: it must not be raised
        $raised = [];
        set_error_handler(static function ($no, $msg) use (&$raised) {
            $raised[] = $msg;
            return true;
        });
        try {
            $canUpdate = $module->canUpdate();
        } finally {
            restore_error_handler();
        }

        t_true(!$canUpdate, 'no package, no update (and no fatal)');
        t_equal([], $raised, 'no deprecation or warning');
    },

    'schemaFilesIn only takes <table>.php files' => function () {
        $dir = sys_get_temp_dir() . '/sibtest-schemafiles-' . getmypid();
        mkdir($dir . '/sub', 0777, true);
        foreach (['users.php', 'secret.ec.php', 'install.sql', '.DS_Store', '.gitkeep', 'notes.php.bak'] as $file) {
            file_put_contents($dir . '/' . $file, 'x');
        }
        try {
            $found = Installer_Model_Installer_Module::schemaFilesIn($dir);
        } finally {
            exec('rm -rf ' . escapeshellarg($dir));
        }

        t_equal(['users'], array_keys($found), 'only users');
    },
];
