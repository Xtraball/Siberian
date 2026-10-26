<?php
/**
 * Backoffice default theme: a progress bar inside a table cell is aligned with its row. Bootstrap's .progress has a
 * 20px bottom margin and the theme centers cells vertically, so every bar was drawn 10px above its label (module
 * installer, certificate uploads in Advanced > Configuration). Every progress bar of the backoffice sits in a table
 * cell, and the theme resets the margin there.
 */
$root = dirname(__DIR__, 3) . '/siberian/app/sae';
$theme = $root . '/design/desktop/backoffice/css/theme-default/styles.css';

return [
    'the default theme removes the bottom margin of progress bars in table cells' => function () use ($theme) {
        $css = file_get_contents($theme);
        t_true(preg_match('/\.table\s*>\s*tbody\s*>\s*tr\s*>\s*td\s*>\s*\.progress\s*\{[^}]*margin-bottom:\s*0\s*;/', $css) === 1);
    },

    'table cells of the default theme are still vertically centered' => function () use ($theme) {
        $css = file_get_contents($theme);
        t_true(preg_match('/\.table\s*>\s*tbody\s*>\s*tr\s*>\s*td,[^{]*\{[^}]*vertical-align:\s*middle/s', $css) === 1);
    },

    'every progress bar of the backoffice sits in a table cell, except the integrity check one' => function () use ($root) {
        // The integrity check bar (Tools page) is a block under a text line, its margin is wanted
        $standalone = ['backoffice/advanced/tools.phtml'];
        $dirs = [$root . '/design/desktop/backoffice/template'];
        foreach (glob($root . '/modules/*/resources/design/desktop/backoffice/template') as $dir) {
            $dirs[] = $dir;
        }

        $found = 0;
        foreach ($dirs as $dir) {
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
            foreach ($it as $file) {
                if ($file->getExtension() !== 'phtml') {
                    continue;
                }
                $path = str_replace('\\', '/',$file->getPathname());
                $skip = false;
                foreach ($standalone as $name) {
                    $skip = $skip || substr($path, -strlen($name)) === $name;
                }
                $html = file_get_contents($file->getPathname());
                $total = preg_match_all('/<div class="progress"/', $html);
                if ($skip || $total === 0) {
                    continue;
                }
                $inCell = preg_match_all('/<td[^>]*>\s*<div class="progress"/', $html);
                $found += $total;
                t_equal($total, $inCell, $path . ': a progress bar outside of a table cell needs its own rule');
                t_true(strpos($html, '<table class="table"') !== false || strpos($html, '<table class="table ') !== false, $path . ': the table has the table class');
            }
        }
        t_true($found >= 14, 'progress bars found: ' . $found);
    },
];
