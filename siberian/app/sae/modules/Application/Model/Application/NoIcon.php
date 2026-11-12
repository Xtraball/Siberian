<?php

/**
 * Class Application_Model_Application_NoIcon
 *
 * An app without an uploaded icon has the grey placeholder as icon. The source generators (Android, iOS) need a
 * file to copy; getIcon() and getAndroidSplashIcon() used to return the placeholder as inline image data, which the
 * generators skip silently. The Android shell then kept the template icon and pointed its theme at
 * @mipmap/ic_splash, which was never written, so the app did not build.
 */
class Application_Model_Application_NoIcon
{
    /**
     * @param string $placeholder the placeholder image
     * @param string $dir where the copy goes
     * @return string path of a readable copy of the placeholder
     * @throws RuntimeException
     */
    public static function file(string $placeholder, string $dir): string
    {
        if (!is_file($placeholder) || !is_readable($placeholder)) {
            throw new RuntimeException('The placeholder icon is missing: ' . $placeholder);
        }

        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('Cannot create ' . $dir);
        }

        // The name follows the content, so a changed placeholder is a new file.
        $dest = rtrim($dir, '/') . '/no-icon-' . substr(sha1_file($placeholder), 0, 12) . '.png';
        if (!is_file($dest)) {
            $tmp = $dest . '.' . uniqid('', true);
            if (!copy($placeholder, $tmp) || !rename($tmp, $dest)) {
                @unlink($tmp);
                throw new RuntimeException('Cannot write ' . $dest);
            }
        }

        return $dest;
    }
}
