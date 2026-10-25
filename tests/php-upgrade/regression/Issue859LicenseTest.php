<?php
/**
 * #859: an expired or refused module key can be replaced by a new one. Everything else stays as it was: a valid key
 * does not prompt, a prompt that is cancelled keeps the installation going, a key typed by the user is checked as before.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
$licenseFile = $siberian . '/lib/Siberian/License.php';
$exceptionFile = $siberian . '/lib/Siberian/License/ReplaceableException.php';
$controllerFile = $siberian . '/app/sae/modules/Installer/controllers/Backoffice/ModuleController.php';
$uiFile = $siberian . '/app/sae/design/desktop/backoffice/js/controllers/installer.js';
$poFile = $siberian . '/languages/base/c_backoffice.po';

if (!class_exists('Siberian\License', false)) {
    require_once $licenseFile;
}
if (!class_exists('Siberian\License\ReplaceableException', false)) {
    require_once $exceptionFile;
}

return [
    '859 only statuses that a new key can fix are replaceable' => function () {
        foreach (['expired', 'disabled', 'revoked', 'missing'] as $status) {
            t_true(\Siberian\License::isReplaceableStatus($status), $status);
        }
        foreach (['valid', 'inactive', 'site_inactive', 'invalid', 'invalid_item_id', '', null, false, 0] as $status) {
            t_true(!\Siberian\License::isReplaceableStatus($status), var_export($status, true));
        }
    },

    '859 the exception is a regular exception and the old statuses still throw the old way' => function () use ($licenseFile) {
        $e = new \Siberian\License\ReplaceableException('x');
        t_true($e instanceof \Exception, 'extends Exception');
        $code = file_get_contents($licenseFile);
        foreach (['site_inactive', 'invalid', 'invalid_item_id', 'inactive'] as $status) {
            t_equal(1, substr_count($code, "=== '$status'"), $status . ' branch kept');
        }
    },

    '859 a key typed by the user is checked exactly as before' => function () use ($licenseFile) {
        $code = file_get_contents($licenseFile);
        t_true(strpos($code, '$license === null && self::isReplaceableStatus(') !== false, 'only for the saved key');
    },

    '859 the controller answers with a soft code, other failures keep license_error' => function () use ($controllerFile) {
        $code = file_get_contents($controllerFile);
        t_true(strpos($code, "catch (\\Siberian\\License\\ReplaceableException \$e) {\n                \$code = 'license_replaceable';") !== false, 'soft code');
        t_true(strpos($code, "} catch (\\Exception \$e) {\n                \$code = 'license_error';") !== false, 'license_error kept');
    },

    '859 the UI keeps the install going when the prompt is cancelled' => function () use ($uiFile) {
        $ui = file_get_contents($uiFile);
        t_true(strpos($ui, "if (error === 'license_replaceable') {") !== false, 'handled');
        t_true(strpos($ui, '$scope.setLicense(code, itemId, true);') !== false, 'prompt in replace mode');
        t_true((bool) preg_match('/if \(isReplaceable && value === false\) \{[^}]*is_visible = true;\s*return;/s', $ui), 'cancel keeps going');
        t_true(strpos($ui, "if (error === 'license_error') {\n                    // Prompt to change license\n                    \$scope.setLicense(code, itemId);") !== false, 'regular prompt unchanged');
    },

    '859 a refused saved key on the downloader path asks for a new one' => function () use ($uiFile) {
        $ui = file_get_contents($uiFile);
        t_true(strpos($ui, "data.error && data.code === 'module_license') {") !== false, 'asks even without a typed key');
        t_true(strpos($ui, "data.code === 'module_license' && license") === false, 'no longer requires a typed key');
    },

    '859 the new texts are in the translation catalog (English is the default)' => function () use ($controllerFile, $poFile) {
        $code = file_get_contents($controllerFile);
        t_true((bool) preg_match_all("/'(?:replaceLicense|keepLicense)' => p__\\('backoffice', '((?:[^'\\\\\\\\]|\\\\\\\\.)*)'\\)/", $code, $m), 'texts found');
        t_equal(2, count($m[1]), 'two texts');
        $po = file_get_contents($poFile);
        foreach ($m[1] as $text) {
            $entry = 'msgctxt "backoffice"' . "\n" . 'msgid "' . $text . '"' . "\n" . 'msgstr "' . $text . '"';
            t_true(strpos($po, $entry) !== false, 'catalog has: ' . $text);
        }
    },
];
