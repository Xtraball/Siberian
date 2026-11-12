<?php

namespace Siberian\Locale;

/**
 * Older Locale<Language> module packs (app/local/modules/Locale*) against the locales shipped in the core.
 *
 * Pure filesystem helpers, every path is given by the caller: detect the packs, compare their catalogs with the shipped
 * ones, take a backup (a plain copy, no zip extension needed) before a pack is removed, list/restore/delete backups.
 */
class Packs
{
    /**
     * Backup folder name: <date>-<time>-<locale code>-<module name>, also the only accepted shape of an id from a request.
     */
    const ID_PATTERN = '/^\d{8}-\d{6}-[A-Za-z_]+-[A-Za-z0-9]+$/';

    /**
     * Installed packs that carry a catalog for a shipped locale, by locale code.
     *
     * @param string $modulesDir app/local/modules
     * @param string[] $shipped
     * @return array
     */
    public static function detect($modulesDir, array $shipped)
    {
        $packs = [];
        if (!is_dir($modulesDir)) {
            return $packs;
        }

        foreach (scandir($modulesDir) as $name) {
            if ($name === 'Locales' || strpos($name, 'Locale') !== 0 || !preg_match('/^[A-Za-z0-9]+$/', $name)) {
                continue;
            }
            $translations = "{$modulesDir}/{$name}/resources/translations";
            if (!is_dir($translations)) {
                continue;
            }
            foreach (scandir($translations) as $code) {
                if ($code[0] === '.' || !in_array($code, $shipped, true) || !is_dir("{$translations}/{$code}")) {
                    continue;
                }
                $package = json_decode((string) @file_get_contents("{$modulesDir}/{$name}/package.json"), true);
                $packs[$code] = [
                    'code' => $code,
                    'module' => $name,
                    'version' => is_array($package) ? (string) ($package['version'] ?? '') : '',
                    'path' => "{$modulesDir}/{$name}",
                    'enabled' => !is_file("{$modulesDir}/{$name}/module.disabled"),
                ];
            }
        }
        ksort($packs);

        return $packs;
    }

    /**
     * @param string $file
     * @return array "<msgctxt>\x04<msgid>" => msgstr, only translated and not fuzzy singular entries
     */
    public static function readCatalog($file)
    {
        $out = [];
        $ctx = $id = $str = $field = null;
        $skip = false;
        $flush = static function () use (&$out, &$ctx, &$id, &$str, &$skip) {
            if ($id !== null && $id !== '' && $str !== null && $str !== '' && !$skip) {
                $out[(string) $ctx . "\x04" . $id] = $str;
            }
            $ctx = $id = $str = null;
            $skip = false;
        };

        $lines = @file($file, FILE_IGNORE_NEW_LINES);
        foreach ($lines === false ? [] : $lines as $line) {
            $line = rtrim($line, "\r");
            if ($line === '') {
                $flush();
                $field = null;
                continue;
            }
            if (strncmp($line, '#~', 2) === 0) {
                $skip = true;
                continue;
            }
            if (strncmp($line, '#,', 2) === 0) {
                $skip = $skip || strpos($line, 'fuzzy') !== false;
                continue;
            }
            if ($line[0] === '#') {
                continue;
            }
            if (preg_match('/^(msgctxt|msgid_plural|msgid|msgstr(?:\[\d+\])?)\s+"(.*)"\s*$/', $line, $m)) {
                $value = stripcslashes($m[2]);
                $field = $m[1];
                if ($field === 'msgctxt') {
                    $ctx = $value;
                } elseif ($field === 'msgid') {
                    $id = $value;
                } elseif ($field === 'msgstr') {
                    $str = $value;
                } else {
                    $skip = true; // plural forms are not compared
                }
                continue;
            }
            if ($line[0] === '"' && $field !== null && preg_match('/^"(.*)"\s*$/', $line, $m)) {
                $value = stripcslashes($m[1]);
                if ($field === 'msgctxt') {
                    $ctx .= $value;
                } elseif ($field === 'msgid') {
                    $id .= $value;
                } elseif ($field === 'msgstr') {
                    $str .= $value;
                }
            }
        }
        $flush();

        return $out;
    }

    /**
     * What a pack has that the shipped catalog has not, file by file.
     *
     * @param string $packDir the pack's resources/translations/<code>
     * @param string $shippedDir the Locales module's resources/translations/<code>
     * @return array pack, shipped (translated texts), only_in_pack, different
     */
    public static function compare($packDir, $shippedDir)
    {
        $result = ['pack' => 0, 'shipped' => 0, 'only_in_pack' => 0, 'different' => 0];
        $files = array_merge(glob("{$packDir}/*.po") ?: [], glob("{$shippedDir}/*.po") ?: []);
        $names = array_unique(array_map('basename', $files));

        foreach ($names as $name) {
            $pack = self::readCatalog("{$packDir}/{$name}");
            $shipped = self::readCatalog("{$shippedDir}/{$name}");
            $result['pack'] += count($pack);
            $result['shipped'] += count($shipped);
            foreach ($pack as $key => $text) {
                if (!isset($shipped[$key])) {
                    $result['only_in_pack']++;
                } elseif ($shipped[$key] !== $text) {
                    $result['different']++;
                }
            }
        }

        return $result;
    }

    /**
     * A pack is stale when it is switched on and the shipped catalog already translates every text the pack translates:
     * it adds nothing, and it can make the older wording win over the maintained one.
     *
     * @param array $comparison result of compare()
     * @param bool $enabled
     * @return bool
     */
    public static function isStale(array $comparison, $enabled)
    {
        return (bool) $enabled && $comparison['shipped'] > 0 && $comparison['only_in_pack'] === 0;
    }

    /**
     * A languages/<code> folder holding nothing but the empty default.csv written by a pack install.
     *
     * @param string $languageDir
     * @return bool
     */
    public static function isPristine($languageDir)
    {
        if (!is_dir($languageDir)) {
            return false;
        }
        $entries = array_values(array_diff(scandir($languageDir), ['.', '..']));
        if ($entries !== ['default.csv']) {
            return false;
        }

        return in_array(trim((string) file_get_contents("{$languageDir}/default.csv")), ['', '"";""'], true);
    }

    /**
     * Copy the pack (and its languages/<code> folder) to <backupDir>/<id>/ with a manifest.json.
     *
     * @param string $root installation root
     * @param array $pack one entry of detect()
     * @param string $backupDir
     * @param string $action what is about to happen, shown in the list
     * @param int $time
     * @param array $extra stored as is in the manifest (the module row)
     * @return array the manifest
     * @throws \Exception
     */
    public static function backup($root, array $pack, $backupDir, $action, $time, array $extra = [])
    {
        $id = date('Ymd-His', $time) . '-' . $pack['code'] . '-' . $pack['module'];
        if (!preg_match(self::ID_PATTERN, $id)) {
            throw new \Exception('Invalid backup id');
        }

        $target = "{$backupDir}/{$id}";
        if (file_exists($target)) {
            throw new \Exception('A backup with this id already exists');
        }
        self::copyDir($pack['path'], "{$target}/module");
        $language = "{$root}/languages/{$pack['code']}";
        $hasLanguage = is_dir($language);
        if ($hasLanguage) {
            self::copyDir($language, "{$target}/language");
        }

        $manifest = [
            'id' => $id,
            'code' => $pack['code'],
            'module' => $pack['module'],
            'version' => $pack['version'],
            'action' => $action,
            'created' => $time,
            'had_language' => $hasLanguage,
            'was_enabled' => $pack['enabled'],
            'extra' => $extra,
        ];
        file_put_contents("{$target}/manifest.json", json_encode($manifest, JSON_PRETTY_PRINT));
        if (!is_file("{$backupDir}/.htaccess")) {
            file_put_contents("{$backupDir}/.htaccess", "Require all denied\n");
        }

        return $manifest;
    }

    /**
     * @param string $backupDir
     * @return array manifests, newest first, with their size in bytes
     */
    public static function listBackups($backupDir)
    {
        $backups = [];
        foreach (glob("{$backupDir}/*/manifest.json") ?: [] as $file) {
            $manifest = json_decode((string) file_get_contents($file), true);
            if (!is_array($manifest) || !isset($manifest['id']) || !preg_match(self::ID_PATTERN, $manifest['id'])) {
                continue;
            }
            $manifest['size'] = self::dirSize(dirname($file));
            $backups[] = $manifest;
        }
        usort($backups, static function ($a, $b) {
            return ($b['created'] ?? 0) <=> ($a['created'] ?? 0);
        });

        return $backups;
    }

    /**
     * Put a backup back in place. The pack folder is replaced, the languages folder is only restored when it is gone.
     *
     * @param string $root
     * @param string $backupDir
     * @param string $id
     * @return array the manifest
     * @throws \Exception
     */
    public static function restore($root, $backupDir, $id)
    {
        $manifest = self::manifest($backupDir, $id);
        $module = "{$root}/app/local/modules/{$manifest['module']}";
        if (file_exists($module)) {
            // the pack was installed again, or changed, since the backup: never replace what is there
            throw new \Exception('The module is installed again, remove or rename it before restoring this backup');
        }
        self::copyDir("{$backupDir}/{$id}/module", $module);
        if (!$manifest['was_enabled']) {
            file_put_contents("{$module}/module.disabled", '1');
        } elseif (is_file("{$module}/module.disabled")) {
            unlink("{$module}/module.disabled");
        }

        $language = "{$root}/languages/{$manifest['code']}";
        if (!empty($manifest['had_language']) && !is_dir($language) && is_dir("{$backupDir}/{$id}/language")) {
            self::copyDir("{$backupDir}/{$id}/language", $language);
        }

        return $manifest;
    }

    /**
     * @param string $backupDir
     * @param string $id
     * @throws \Exception
     */
    public static function deleteBackup($backupDir, $id)
    {
        self::manifest($backupDir, $id);
        self::removeDir("{$backupDir}/{$id}");
    }

    /**
     * Remove the pack folder, and the languages/<code> folder when nothing but the pack's empty default.csv is in it.
     *
     * @param string $root
     * @param array $pack
     * @return bool true when the languages folder was removed too
     */
    public static function remove($root, array $pack)
    {
        self::removeDir($pack['path']);
        $language = "{$root}/languages/{$pack['code']}";
        if (self::isPristine($language)) {
            self::removeDir($language);

            return true;
        }

        return false;
    }

    /**
     * @param string $backupDir
     * @param string $id
     * @return array
     * @throws \Exception
     */
    protected static function manifest($backupDir, $id)
    {
        if (!preg_match(self::ID_PATTERN, (string) $id) || !is_file("{$backupDir}/{$id}/manifest.json")) {
            throw new \Exception('Unknown backup');
        }
        $manifest = json_decode((string) file_get_contents("{$backupDir}/{$id}/manifest.json"), true);
        if (!is_array($manifest) || ($manifest['id'] ?? '') !== $id || !preg_match('/^[A-Za-z0-9]+$/', $manifest['module'] ?? '')
            || !preg_match('/^[A-Za-z_]+$/', $manifest['code'] ?? '')) {
            throw new \Exception('Invalid backup manifest');
        }

        return $manifest;
    }

    /**
     * @param string $from
     * @param string $to
     * @throws \Exception
     */
    public static function copyDir($from, $to)
    {
        if (!is_dir($to) && !mkdir($to, 0775, true) && !is_dir($to)) {
            throw new \Exception("Unable to create {$to}");
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($from, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $item) {
            $destination = $to . '/' . $iterator->getSubPathName();
            if ($item->isDir()) {
                if (!is_dir($destination) && !mkdir($destination, 0775, true) && !is_dir($destination)) {
                    throw new \Exception("Unable to create {$destination}");
                }
            } elseif (!copy($item->getPathname(), $destination)) {
                throw new \Exception("Unable to copy {$item->getPathname()}");
            }
        }
    }

    /**
     * @param string $dir
     */
    public static function removeDir($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $item) {
            $item->isDir() && !$item->isLink() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($dir);
    }

    /**
     * @param string $dir
     * @return int
     */
    public static function dirSize($dir)
    {
        $size = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)) as $file) {
            $size += $file->getSize();
        }

        return $size;
    }
}
