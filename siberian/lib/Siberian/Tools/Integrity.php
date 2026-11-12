<?php

/**
 * Class Siberian_Tools_Integrity
 *
 * Compares the installation with manifest.json: the list of every file of the release with its md5 hash, shipped
 * with each update. The files are checked by slices, so a request stays short whatever the size of the installation
 * (about 40 000 files, 700 MB, most of them in var/apps).
 */
class Siberian_Tools_Integrity
{
    /**
     * @var string
     */
    public static $manifest = "manifest.json";

    /**
     * Files hashed per request
     */
    const SLICE = 1000;
    const MIN_SLICE = 1;
    const MAX_SLICE = 5000;

    /**
     * Every file of the manifest, or only the ones outside of the app sources
     */
    const SCOPE_ALL = 'all';
    const SCOPE_CORE = 'core';

    /**
     * The app sources are rewritten by module installations and app generation, they are reported apart
     */
    const APPS_PREFIX = 'var/apps/';

    /**
     * @param string $file
     * @return bool
     */
    public static function isAppSource(string $file): bool
    {
        return strpos($file, self::APPS_PREFIX) === 0;
    }

    /**
     * The well formed entries of the manifest, as [file, hash] pairs.
     *
     * @param string $basePath
     * @return array
     * @throws Exception when the manifest is missing or is not a list
     */
    public static function loadManifest(string $basePath): array
    {
        $path = $basePath . self::$manifest;
        if (!is_file($path) || !is_readable($path)) {
            throw new \Exception(p__('backoffice', 'The file manifest.json is missing, the integrity cannot be checked.'));
        }

        $manifest = json_decode((string) file_get_contents($path), true);
        if (!is_array($manifest)) {
            throw new \Exception(p__('backoffice', 'The file manifest.json is not valid, the integrity cannot be checked.'));
        }

        $entries = [];
        foreach ($manifest as $info) {
            if (is_array($info) &&
                isset($info['file'], $info['hash']) &&
                is_string($info['file']) &&
                is_string($info['hash'])) {
                $entries[] = ['file' => $info['file'], 'hash' => strtolower($info['hash'])];
            }
        }

        return $entries;
    }

    /**
     * @param array $entries
     * @param string $scope
     * @return array
     */
    public static function selectEntries(array $entries, string $scope): array
    {
        if ($scope !== self::SCOPE_CORE) {
            return $entries;
        }

        return array_values(array_filter($entries, function ($entry) {
            return !self::isAppSource($entry['file']);
        }));
    }

    /**
     * Hashes the given entries.
     *
     * - hash: the file is there and is not the one of the release
     * - missing: the file is not there
     * - unreadable: it cannot be hashed (not a file, no access, or a path that leaves the installation)
     *
     * @param string $basePath
     * @param array $entries
     * @return array
     */
    public static function checkEntries(string $basePath, array $entries): array
    {
        $result = [
            'hash' => [],
            'missing' => [],
            'unreadable' => [],
        ];

        foreach ($entries as $entry) {
            $file = $entry['file'];

            if ($file === '' || $file[0] === '/' || preg_match('#(^|/)\.\.(/|$)#', $file)) {
                $result['unreadable'][] = $file;
                continue;
            }

            $path = $basePath . $file;
            if (!file_exists($path)) {
                $result['missing'][] = $file;
                continue;
            }

            $hash = is_file($path) && is_readable($path) ? @md5_file($path) : false;
            if ($hash === false) {
                $result['unreadable'][] = $file;
            } else if ($hash !== $entry['hash']) {
                $result['hash'][] = $file;
            }
        }

        return $result;
    }

    /**
     * One slice of the check.
     *
     * @param int $offset first entry of the slice, in the selected entries
     * @param int $limit entries in the slice
     * @param string $scope all | core
     * @param string|null $basePath the installation, by default the one that runs
     * @return array hash, missing, unreadable, total (entries in the scope), checked (so far), done and next (the
     *               offset of the following slice, null when done)
     * @throws Exception
     */
    public static function check(int $offset = 0, int $limit = self::SLICE, string $scope = self::SCOPE_ALL, ?string $basePath = null): array
    {
        if ($basePath === null) {
            $basePath = Core_Model_Directory::getBasePathTo("/");
        }

        $offset = max(0, $offset);
        $limit = max(self::MIN_SLICE, min(self::MAX_SLICE, $limit));

        $entries = self::selectEntries(self::loadManifest($basePath), $scope);
        $total = count($entries);

        $result = self::checkEntries($basePath, array_slice($entries, $offset, $limit));

        $checked = min($total, $offset + $limit);
        $done = $checked >= $total;

        $result['total'] = $total;
        $result['checked'] = $checked;
        $result['done'] = $done;
        $result['next'] = $done ? null : $checked;

        return $result;
    }

    /**
     * Check the whole installation in one go (kept for scripts, the backoffice goes by slices).
     *
     * @param string|null $basePath
     * @return array hash, missing, unreadable
     * @throws Exception
     */
    public static function checkIntegrity(?string $basePath = null): array
    {
        $all = [
            'hash' => [],
            'missing' => [],
            'unreadable' => [],
        ];

        $offset = 0;
        do {
            $slice = self::check($offset, self::MAX_SLICE, self::SCOPE_ALL, $basePath);
            foreach (array_keys($all) as $key) {
                $all[$key] = array_merge($all[$key], $slice[$key]);
            }
            $offset = (int) $slice['next'];
        } while (!$slice['done']);

        return $all;
    }
}
