<?php

namespace Siberian;

/**
 * A path that came from outside, resolved and kept inside the directories it may name.
 *
 * Why: checking that a path starts with "/var/tmp" is not enough, "/var/tmp/../../app/configs/app.ini" starts with it
 * too. The check has to be on the real path, after the dots and the symbolic links are resolved.
 */
class SafePath
{
    /**
     * @param mixed $path as received
     * @param string[] $roots the directories it may be in (a root that does not exist allows nothing)
     * @return string|null the real path of an existing file below one of the roots, null otherwise
     */
    public static function file($path, array $roots)
    {
        if (!is_string($path) || $path === '' || strpos($path, "\0") !== false) {
            return null;
        }

        $real = realpath($path);
        if ($real === false || !is_file($real)) {
            return null;
        }

        foreach ($roots as $root) {
            $realRoot = is_string($root) ? realpath($root) : false;
            if ($realRoot === false) {
                continue;
            }
            $prefix = rtrim($realRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            if (strpos($real, $prefix) === 0) {
                return $real;
            }
        }

        return null;
    }

    /**
     * A name for the Content-Disposition header: no directory, no quote, no control character.
     *
     * @param mixed $name
     * @param string $default
     * @return string
     */
    public static function downloadName($name, $default = 'download')
    {
        $name = is_string($name) ? basename(str_replace('\\', '/', $name)) : '';
        $name = trim((string) preg_replace('/[\x00-\x1f\x7f"\\\\;]+/', '', $name));

        return $name === '' || $name === '.' || $name === '..' ? $default : $name;
    }
}
