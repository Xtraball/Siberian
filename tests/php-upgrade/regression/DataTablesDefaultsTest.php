<?php
/**
 * DataTables 1.10 shows an alert("Cannot reinitialise DataTable") and stops the page when a table is initialised twice.
 * The editor loads module templates (Xdelivery, Restaurants ...) again on every visit of a tab, so the same table id is
 * initialised more than once. A small script right after the library makes the second initialisation destroy the first
 * one instead. Reproduced in jsdom with the real jQuery and DataTables 1.10.2: the alert appears without the script and
 * not with it.
 */
$root = dirname(__DIR__, 3) . '/siberian/app/sae';

return [
    'a script loaded after DataTables makes a second initialisation destroy the first one' => function () use ($root) {
        $script = @file_get_contents($root . '/design/desktop/flat/js/dataTables.defaults.js');
        t_true($script !== false, 'the script exists');
        t_true(strpos($script, '$.fn.dataTable.defaults') !== false, 'it changes the DataTables defaults');
        t_true(preg_match('/destroy:\s*true/', $script) === 1, 'destroy is on');
        t_true(strpos($script, 'if (!$ || !$.fn || !$.fn.dataTable') !== false, 'it does nothing when DataTables is not loaded');
    },

    'every layout that loads DataTables loads the defaults right after it' => function () use ($root) {
        $layouts = [
            $root . '/design/desktop/flat/layout/front.xml',
            $root . '/modules/Customer/resources/design/desktop/flat/layout/customer.xml',
        ];
        foreach ($layouts as $layout) {
            $xml = file_get_contents($layout);
            $library = strpos($xml, 'js/jquery.dataTables.min.js');
            $defaults = strpos($xml, 'js/dataTables.defaults.js');
            t_true($library !== false, basename($layout) . ': loads DataTables');
            t_true($defaults !== false && $defaults > $library, basename($layout) . ': defaults come after the library');
        }

        // Nothing else of the core loads the library without the defaults
        $others = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->isFile() && substr($file->getFilename(), -4) === '.xml' &&
                strpos(file_get_contents($file->getPathname()), 'jquery.dataTables') !== false &&
                !in_array($file->getPathname(), $layouts, true)) {
                $others[] = $file->getPathname();
            }
        }
        t_equal([], $others, 'no other layout loads DataTables');
    },
];
