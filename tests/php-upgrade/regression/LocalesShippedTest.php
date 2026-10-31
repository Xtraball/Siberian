<?php
/**
 * Locales are shipped in trunk (app/sae/modules/Locales/resources/translations/<code>/*.po) and an admin switches them on
 * one by one in Settings > Translations. Off by default: an install that did not switch anything lists the same languages
 * as before (folders in languages/ plus English). The stored value is narrowed to the shipped codes.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
$languageFile = $siberian . '/app/sae/modules/Core/Model/Language.php';
$controllerFile = $siberian . '/app/sae/modules/Translation/controllers/Backoffice/ListController.php';
$templateFile = $siberian . '/app/sae/modules/Translation/resources/design/desktop/backoffice/template/translation/list.phtml';
$shipped = $siberian . '/app/sae/modules/Locales/resources/translations';
$poFile = $siberian . '/languages/base/c_translation.po';

require_once $languageFile;

if (!function_exists('p__')) {
    function p__($context, $text)
    {
        return $text;
    }
}

return [
    'nothing stored means nothing enabled' => function () {
        t_equal([], Core_Model_Language::parseEnabledLocales(null, ['fr', 'de']));
        t_equal([], Core_Model_Language::parseEnabledLocales('', ['fr', 'de']));
        t_equal([], Core_Model_Language::parseEnabledLocales('not json', ['fr', 'de']));
        t_equal([], Core_Model_Language::parseEnabledLocales('{"a":1}x', ['fr', 'de']));
    },

    'the stored list is narrowed to the shipped locales' => function () {
        t_equal(['fr'], Core_Model_Language::parseEnabledLocales('["fr","xx","../etc"]', ['fr', 'de']));
        t_equal(['de', 'fr'], Core_Model_Language::parseEnabledLocales('["de","fr","de",5,null]', ['fr', 'de']));
    },

    'prepare registers a shipped locale only through the enabled list' => function () use ($languageFile) {
        $src = file_get_contents($languageFile);
        t_true(strpos($src, 'foreach (self::getEnabledLocales() as $code)') !== false, 'enabled loop');
        t_true(strpos($src, 'foreach (self::getShippedLocales() as $code) {' . "\n" . '                self::registerLanguage') === false,
            'shipped locales are never registered unconditionally');
    },

    'folders in languages/ keep working and cannot be toggled' => function () use ($languageFile) {
        $src = file_get_contents($languageFile);
        t_true(strpos($src, 'if (!in_array($code, $shipped, true) || self::hasLanguageDirectory($code))') !== false, 'toggle guard');
        t_true(strpos($src, "if (!\$directory->isDot() && isset(\$territories[\$dir_name])) {\n                self::registerLanguage") !== false,
            'legacy scan kept');
    },

    'switching a locale clears the translation file cache, otherwise it keeps its empty default translations' => function () use ($languageFile) {
        $src = file_get_contents($languageFile);
        $toggle = substr($src, strpos($src, 'public static function toggleLocale'), 1500);
        t_true(strpos($toggle, 'setValueFor(') !== false, 'saves the setting');
        t_true(strpos($toggle, 'Siberian_Cache_Translation::clearCache()') > strpos($toggle, 'setValueFor('), 'clears the cache after saving');
    },

    'fourteen locales are shipped, each with catalogs and LF line endings' => function () use ($shipped) {
        $codes = array_map('basename', glob($shipped . '/*', GLOB_ONLYDIR));
        sort($codes);
        t_equal(['ca', 'cs', 'de', 'el', 'es', 'fr', 'hi', 'it', 'ja', 'nl', 'pt_BR', 'ru', 'tr', 'zh_Hans'], $codes);
        foreach ($codes as $code) {
            $files = glob("{$shipped}/{$code}/*.po");
            t_true(count($files) >= 40, "{$code} has " . count($files) . ' catalogs');
            foreach ($files as $file) {
                t_true(strpos(file_get_contents($file), "\r") === false, "{$code}/" . basename($file) . ' has CR');
            }
        }
    },

    'the backoffice list exposes a toggle and the texts are in a catalog' => function () use ($controllerFile, $templateFile, $poFile, $languageFile) {
        $controller = file_get_contents($controllerFile);
        t_true(strpos($controller, 'function toggleAction()') !== false, 'toggleAction');
        t_true(strpos($controller, 'is_demo') !== false, 'demo guard');
        $po = file_get_contents($poFile);
        $texts = [];
        foreach ([$controller, file_get_contents($templateFile), file_get_contents($languageFile)] as $src) {
            preg_match_all('/p__\(\s*["\']translation["\']\s*,\s*["\']((?:[^"\'\\\\]|\\\\.)*)["\']/', $src, $m);
            $texts = array_merge($texts, $m[1]);
        }
        t_true(count($texts) >= 8, 'found ' . count($texts) . ' texts');
        foreach (array_unique($texts) as $text) {
            t_true(strpos($po, 'msgid "' . stripslashes($text) . '"') !== false, "missing in c_translation.po: {$text}");
        }
    },
];
