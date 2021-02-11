<?php

/**
 * Class Manifest
 *
 * @version 4.19.1
 */
class Manifest
{
    /**
     * Default excluded patterns
     *
     * @var array
     */
    public static $exclude_patterns = [
        "\.gitignore",
        "\.gitattributes",
        "\.git",
        "\.htaccess",
        "htaccess.txt",
        ".*\.md$",
        "\.gitignore",
        "\.DS_Store",
        "\.idea",
        "npm-debug.log",
        "chcp\.json$",
        "chcp\.manifest$",
        "index-prod\.html$",
        "prod\.css$",
        "prod\.js$",
        "^config\.php",
        "^app/local/",
        "^app/configs/",
        "^docs/",
        "^errors/",
        "^external/",
        "^images/",
        "^languages/",
        "^lib/Stripe/",
        "^metrics/",
        "^var/apps/ionic/tools/",
        "^var/(cache|log|session|tmp|schema)",
        "^var/apps.*index\.html$",
        "^var/apps.*config\.xml$",
        "^app/sae/modules.*dummy_.*\.xml",
        "^var/apps.*inbox.*",
        "^var/apps.*dist/templates\.js",
        "^lib/vendor/symfony/finder/Tests/Fixtures.*",
        "user-style.css",
        "^var/apps/browser.tgz",
        "^var/apps/ionic/android.tgz",
        "^var/apps/ionic/ios.tgz",
        "^var/apps/ionic/ios-noads.tgz",
        "^var/apps/ionic/previewer.tgz",
        "^var/pid/*",
        "favicon\.",
        "manifest\.json",
        "app/sae/design/desktop/flat/images/header/logo\.png",
        "lib/vendor/gregwar/image/Gregwar/Image/phpunit\.xml\.dist",
    ];

    /**
     * @var string
     */
    public static $manifest = "manifest.json";

    /**
     * @param $path
     * @param null $manifest
     */
    public static function build($path, $manifest = null)
    {
        $hash = [];

        /** Looping trough files */
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, 4096),
            RecursiveIteratorIterator::SELF_FIRST);

        foreach ($files as $file) {
            if ($file->isDir()) {
                continue;
            }

            $pathname = $file->getPathname();
            $relative_path = str_replace($path, "", $pathname);

            # Add only required files
            if (!self::exclude($relative_path)) {
                $hash[] = [
                    'file' => $relative_path,
                    'hash' => md5_file($pathname),
                ];
            }
        }

        $manifest_path = $path . self::$manifest;
        if ($manifest !== null) {
            $manifest_path = $manifest . self::$manifest;
        }
        file_put_contents($manifest_path,
            json_encode($hash, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Test if file matches one of the exclude pattern
     *
     * @param $file
     * @return bool
     */
    public static function exclude($file)
    {
        foreach (self::$exclude_patterns as $pattern) {
            if (preg_match('#' . $pattern . '#i', $file)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string|array $patterns
     */
    public static function addExcludePatterns($patterns)
    {
        if (!is_array($patterns)) {
            $patterns = [$patterns];
        }
        foreach ($patterns as $pattern) {
            self::$exclude_patterns[] = $pattern;
        }
    }

}

if (isset($argv) && isset($argv[1]) && isset($argv[2]) && isset($argv[3])) {
    $type = strtolower($argv[1]);
    switch ($type) {
        case 'sae':
            Manifest::addExcludePatterns([
                '^app/pe/',
                '^app/mae/',
            ]);
            break;
        case 'mae':
            Manifest::addExcludePatterns([
                '^app/pe/',
            ]);
            break;
        case 'pe':

            break;
    }

    Manifest::build($argv[2], $argv[3]);
} else {
    echo 'Unable to build manifest' . PHP_EOL;
}

