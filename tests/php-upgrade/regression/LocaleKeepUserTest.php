<?php
/**
 * Nothing we ship or run may override a locale or a translation a user already has: the locales shipped in the core are
 * indexed after every other source, a backup is never restored over an installed pack, a backup never reuses a folder.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
require_once $siberian . '/lib/Siberian/Locale/Catalogs.php';
require_once $siberian . '/lib/Siberian/Locale/Packs.php';

use Siberian\Locale\Catalogs;
use Siberian\Locale\Packs;

$makeModule = static function ($root, $name, $code, $files) {
    $dir = "{$root}/modules/{$name}/resources/translations/{$code}";
    mkdir($dir, 0777, true);
    foreach ($files as $file => $body) {
        file_put_contents("{$dir}/{$file}", $body);
    }
    return "{$root}/modules/{$name}";
};
$tmp = static function () {
    return sys_get_temp_dir() . '/keep-' . bin2hex(random_bytes(4));
};
$rm = static function ($dir) {
    exec('rm -rf ' . escapeshellarg($dir));
};

return [
    'a pack installed by the user keeps its file when a shipped locale has the same code and file name' => function () use ($makeModule, $tmp, $rm) {
        $root = $tmp();
        $pack = $makeModule($root, 'LocaleFrench', 'fr', ['admin.po' => 'pack', 'only_pack.po' => 'pack']);
        $shipped = $makeModule($root, Catalogs::SHIPPED_MODULE, 'fr', ['admin.po' => 'shipped', 'only_shipped.po' => 'shipped']);

        // same order as Siberian\Cache\Translation::walk(): every installed module, then the shipped locales
        $cache = Catalogs::indexModule($pack, []);
        $cache = Catalogs::indexModule($shipped, $cache);

        t_equal("{$pack}/resources/translations/fr/admin.po", $cache['fr']['admin.po'], 'the pack wins');
        t_equal("{$shipped}/resources/translations/fr/only_shipped.po", $cache['fr']['only_shipped.po'], 'the shipped file only fills a gap');
        t_true(isset($cache['fr']['only_pack.po']), 'pack file kept');
        $rm($root);
    },

    'the translation cache indexes the shipped module last and skips it in the module loop' => function () use ($siberian) {
        $src = file_get_contents($siberian . '/lib/Siberian/Cache/Translation.php');
        t_true(strpos($src, 'Catalogs::SHIPPED_MODULE') !== false, 'shipped module named');
        $walk = substr($src, strpos($src, 'public static function walk'), 900);
        t_true(strpos($walk, 'static::fetchShipped()') > strpos($walk, 'static::fetch(self::LOCAL_PATH)'), 'shipped locales are indexed after app/local');
        $fetch = substr($src, strpos($src, 'public static function fetch('), 1200);
        t_true(strpos($fetch, '$isCore && $module_folder->getFilename() === Catalogs::SHIPPED_MODULE') !== false, 'skipped in the module loop');
    },

    'a backup is not restored over a pack that is installed, nothing is changed' => function () use ($tmp, $rm) {
        $root = $tmp();
        $mod = $root . '/app/local/modules/LocaleFrench';
        mkdir($mod . '/resources/translations/fr', 0777, true);
        file_put_contents($mod . '/resources/translations/fr/default.po', 'original');
        $pack = Packs::detect($root . '/app/local/modules', ['fr'])['fr'];
        $manifest = Packs::backup($root, $pack, $root . '/var/backup/locales', 'disable', 1793440000);

        // the user edits the pack after the backup
        file_put_contents($mod . '/resources/translations/fr/default.po', 'edited by the user');
        file_put_contents($mod . '/mine.txt', 'mine');

        $threw = false;
        try {
            Packs::restore($root, $root . '/var/backup/locales', $manifest['id']);
        } catch (\Exception $e) {
            $threw = true;
        }
        t_true($threw, 'restore refused');
        t_equal('edited by the user', file_get_contents($mod . '/resources/translations/fr/default.po'));
        t_true(is_file($mod . '/mine.txt'), 'user file kept');
        $rm($root);
    },

    'a second backup in the same second does not overwrite the first' => function () use ($tmp, $rm) {
        $root = $tmp();
        $mod = $root . '/app/local/modules/LocaleFrench';
        mkdir($mod . '/resources/translations/fr', 0777, true);
        file_put_contents($mod . '/resources/translations/fr/default.po', 'first');
        $pack = Packs::detect($root . '/app/local/modules', ['fr'])['fr'];
        $dir = $root . '/var/backup/locales';
        $manifest = Packs::backup($root, $pack, $dir, 'disable', 1793440000);
        file_put_contents($mod . '/resources/translations/fr/default.po', 'second');
        $threw = false;
        try {
            Packs::backup($root, $pack, $dir, 'disable', 1793440000);
        } catch (\Exception $e) {
            $threw = true;
        }
        t_true($threw, 'refused');
        t_equal('first', file_get_contents("{$dir}/{$manifest['id']}/module/resources/translations/fr/default.po"));
        $rm($root);
    },
];
