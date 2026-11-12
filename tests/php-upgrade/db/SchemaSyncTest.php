<?php
/**
 * Tier 1: Siberian_Migration_Db_Table against a real database.
 *
 * The installer builds and updates every table of an installation from resources/db/schema/<table>.php. These tests
 * pin the promises the upgrade path relies on: a missing table is created as described, a new column reaches an old
 * table without touching its rows, running the sync again changes nothing, and every schema file we ship builds on a
 * clean database.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';

/** @return array<string, array{0: string, 1: string}> table => [module, file] for every shipped core schema */
$shippedSchemas = static function () use ($siberian): array {
    $schemas = [];
    foreach (glob($siberian . '/app/sae/modules/*/resources/db/schema/*.php') as $file) {
        $schemas[basename($file, '.php')] = [basename(dirname($file, 4)), $file];
    }
    ksort($schemas);

    return $schemas;
};

/** @return array<string, array> column => describeTable row */
$columnsOf = static function (Zend_Db_Adapter_Abstract $db, string $table): array {
    return $db->describeTable($table);
};

$widget = [
    'widget_id' => ['type' => 'int(11) unsigned', 'auto_increment' => true, 'primary' => true],
    'name' => ['type' => 'varchar(50)', 'charset' => 'utf8', 'collation' => 'utf8_unicode_ci'],
    'note' => ['type' => 'text', 'is_null' => true, 'charset' => 'utf8', 'collation' => 'utf8_unicode_ci'],
    'size' => ['type' => 'int(11)', 'default' => '3'],
    'created_at' => ['type' => 'timestamp', 'default' => 'CURRENT_TIMESTAMP'],
];

return [
    'a missing table is created from its schema' => function () use ($widget, $columnsOf) {
        $db = t_db_fresh();
        t_db_sync('widget', t_schema_file('widget', $widget));

        $columns = $columnsOf($db, 'widget');
        t_equal(['widget_id', 'name', 'note', 'size', 'created_at'], array_keys($columns), 'columns in schema order');
        t_true($columns['widget_id']['PRIMARY'], 'primary key');
        t_true($columns['widget_id']['IDENTITY'], 'auto increment');
        t_true($columns['note']['NULLABLE'], 'is_null makes the column nullable');
        t_true(!$columns['name']['NULLABLE'], 'columns are NOT NULL unless is_null');
        t_equal('3', $columns['size']['DEFAULT'], 'default value');

        $db->insert('widget', ['name' => 'a']);
        $row = $db->fetchRow('SELECT * FROM widget');
        t_equal('3', (string) $row['size'], 'the default applies on insert');
        t_true(!empty($row['created_at']), 'CURRENT_TIMESTAMP is not quoted as a string');
    },

    'a new column is added to an existing table and its rows are kept' => function () use ($widget, $columnsOf) {
        $db = t_db_fresh();
        $old = $widget;
        unset($old['note'], $old['size']);
        t_db_sync('widget', t_schema_file('widget', $old));
        $db->insert('widget', ['name' => 'first']);
        $db->insert('widget', ['name' => 'second']);

        // The module ships a new version of its schema (what a release does)
        t_db_sync('widget', t_schema_file('widget', $widget));

        $columns = $columnsOf($db, 'widget');
        t_true(isset($columns['note'], $columns['size']), 'new columns exist');
        t_equal(['first', 'second'], $db->fetchCol('SELECT name FROM widget ORDER BY widget_id'), 'rows kept');
        t_equal(['3', '3'], array_map('strval', $db->fetchCol('SELECT size FROM widget')), 'the default fills old rows');
    },

    'running the sync again changes nothing' => function () use ($widget) {
        $db = t_db_fresh();
        $first = t_db_sync('widget', t_schema_file('widget', $widget));
        $db->insert('widget', ['name' => 'kept']);
        $statementsAfterCreate = count(t_db_logger()->lines);

        t_db_sync('widget', t_schema_file('widget', $widget));
        t_db_sync('widget', t_schema_file('widget', $widget));

        t_equal($statementsAfterCreate, count(t_db_logger()->lines), 'no statement ran on the second and third sync');
        t_equal(1, (int) $db->fetchOne('SELECT COUNT(*) FROM widget'), 'data untouched');
    },

    'a column that is not in the schema anymore is left alone' => function () use ($widget, $columnsOf) {
        $db = t_db_fresh();
        t_db_sync('widget', t_schema_file('widget', $widget));
        $db->query('ALTER TABLE widget ADD COLUMN legacy varchar(10) NULL');
        $db->insert('widget', ['name' => 'x', 'legacy' => 'keep me']);

        t_db_sync('widget', t_schema_file('widget', $widget));

        t_true(isset($columnsOf($db, 'widget')['legacy']), 'the sync never drops a column');
        t_equal('keep me', $db->fetchOne('SELECT legacy FROM widget'), 'and never loses its data');
    },

    'the sync only adds columns: a changed type or default of an existing one is not applied' => function () use ($widget, $columnsOf) {
        // Characterization of a known limit. A schema change that is not a new column (type, default, nullability)
        // does not reach installations that already have the table, so such a change needs a data/upgrade script.
        // If the sync learns to alter columns, this test is the one to rewrite on purpose.
        $db = t_db_fresh();
        t_db_sync('widget', t_schema_file('widget', $widget));

        $changed = $widget;
        $changed['name'] = ['type' => 'varchar(200)', 'charset' => 'utf8', 'collation' => 'utf8_unicode_ci'];
        $changed['size']['default'] = '9';
        t_db_sync('widget', t_schema_file('widget', $changed));

        $columns = $columnsOf($db, 'widget');
        t_equal(50, (int) $columns['name']['LENGTH'], 'varchar(50) stays');
        t_equal('3', $columns['size']['DEFAULT'], 'default stays');
    },

    'foreign keys are added once, after the tables exist' => function () {
        $db = t_db_fresh();
        $parent = ['parent_id' => ['type' => 'int(11) unsigned', 'auto_increment' => true, 'primary' => true]];
        $child = [
            'child_id' => ['type' => 'int(11) unsigned', 'auto_increment' => true, 'primary' => true],
            'parent_id' => [
                'type' => 'int(11) unsigned',
                'foreign_key' => ['table' => 'parent', 'column' => 'parent_id', 'name' => 'child_parent_fk', 'on_update' => 'CASCADE', 'on_delete' => 'CASCADE'],
            ],
        ];
        $tables = [t_db_sync('child', t_schema_file('child', $child)), t_db_sync('parent', t_schema_file('parent', $parent))];

        foreach ($tables as $table) {
            $table->updateForeignKeys();
        }
        $count = static function () use ($db): int {
            return (int) $db->fetchOne(
                "SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_TYPE = 'FOREIGN KEY'");
        };
        t_equal(1, $count(), 'one foreign key');

        $db->insert('parent', ['parent_id' => 1]);
        $db->insert('child', ['parent_id' => 1]);
        $db->delete('parent', 'parent_id = 1');
        t_equal(0, (int) $db->fetchOne('SELECT COUNT(*) FROM child'), 'ON DELETE CASCADE works');

        // A second pass (the next update of the module) adds nothing and does not fail
        $again = [t_db_sync('child', t_schema_file('child', $child)), t_db_sync('parent', t_schema_file('parent', $parent))];
        foreach ($again as $table) {
            $table->updateForeignKeys();
        }
        t_equal(1, $count(), 'still one foreign key');
    },

    'a schema that cannot be created is reported with the table name' => function () {
        t_db_fresh();
        $broken = ['id' => ['type' => 'not_a_type', 'primary' => true]];
        $message = null;
        try {
            t_db_sync('broken_table', t_schema_file('broken_table', $broken));
        } catch (Throwable $e) {
            $message = $e->getMessage();
        }
        t_true($message !== null && strpos($message, 'broken_table') !== false, 'the exception names the table: ' . var_export($message, true));
    },

    'every schema we ship builds on a clean database, module by module' => function () use ($shippedSchemas) {
        $db = t_db_fresh();
        $byModule = [];
        foreach ($shippedSchemas() as $table => [$module, $file]) {
            $byModule[$module][$table] = $file;
        }
        t_true(count($byModule) > 10, 'found the core modules: ' . count($byModule));

        $problems = [];
        foreach ($byModule as $module => $files) {
            // What Installer_Model_Installer_Module::install() does for one module
            $migrations = [];
            foreach ($files as $table => $file) {
                try {
                    $migrations[] = t_db_sync($table, $file);
                } catch (Throwable $e) {
                    $problems[] = "$module/$table: " . $e->getMessage();
                }
            }
            foreach ($migrations as $migration) {
                try {
                    $migration->updateForeignKeys();
                } catch (Throwable $e) {
                    $problems[] = "$module/foreign keys: " . $e->getMessage();
                }
            }
        }
        t_equal([], $problems, 'tables that failed');

        $tables = $db->listTables();
        $missing = array_values(array_diff(array_keys($shippedSchemas()), $tables));
        t_equal([], $missing, 'tables that were not created');
    },

    'a second install of every shipped schema runs no statement' => function () use ($shippedSchemas) {
        t_db_fresh();
        $all = $shippedSchemas();
        foreach ($all as $table => [$module, $file]) {
            t_db_sync($table, $file);
        }
        $after = count(t_db_logger()->lines);

        foreach ($all as $table => [$module, $file]) {
            t_db_sync($table, $file);
        }
        $extra = array_slice(t_db_logger()->lines, $after);
        t_equal([], array_map(static function ($q) {
            return substr(preg_replace('/\s+/', ' ', $q), 0, 160);
        }, $extra), 'statements the second pass ran (a sync must be idempotent)');
    },
];
