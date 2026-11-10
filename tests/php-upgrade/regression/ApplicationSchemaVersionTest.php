<?php
/**
 * The installer syncs a module's schema only when the version in its package.json is newer than the one recorded in
 * the `module` table (Installer_Model_Installer_Module::canUpdate()). 5.1.14 added application.android_edge_to_edge
 * (the edge-to-edge option) without raising Application above 5.0.15, so installations updated from 5.1.13 never got
 * the column: the option showed "Options saved" but its value was dropped, because the row only keeps known columns.
 * Fresh installs were not affected.
 */
$root = dirname(__DIR__, 3);
$module = "$root/siberian/app/sae/modules/Application";

// Columns added to the application table after Application 5.0.15 was released (5.1.13).
$addedAfter5015 = ['android_edge_to_edge'];

$columns = function () use ($module) {
    $schemas = [];
    include "$module/resources/db/schema/application.php";
    return array_keys($schemas['application'] ?? []);
};

$version = function () use ($module) {
    $package = json_decode(file_get_contents("$module/package.json"), true);
    return (string) ($package['version'] ?? '');
};

return [
    'the columns added after 5.0.15 are in the schema' => function () use ($columns, $addedAfter5015) {
        $cols = $columns();
        foreach ($addedAfter5015 as $column) {
            t_true(in_array($column, $cols, true), "application.$column is declared");
        }
    },

    'Application is above 5.0.15, so updated installations sync the new columns' => function () use ($version) {
        $v = $version();
        t_true(version_compare($v, '5.0.15', '>'), "Application package.json version $v > 5.0.15");
    },
];
