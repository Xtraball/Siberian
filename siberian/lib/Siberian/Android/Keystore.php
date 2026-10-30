<?php

namespace Siberian\Android;

/**
 * Helpers around the Android signing keystore of an application (var/apps/android/keystore/<appId>.pks).
 *
 * With Google Play App Signing (#855) the keystore of the application is only the upload key: Google keeps the app
 * signing key. These helpers never touch the database, so they can be tested alone.
 */
class Keystore
{
    /**
     * A keystore and its passwords are a few kilobytes, anything bigger is not one.
     */
    public const MAX_SIZE = 1048576;

    /**
     * @param string $path path of the current keystore
     * @param int|null $now
     * @return string|null the path of the backup, null when there was nothing to keep
     */
    public static function backup(string $path, ?int $now = null): ?string
    {
        if (!is_file($path)) {
            return null;
        }

        $stamp = date('Y-m-d_H-i-s', $now ?? time());
        $base = preg_replace('/\.pks$/', '-backup-' . $stamp . '.pks', $path);
        $backup = $base;
        for ($i = 1; file_exists($backup); $i++) {
            $backup = preg_replace('/\.pks$/', '-' . $i . '.pks', $base);
        }

        if (!rename($path, $backup)) {
            throw new \RuntimeException('Cannot keep a backup of the current keystore.');
        }

        return $backup;
    }

    /**
     * @param string $binary
     * @return string|null jks, jceks, pkcs12, or null when this is not a keystore
     */
    public static function kind(string $binary): ?string
    {
        $head = substr($binary, 0, 4);
        if ($head === "\xFE\xED\xFE\xED") {
            return 'jks';
        }
        if ($head === "\xCE\xCE\xCE\xCE") {
            return 'jceks';
        }
        // PKCS#12 is a DER sequence
        if (strlen($binary) > 64 && $head !== '' && $head[0] === "\x30") {
            return 'pkcs12';
        }

        return null;
    }

    /**
     * The format of passwords.txt of the example archive: one "name:value" per line.
     *
     * @param string $text
     * @return array{alias: string, store_pass: string, key_pass: string}
     */
    public static function parsePasswords(string $text): array
    {
        $values = [];
        foreach (preg_split('/\r\n|\r|\n/', $text) as $line) {
            $position = strpos($line, ':');
            if ($position === false) {
                continue;
            }
            $values[strtolower(trim(substr($line, 0, $position)))] = trim(substr($line, $position + 1));
        }

        foreach (['alias', 'store_pass', 'key_pass'] as $name) {
            if (empty($values[$name])) {
                throw new \InvalidArgumentException("Missing {$name} in passwords.txt.");
            }
        }
        // Java refuses keystore passwords shorter than 6 characters, the columns hold 255 and 50
        if (strlen($values['store_pass']) < 6 || strlen($values['key_pass']) < 6) {
            throw new \InvalidArgumentException('The passwords must have at least 6 characters.');
        }
        if (strlen($values['store_pass']) > 255 || strlen($values['key_pass']) > 255 || strlen($values['alias']) > 50) {
            throw new \InvalidArgumentException('A password or the alias is too long.');
        }

        return [
            'alias' => $values['alias'],
            'store_pass' => $values['store_pass'],
            'key_pass' => $values['key_pass'],
        ];
    }

    /**
     * Reads an archive holding one keystore (*.pks) and passwords.txt, nothing is extracted to disk.
     *
     * @param string $zipPath
     * @return array{pks: string, alias: string, store_pass: string, key_pass: string}
     */
    public static function readArchive(string $zipPath): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \InvalidArgumentException('This is not a zip archive.');
        }

        try {
            $pks = null;
            $passwords = null;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $name = basename($stat['name']);
                // Directories, oversized entries and the resource forks macOS adds (__MACOSX/._name) are ignored
                if ($stat['size'] > self::MAX_SIZE || substr($stat['name'], -1) === '/' ||
                    strpos($stat['name'], '__MACOSX/') === 0 || $name[0] === '.') {
                    continue;
                }
                if (strtolower($name) === 'passwords.txt') {
                    $passwords = $zip->getFromIndex($i);
                } elseif (strtolower(pathinfo($name, PATHINFO_EXTENSION)) === 'pks') {
                    if ($pks !== null) {
                        throw new \InvalidArgumentException('The archive must hold only one keystore.');
                    }
                    $pks = $zip->getFromIndex($i);
                }
            }
        } finally {
            $zip->close();
        }

        if ($pks === null || $pks === false) {
            throw new \InvalidArgumentException('No keystore (.pks) in the archive.');
        }
        if ($passwords === null || $passwords === false) {
            throw new \InvalidArgumentException('No passwords.txt in the archive.');
        }
        if (self::kind($pks) === null) {
            throw new \InvalidArgumentException('This file is not a Java keystore.');
        }

        return ['pks' => $pks] + self::parsePasswords($passwords);
    }
}
