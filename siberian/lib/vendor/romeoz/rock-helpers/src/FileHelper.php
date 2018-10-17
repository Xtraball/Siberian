<?php
namespace rock\helpers;

use League\Flysystem\Util;
use rock\log\Log;

/**
 * Helper "File"
 *
 * @package rock\helpers
 */
class FileHelper
{
    private static $_mimeTypes = [];

    /**
     * Normalizes a file/directory path.
     * After normalization, the directory separators in the path will be `DIRECTORY_SEPARATOR`,
     * and any trailing directory separators will be removed. For example, `/home\demo/` on Linux
     * will be normalized as '/home/demo'.
     *
     * The normalization does the following work:
     *
     * - Convert all directory separators into `DIRECTORY_SEPARATOR` (e.g. "\a/b\c" becomes "/a/b/c")
     * - Remove trailing directory separators (e.g. "/a/b/c/" becomes "/a/b/c")
     * - Turn multiple consecutive slashes into a single one (e.g. "/a///b/c" becomes "/a/b/c")
     * - Remove ".." and "." based on their meanings (e.g. "/a/./b/../c" becomes "/a/c")
     *
     * @param string $path the file/directory path to be normalized
     * @param string $ds the directory separator to be used in the normalized result. Defaults to `DIRECTORY_SEPARATOR`.
     * @param bool $clearRelative
     * @return string the normalized file/directory path
     */
    public static function normalizePath($path, $ds = DIRECTORY_SEPARATOR, $clearRelative = true)
    {
        $path = rtrim(strtr($path, ['/' => $ds, '\\' => $ds]), $ds);
        if (strpos($ds . $path, "{$ds}.") === false && strpos($path, "{$ds}{$ds}") === false) {
            return $path;
        }
        if (!$clearRelative) {
            $path = preg_replace('#' . DIRECTORY_SEPARATOR . '+#', DIRECTORY_SEPARATOR, $path);
            return $path === '' ? '.' : $path;
        }
        // the path may contain ".", ".." or double slashes, need to clean them up
        $parts = [];
        foreach (explode($ds, $path) as $part) {
            if ($part === '..' && !empty($parts) && end($parts) !== '..') {
                array_pop($parts);
            } elseif ($part === '.' || $part === '' && !empty($parts)) {
                continue;
            } else {
                $parts[] = $part;
            }
        }
        $path = implode($ds, $parts);
        return $path === '' ? '.' : $path;
    }

    /**
     * Create of file.
     *
     * @param string $pathFile path to file.
     * @param string $value value.
     * @param int $const constant for `file_put_contents`.
     * @param bool $recursive
     * @param int $mode the permission to be set for the created file.
     * @return bool
     */
    public static function create($pathFile, $value = "", $const = 0, $recursive = true, $mode = 0775)
    {
        if ($recursive === true) {
            if (!static::createDirectory(dirname($pathFile))) {
                return false;
            }
        }
        if (!file_put_contents($pathFile, $value, $const)) {
            if (class_exists('\rock\log\Log')) {
                $message = FileHelperException::convertExceptionToString(new FileHelperException(FileHelperException::NOT_CREATE_FILE, ['name' => $pathFile]));
                Log::warn($message);
            }

            return false;
        }
        chmod($pathFile, $mode);

        return true;
    }

    /**
     * Delete of file.
     *
     * @param string $path path to file
     * @return bool
     */
    public static function delete($path)
    {
        if (!file_exists($path)) {
            return false;
        }
        @unlink($path);

        return true;
    }

    /**
     * Rename file.
     *
     * @param string $oldPath old path.
     * @param string $newPath new path.
     * @return bool
     */
    public static function rename($oldPath, $newPath)
    {
        if (!@rename($oldPath, $newPath)) {
            if (class_exists('\rock\log\Log')) {
                $message = FileHelperException::convertExceptionToString(new FileHelperException("Error when renaming file: {$oldPath}"));
                Log::err($message);
            }

            return false;
        }

        return true;
    }

    /**
     * Returns the trailing name component of a path.
     *
     * This method is similar to the php function `basename()` except that it will
     * treat both `\` and `/` as directory separators, independent of the operating system.
     * This method was mainly created to work on php namespaces. When working with real
     * file paths, php's `basename()` should work fine for you.
     * Note: this method is not aware of the actual filesystem, or path components such as `..`.
     *
     * @param string $path A path string.
     * @param string $suffix If the name component ends in suffix this will also be cut off.
     * @return string the trailing name component of the given path.
     * @see http://www.php.net/manual/en/function.basename.php
     */
    public static function basename($path, $suffix = '')
    {
        $path = static::normalizePath($path);
        if (($len = mb_strlen($suffix)) > 0 && mb_substr($path, -$len) == $suffix) {
            $path = mb_substr($path, 0, -$len);
        }
        $path = rtrim($path, '/\\');
        if (($pos = mb_strrpos($path, '/')) !== false) {
            return mb_substr($path, $pos + 1);
        }

        return $path;
    }

    /**
     * Returns parent directory's path.
     *
     * This method is similar to `dirname()` except that it will treat
     * both `\` and `/` as directory separators, independent of the operating system.
     *
     * @param string $path A path string.
     * @return string the parent directory's path.
     * @see http://www.php.net/manual/en/function.basename.php
     */
    public static function dirname($path)
    {
        $path = static::normalizePath($path);
        $pos = mb_strrpos($path, '/');
        if ($pos !== false) {
            return mb_substr($path, 0, $pos);
        } else {
            return '';
        }
    }

    /**
     * Determines the MIME type of the specified file.
     *
     * This method will first try to determine the MIME type based on
     * [finfo_open](http://php.net/manual/en/function.finfo-open.php). If this doesn't work, it will
     * fall back to {@see \rock\helpers\FileHelper::getMimeTypeByExtension()}
     *
     * @param string $file the file name.
     * @param string $magicFile name of the optional magic database file, usually something like `/path/to/magic.mime`.
     *                               This will be passed as the second parameter to [finfo_open](http://php.net/manual/en/function.finfo-open.php).
     * @param bool $checkExtension whether to use the file extension to determine the MIME type in case
     *                               `finfo_open()` cannot determine it.
     * @return string|false the MIME type (e.g. `text/plain`). Null is returned if the MIME type cannot be determined.
     */
    public static function getMimeType($file, $magicFile = null, $checkExtension = true)
    {
        if (function_exists('finfo_open')) {
            $info = finfo_open(FILEINFO_MIME_TYPE, $magicFile);
            if ($info) {
                if (!$result = @finfo_file($info, $file)) {
                    return false;
                }
                finfo_close($info);
                if ($result !== false) {
                    return $result;
                }
            }
        }

        return $checkExtension ? static::getMimeTypeByExtension($file) : null;
    }

    /**
     * Determines the MIME type based on the extension name of the specified file.
     *
     * This method will use a local map between extension names and MIME types.
     *
     * @param string $file the file name.
     * @param string $magicFile the path of the file that contains all available MIME type information.
     *                          If this is not set, the default file aliased by `@rock/helpers/mimeTypes.php` will be used.
     * @return string the MIME type. Null is returned if the MIME type cannot be determined.
     */
    public static function getMimeTypeByExtension($file, $magicFile = null)
    {
        $mimeTypes = static::loadMimeTypes($magicFile);
        if (($ext = pathinfo($file, PATHINFO_EXTENSION)) !== '') {
            $ext = strtolower($ext);
            if (isset($mimeTypes[$ext])) {
                return $mimeTypes[$ext];
            }
        }

        return null;
    }

    /**
     * Determines the extensions by given MIME type.
     *
     * This method will use a local map between extension names and MIME types.
     *
     * @param string $mimeType file MIME type.
     * @param string $magicFile the path of the file that contains all available MIME type information.
     *                          If this is not set, the default file aliased by `@rock/helpers/mimeTypes.php` will be used.
     * @return array the extensions corresponding to the specified MIME type
     */
    public static function getExtensionsByMimeType($mimeType, $magicFile = null)
    {
        $mimeTypes = static::loadMimeTypes($magicFile);

        return array_keys($mimeTypes, mb_strtolower($mimeType, 'utf-8'), true);
    }

    /**
     * Creates a new directory.
     *
     * This method is similar to the PHP `mkdir()` function except that
     * it uses `chmod()` to set the permission of the created directory
     * in order to avoid the impact of the `umask` setting.
     *
     * @param string $path path of the directory to be created.
     * @param integer $mode the permission to be set for the created directory.
     * @param bool $recursive whether to create parent directories if they do not exist.
     * @return bool whether the directory is created successfully
     */
    public static function createDirectory($path, $mode = 0775, $recursive = true)
    {
        if (is_dir($path)) {
            return true;
        }
        $parentDir = dirname($path);
        if ($recursive && !is_dir($parentDir)) {
            static::createDirectory($parentDir, $mode, true);
        }
        if (!$result = @mkdir($path, $mode)) {
            if (class_exists('\rock\log\Log')) {
                $message = FileHelperException::convertExceptionToString(new FileHelperException(FileHelperException::NOT_CREATE_DIR, ['name' => $path]));
                Log::warn($message);
            }
            return false;
        }
        chmod($path, $mode);

        return $result;
    }

    /**
     * Copies a whole directory as another one.
     *
     * The files and sub-directories will also be copied over.
     *
     * @param string $src the source directory
     * @param string $dst the destination directory
     * @param array $options options for directory copy. Valid options are:
     *
     * - dirMode: integer, the permission to be set for newly copied directories. Defaults to `0775`.
     * - fileMode:  integer, the permission to be set for newly copied files. Defaults to the current environment setting.
     * - filter: callback, a PHP callback that is called for each directory or file.
     *   The signature of the callback should be: `function ($path)`, where `$path` refers the full path to be filtered.
     *   The callback can return one of the following values:
     *
     *   * true: the directory or file will be copied (the "only" and "except" options will be ignored)
     *   * false: the directory or file will NOT be copied (the "only" and "except" options will be ignored)
     *   * null: the "only" and "except" options will determine whether the directory or file should be copied
     *
     * - recursive: bool, whether the files under the subdirectories should also be copied. Defaults to true.
     * - beforeCopy: callback, a PHP callback that is called before copying each sub-directory or file.
     *   If the callback returns false, the copy operation for the sub-directory or file will be cancelled.
     *   The signature of the callback should be: `function ($from, $to)`, where `$from` is the sub-directory or
     *   file to be copied from, while `$to` is the copy target.
     * - afterCopy: callback, a PHP callback that is called after each sub-directory or file is successfully copied.
     *   The signature of the callback should be: `function ($from, $to)`, where `$from` is the sub-directory or
     *   file copied from, while `$to` is the copy target.
     * @return bool
     */
    public static function copyDirectory($src, $dst, $options = [])
    {
        if (!is_dir($dst)) {
            static::createDirectory($dst, isset($options['dirMode']) ? $options['dirMode'] : 0775, true);
        }

        $handle = opendir($src);
        if ($handle === false) {
            if (class_exists('\rock\log\Log')) {
                $message = FileHelperException::convertExceptionToString(new FileHelperException('Unable to open directory: ' . $src));
                Log::warn($message);
            }
            return false;
        }
        if (!isset($options['basePath'])) {
            // this should be done only once
            $options['basePath'] = realpath($src);
            $options = self::normalizeOptions($options);
        }
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $from = $src . DIRECTORY_SEPARATOR . $file;
            $to = $dst . DIRECTORY_SEPARATOR . $file;
            if (static::filterPath($from, $options)) {
                if (isset($options['beforeCopy']) && !call_user_func($options['beforeCopy'], $from, $to)) {
                    continue;
                }
                if (is_file($from)) {
                    copy($from, $to);
                    if (isset($options['fileMode'])) {
                        @chmod($to, $options['fileMode']);
                    }
                } else {
                    static::copyDirectory($from, $to, $options);
                }
                if (isset($options['afterCopy'])) {
                    call_user_func($options['afterCopy'], $from, $to);
                }
            }
        }
        closedir($handle);
        return true;
    }

    /**
     * Removes a directory (and all its content) recursively.
     *
     * @param string $dir the directory to be deleted recursively.
     * @param array $options options for directory remove. Valid options are:
     *
     * - traverseSymlinks: boolean, whether symlinks to the directories should be traversed too.
     *   Defaults to `false`, meaning the content of the symlinked directory would not be deleted.
     *   Only symlink would be removed in that default case.
     * @return bool
     */
    public static function deleteDirectory($dir, $options = [])
    {
        if (!is_dir($dir)) {
            return false;
        }
        if (!is_link($dir) || isset($options['traverseSymlinks']) && $options['traverseSymlinks']) {
            if (!($handle = opendir($dir))) {
                return false;
            }
            while (($file = readdir($handle)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $path = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($path)) {
                    static::deleteDirectory($path, $options);
                } else {
                    unlink($path);
                }
            }
            closedir($handle);
        }
        if (is_link($dir)) {
            unlink($dir);
        } else {
            rmdir($dir);
        }
        return true;
    }

    /**
     * Checks if the given file path satisfies the filtering options.
     *
     * @param string $path the path of the file or directory to be checked
     * @param array $options the filtering options.
     * @return bool whether the file or directory satisfies the filtering options.
     */
    public static function filterPath($path, $options)
    {
        if (isset($options['filter'])) {
            $result = call_user_func($options['filter'], $path);
            if (is_bool($result)) {
                return $result;
            }
        }

        return true;
    }

    /**
     * Converts php.ini style size to bytes.
     *
     * @param string $sizeStr
     * @return int
     */
    public static function sizeToBytes($sizeStr)
    {
        if (!is_string($sizeStr)) {
            return $sizeStr;
        }
        switch (substr($sizeStr, -1)) {
            case 'M':
            case 'm':
                return (int)$sizeStr * 1048576;
            case 'K':
            case 'k':
                return (int)$sizeStr * 1024;
            case 'G':
            case 'g':
                return (int)$sizeStr * 1073741824;
            default:
                return (int)$sizeStr;
        }
    }

    /**
     * Fix for overflowing signed 32 bit integers,
     * works for sizes up to 2^32-1 bytes (4 GiB - 1).
     *
     * @param int $size
     * @return float
     */
    public static function fixedIntegerOverflow($size)
    {
        if ($size < 0) {
            $size += 2.0 * (PHP_INT_MAX + 1);
        }

        return $size;
    }

    /**
     * @param array $options raw options
     * @return array normalized options
     */
    private static function normalizeOptions(array $options)
    {
        if (!array_key_exists('caseSensitive', $options)) {
            $options['caseSensitive'] = true;
        }
        return $options;
    }

    /**
     * Loads MIME types from the specified file.
     *
     * @param string $magicFile the file that contains MIME type information.
     *                          If null, the file `@rock/helpers/mimeTypes.php` will be used.
     * @return array the mapping from file extensions to MIME types
     */
    protected static function loadMimeTypes($magicFile)
    {
        if ($magicFile === null) {
            $magicFile = __DIR__ . '/mimeTypes.php';
        }
        if (!isset(self::$_mimeTypes[$magicFile])) {
            self::$_mimeTypes[$magicFile] = require($magicFile);
        }

        return self::$_mimeTypes[$magicFile];
    }
}