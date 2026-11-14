<?php
/**
 * Tier 1: Siberian_Migration_Db_Table against a real database.
 *
 * #1122: declaring 'index' (or the bare 'unique' flag) in a schema column only ever reached the database at
 * CREATE TABLE time, and even then only through a path ($this->indexes) that createTable() never populated.
 * Two gaps, same root cause: a fresh install got no declared index at all, and an existing, already-synced
 * column with an index added to its schema later never got it either. These tests pin both fixed behaviours,
 * plus the safety net (a UNIQUE index is skipped, not a failure, when the column already has duplicates) and
 * that a second sync, as always, changes nothing.
 */

/** @return array<string, array> column => SHOW INDEX row, keyed by Key_name */
$indexesOf = static function (Zend_Db_Adapter_Abstract $db, string $table): array {
    $rows = $db->query("SHOW INDEX FROM `{$table}` WHERE `Key_name` != 'PRIMARY'")->fetchAll();
    $byKey = [];
    foreach ($rows as $row) {
        $byKey[$row['Key_name']] = $row;
    }

    return $byKey;
};

return [
    'a fresh table gets its declared index at creation time' => function () use ($indexesOf) {
        $db = t_db_fresh();
        $schema = [
            'widget_id' => ['type' => 'int(11) unsigned', 'auto_increment' => true, 'primary' => true],
            'sku' => [
                'type' => 'varchar(50)',
                'index' => ['key_name' => 'KEY_SKU', 'index_type' => 'BTREE', 'is_null' => false, 'is_unique' => false],
            ],
        ];
        t_db_sync('widget', t_schema_file('widget', $schema));

        $indexes = $indexesOf($db, 'widget');
        t_true(isset($indexes['KEY_SKU']), 'the declared index exists: ' . implode(',', array_keys($indexes)));
        t_equal('sku', $indexes['KEY_SKU']['Column_name'], 'on the right column');
        t_true((bool) $indexes['KEY_SKU']['Non_unique'], 'not unique, as declared');
    },

    'a fresh table gets a UNIQUE index when the schema declares one' => function () use ($indexesOf) {
        $db = t_db_fresh();
        $schema = [
            'widget_id' => ['type' => 'int(11) unsigned', 'auto_increment' => true, 'primary' => true],
            'code' => [
                'type' => 'varchar(50)',
                'index' => ['key_name' => 'UNIQUE_CODE', 'index_type' => 'BTREE', 'is_null' => false, 'is_unique' => true],
            ],
        ];
        t_db_sync('widget', t_schema_file('widget', $schema));

        $indexes = $indexesOf($db, 'widget');
        t_true(isset($indexes['UNIQUE_CODE']), 'the unique index exists');
        t_true(!$indexes['UNIQUE_CODE']['Non_unique'], 'enforced as unique');

        $db->insert('widget', ['code' => 'a']);
        $failed = false;
        try {
            $db->insert('widget', ['code' => 'a']);
        } catch (Throwable $e) {
            $failed = true;
        }
        t_true($failed, 'a duplicate is rejected');
    },

    'a fresh table gets a UNIQUE KEY for a bare unique flag with no index sub-array' => function () use ($indexesOf) {
        $db = t_db_fresh();
        $schema = [
            'widget_id' => ['type' => 'int(11) unsigned', 'auto_increment' => true, 'primary' => true],
            'token' => ['type' => 'varchar(50)', 'unique' => true],
        ];
        t_db_sync('widget', t_schema_file('widget', $schema));

        $indexes = $indexesOf($db, 'widget');
        t_true(isset($indexes['UNIQUE_TOKEN']), 'the bare unique flag became a real key: ' . implode(',', array_keys($indexes)));
        t_true(!$indexes['UNIQUE_TOKEN']['Non_unique'], 'enforced as unique');
    },

    'an existing column whose schema gains an index gets it retrofitted, without touching its rows' => function () use ($indexesOf) {
        $db = t_db_fresh();
        $withoutIndex = [
            'widget_id' => ['type' => 'int(11) unsigned', 'auto_increment' => true, 'primary' => true],
            'sku' => ['type' => 'varchar(50)'],
        ];
        t_db_sync('widget', t_schema_file('widget', $withoutIndex));
        $db->insert('widget', ['sku' => 'kept-1']);
        $db->insert('widget', ['sku' => 'kept-2']);

        // The module ships a release that declares the index on the same, already-existing column (#1122)
        $withIndex = $withoutIndex;
        $withIndex['sku']['index'] = ['key_name' => 'KEY_SKU', 'index_type' => 'BTREE', 'is_null' => false, 'is_unique' => false];
        t_db_sync('widget', t_schema_file('widget', $withIndex));

        $indexes = $indexesOf($db, 'widget');
        t_true(isset($indexes['KEY_SKU']), 'the retrofitted index exists');
        t_equal(['kept-1', 'kept-2'], $db->fetchCol('SELECT sku FROM widget ORDER BY widget_id'), 'rows untouched');
    },

    'retrofitting a UNIQUE index is skipped, not failed, when the column already has duplicates' => function () use ($indexesOf) {
        $db = t_db_fresh();
        $withoutIndex = [
            'widget_id' => ['type' => 'int(11) unsigned', 'auto_increment' => true, 'primary' => true],
            'email' => ['type' => 'varchar(50)'],
        ];
        t_db_sync('widget', t_schema_file('widget', $withoutIndex));
        $db->insert('widget', ['email' => 'same@x.test']);
        $db->insert('widget', ['email' => 'same@x.test']);

        $withUniqueIndex = $withoutIndex;
        $withUniqueIndex['email']['index'] =
            ['key_name' => 'UNIQUE_EMAIL', 'index_type' => 'BTREE', 'is_null' => false, 'is_unique' => true];

        $thrown = null;
        try {
            t_db_sync('widget', t_schema_file('widget', $withUniqueIndex));
        } catch (Throwable $e) {
            $thrown = $e;
        }

        t_true($thrown === null, 'the sync does not fail: ' . ($thrown ? $thrown->getMessage() : ''));
        $indexes = $indexesOf($db, 'widget');
        t_true(!isset($indexes['UNIQUE_EMAIL']), 'the unique index was not added while data conflicts with it');
        t_equal(2, (int) $db->fetchOne('SELECT COUNT(*) FROM widget'), 'both rows kept');
        t_true(
            (bool) preg_grep('/Skipped UNIQUE INDEX .UNIQUE_EMAIL./', t_db_logger()->lines),
            'the skip is logged'
        );
    },

    'running the sync again after a retrofit changes nothing' => function () use ($indexesOf) {
        $db = t_db_fresh();
        $withoutIndex = [
            'widget_id' => ['type' => 'int(11) unsigned', 'auto_increment' => true, 'primary' => true],
            'sku' => ['type' => 'varchar(50)'],
        ];
        t_db_sync('widget', t_schema_file('widget', $withoutIndex));

        $withIndex = $withoutIndex;
        $withIndex['sku']['index'] = ['key_name' => 'KEY_SKU', 'index_type' => 'BTREE', 'is_null' => false, 'is_unique' => false];
        t_db_sync('widget', t_schema_file('widget', $withIndex));
        $statementsAfterRetrofit = count(t_db_logger()->lines);

        t_db_sync('widget', t_schema_file('widget', $withIndex));
        t_db_sync('widget', t_schema_file('widget', $withIndex));

        t_equal($statementsAfterRetrofit, count(t_db_logger()->lines), 'no statement ran on the second and third sync');
        t_true(isset($indexesOf($db, 'widget')['KEY_SKU']), 'the index is still there');
    },
];
