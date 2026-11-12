<?php
/**
 * Settings > Translations lists the older Locale<Language> packs next to the locales shipped in the core, compares them,
 * and switches to the built-in locale after an automatic backup (a plain copy, listed on the page, restorable).
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
require_once $siberian . '/lib/Siberian/Locale/Packs.php';

use Siberian\Locale\Packs;

$controllerFile = $siberian . '/app/sae/modules/Translation/controllers/Backoffice/PackController.php';
$poFile = $siberian . '/languages/base/c_translation.po';

if (!function_exists('p__')) {
    function p__($context, $text)
    {
        return $text;
    }
}

/** A fake installation: root with a pack for fr (and its pristine languages/fr), plus the shipped fr catalog. */
$makeRoot = static function () {
    $root = sys_get_temp_dir() . '/packs-' . bin2hex(random_bytes(4));
    $mod = $root . '/app/local/modules/LocaleFrench';
    mkdir($mod . '/resources/translations/fr', 0777, true);
    mkdir($root . '/languages/fr', 0777, true);
    mkdir($root . '/shipped/fr', 0777, true);
    file_put_contents($mod . '/package.json', json_encode(['name' => 'LocaleFrench', 'version' => '1.1.8']));
    file_put_contents($root . '/languages/fr/default.csv', '"";""' . "\n");
    $po = "msgid \"\"\nmsgstr \"\"\n\"Language: fr\\n\"\n\n"
        . "msgid \"Save\"\nmsgstr \"Enregistrer\"\n\n"
        . "msgid \"Only in pack\"\nmsgstr \"Seulement ici\"\n\n"
        . "# c\nmsgctxt \"admin\"\nmsgid \"Hello\"\nmsgstr \"Bonjour\"\n\n"
        . "#, fuzzy\nmsgid \"Fuzzy\"\nmsgstr \"Flou\"\n\n"
        . "msgid \"Empty\"\nmsgstr \"\"\n";
    file_put_contents($mod . '/resources/translations/fr/default.po', $po);
    file_put_contents($root . '/shipped/fr/default.po',
        "msgid \"\"\nmsgstr \"\"\n\nmsgid \"Save\"\nmsgstr \"Sauvegarder\"\n\n"
        . "msgctxt \"admin\"\nmsgid \"Hello\"\nmsgstr \"Bonjour\"\n");
    return $root;
};
$rm = static function ($dir) {
    exec('rm -rf ' . escapeshellarg($dir));
};

return [
    'a Locale pack for a shipped language is detected, other modules and unknown codes are not' => function () use ($makeRoot, $rm) {
        $root = $makeRoot();
        mkdir($root . '/app/local/modules/Locales/resources/translations/fr', 0777, true);
        mkdir($root . '/app/local/modules/LocaleKlingon/resources/translations/tlh', 0777, true);
        mkdir($root . '/app/local/modules/Weather', 0777, true);
        $packs = Packs::detect($root . '/app/local/modules', ['fr', 'de']);
        t_equal(['fr'], array_keys($packs));
        t_equal('LocaleFrench', $packs['fr']['module']);
        t_equal('1.1.8', $packs['fr']['version']);
        t_true($packs['fr']['enabled'], 'enabled');
        file_put_contents($root . '/app/local/modules/LocaleFrench/module.disabled', '1');
        t_true(!Packs::detect($root . '/app/local/modules', ['fr'])['fr']['enabled'], 'disabled');
        $rm($root);
    },

    'compare counts what only the pack translates and what differs, fuzzy and empty are ignored' => function () use ($makeRoot, $rm) {
        $root = $makeRoot();
        $r = Packs::compare($root . '/app/local/modules/LocaleFrench/resources/translations/fr', $root . '/shipped/fr');
        t_equal(3, $r['pack'], 'pack');
        t_equal(2, $r['shipped'], 'shipped');
        t_equal(1, $r['only_in_pack'], 'only in pack');
        t_equal(1, $r['different'], 'different');
        $rm($root);
    },

    'a switched-on pack that the shipped catalog fully covers is stale, one with texts of its own is not' => function () use ($makeRoot, $rm) {
        $root = $makeRoot();
        $packDir = $root . '/app/local/modules/LocaleFrench/resources/translations/fr';
        $partial = Packs::compare($packDir, $root . '/shipped/fr');
        t_true(!Packs::isStale($partial, true), 'texts only in the pack: not stale');

        // the shipped catalog now translates everything the pack does
        file_put_contents($root . '/shipped/fr/default.po', file_get_contents($packDir . '/default.po'));
        $covered = Packs::compare($packDir, $root . '/shipped/fr');
        t_equal(0, $covered['only_in_pack']);
        t_true(Packs::isStale($covered, true), 'covered and enabled: stale');
        t_true(!Packs::isStale($covered, false), 'already disabled: nothing to advise');
        t_true(!Packs::isStale(['pack' => 0, 'shipped' => 0, 'only_in_pack' => 0, 'different' => 0], true), 'no shipped catalog: not stale');
        $rm($root);
    },

    'the list exposes the verdict and the page offers the disable action with the texts in a catalog' => function () use ($controllerFile, $poFile) {
        $src = file_get_contents($controllerFile);
        t_true(strpos($src, "'stale' => Packs::isStale(") !== false, 'findall returns stale');
        $dir = dirname($controllerFile, 3) . '/resources/design/desktop/backoffice';
        $tpl = file_get_contents($dir . '/template/translation/list.phtml');
        t_true(strpos($tpl, 'stalePacks().length') !== false && strpos($tpl, "switchPack(pack, 'disable')") !== false, 'banner with the disable action');
        t_true(strpos(file_get_contents($dir . '/js/controllers/translation.js'), '$scope.stalePacks') !== false, 'scope function');
        $po = file_get_contents($poFile);
        foreach (['Outdated language packs', 'Outdated'] as $text) {
            t_true(strpos($po, 'msgid "' . $text . '"') !== false, "missing in c_translation.po: {$text}");
        }
    },

    'pristine means only the empty default.csv' => function () use ($makeRoot, $rm) {
        $root = $makeRoot();
        t_true(Packs::isPristine($root . '/languages/fr'), 'pristine');
        file_put_contents($root . '/languages/fr/default.po', 'x');
        t_true(!Packs::isPristine($root . '/languages/fr'), 'edited');
        t_true(!Packs::isPristine($root . '/languages/xx'), 'missing');
        $rm($root);
    },

    'backup, remove and restore bring the pack back as it was' => function () use ($makeRoot, $rm) {
        $root = $makeRoot();
        $pack = Packs::detect($root . '/app/local/modules', ['fr'])['fr'];
        $backupDir = $root . '/var/backup/locales';
        $manifest = Packs::backup($root, $pack, $backupDir, 'uninstall', 1793440000, ['row' => ['name' => 'LocaleFrench']]);
        t_true(preg_match(Packs::ID_PATTERN, $manifest['id']) === 1, 'id shape');
        t_true(is_file($backupDir . '/.htaccess'), 'backup folder is not served');

        t_true(Packs::remove($root, $pack), 'pristine language folder removed too');
        t_true(!is_dir($root . '/app/local/modules/LocaleFrench') && !is_dir($root . '/languages/fr'), 'removed');

        $list = Packs::listBackups($backupDir);
        t_equal(1, count($list));
        t_equal('uninstall', $list[0]['action']);
        t_true($list[0]['size'] > 0, 'size');

        $back = Packs::restore($root, $backupDir, $manifest['id']);
        t_equal(['name' => 'LocaleFrench'], $back['extra']['row']);
        t_true(is_file($root . '/app/local/modules/LocaleFrench/resources/translations/fr/default.po'), 'module back');
        t_true(is_file($root . '/languages/fr/default.csv'), 'language folder back');

        Packs::deleteBackup($backupDir, $manifest['id']);
        t_equal([], Packs::listBackups($backupDir));
        $rm($root);
    },

    'a language folder with user translations is kept when the pack is removed' => function () use ($makeRoot, $rm) {
        $root = $makeRoot();
        file_put_contents($root . '/languages/fr/default.po', 'msgid "a"' . "\n");
        $pack = Packs::detect($root . '/app/local/modules', ['fr'])['fr'];
        t_true(!Packs::remove($root, $pack), 'language folder kept');
        t_true(is_file($root . '/languages/fr/default.po'), 'user file kept');
        $rm($root);
    },

    'a backup id from a request cannot leave the backup folder' => function () use ($makeRoot, $rm) {
        $root = $makeRoot();
        $dir = $root . '/var/backup/locales';
        mkdir($dir, 0777, true);
        foreach (['../../etc', '..', '20261031-101010-fr-LocaleFrench/../x', '', "20261031-101010-fr-LocaleFrench\0"] as $id) {
            $threw = false;
            try {
                Packs::deleteBackup($dir, $id);
            } catch (\Exception $e) {
                $threw = true;
            }
            t_true($threw, 'rejected: ' . json_encode($id));
        }
        t_true(is_dir($root . '/app'), 'nothing removed');
        $rm($root);
    },

    'the controller backs up before it changes anything, guards the demo and has every text in a catalog' => function () use ($controllerFile, $poFile) {
        $src = file_get_contents($controllerFile);
        $switch = substr($src, strpos($src, 'function switchAction'), 2500);
        t_true(strpos($switch, 'Packs::backup(') < strpos($switch, 'Packs::remove('), 'backup first (uninstall)');
        t_true(strpos($switch, 'Packs::backup(') < strpos($switch, 'toggleIsEnabled(false)'), 'backup first (disable)');
        t_equal(3, substr_count($src, '$this->denyDemo();'), 'every writing action is guarded');
        t_true(strpos($src, 'Siberian_Cache_Translation::clearCache()') !== false, 'translation cache cleared after a switch or restore');
        $po = file_get_contents($poFile);
        preg_match_all('/p__\(\s*[\'"]translation[\'"]\s*,\s*[\'"]((?:[^\'"\\\\]|\\\\.)*)[\'"]/', $src, $m);
        t_true(count($m[1]) >= 7, 'texts found');
        foreach (array_unique($m[1]) as $text) {
            t_true(strpos($po, 'msgid "' . stripslashes($text) . '"') !== false, "missing in c_translation.po: {$text}");
        }
    },
];
