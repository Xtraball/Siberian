<?php
/**
 * The pre-built design caches (app/<edition>/design/design-cache.json) list every design file of an edition. A file
 * that is not in the list is never sent to the browser: a directive added after the last rebuild made the whole
 * Settings > Communications page render as a flat form. This fails when a design file is missing from its cache.
 *
 *   php tests/php-upgrade/design-cache-check.php [root]     root defaults to ./siberian; an edition is checked only
 *                                                           when app/<edition>/design/design-cache.json exists
 * Fix: php cli cache:design <sae|mae|pe> on an assembled tree, then commit the caches.
 */
$root = rtrim($argv[1] ?? 'siberian', '/');
$missing = [];
$checked = 0;

foreach (['sae', 'mae', 'pe'] as $edition) {
    $cacheFile = "$root/app/$edition/design/design-cache.json";
    if (!is_file($cacheFile)) {
        continue;
    }
    $cache = json_decode(file_get_contents($cacheFile), true);
    if (!is_array($cache)) {
        fwrite(STDERR, "design-cache-check: $cacheFile is not valid JSON\n");
        exit(1);
    }
    $listed = [];
    array_walk_recursive($cache, function ($path) use (&$listed) {
        $listed[$path] = true;
    });

    $folders = ["app/$edition/design"];
    foreach (glob("$root/app/$edition/modules/*/resources/design", GLOB_ONLYDIR) ?: [] as $dir) {
        $folders[] = substr($dir, strlen($root) + 1);
    }
    foreach ($folders as $folder) {
        if (!is_dir("$root/$folder")) {
            continue;
        }
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("$root/$folder", FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            // Scripts and stylesheets only: they are what a stale list silently drops from a page. Layouts and
            // templates of a module that another one overrides are left out of the list on purpose.
            if (!$file->isFile() || !in_array($file->getExtension(), ['js', 'css'], true)) {
                continue;
            }
            $relative = substr($file->getPathname(), strlen($root) + 1);
            $checked++;
            if (!isset($listed[$relative])) {
                $missing[$edition][] = $relative;
            }
        }
    }
}

foreach ($missing as $edition => $files) {
    foreach ($files as $file) {
        echo "MISSING in $edition cache: $file\n";
    }
}
echo 'design-cache-check: ' . $checked . ' design files, ' . array_sum(array_map('count', $missing)) . " not in their cache\n";
exit($missing ? 1 : 0);
