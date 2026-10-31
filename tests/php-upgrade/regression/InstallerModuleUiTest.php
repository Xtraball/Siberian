<?php
/**
 * Module installer page (Settings > Updates & Modules): one click-or-drop area, a labelled "Check for updates"
 * button, feedback where the page used to stay silent, and the contract between the page and the controller actions.
 *
 * The behaviour of the actions themselves (upload, permissions, copy, install, licence, module updates) is unchanged.
 * The UI actions are driven for real (controller, template and angular-file-upload in jsdom) by
 * tests/jsdom/installer-module-ui.js, run it with `NODE_PATH=<dir with jsdom> node tests/jsdom/installer-module-ui.js`.
 */
$root = dirname(__DIR__, 3);
$backoffice = $root . '/siberian/app/sae/design/desktop/backoffice';
$template = file_get_contents($backoffice . '/template/installer/module.phtml');
$controllerJs = file_get_contents($backoffice . '/js/controllers/installer.js');
$factoryJs = file_get_contents($backoffice . '/js/factories/installer.js');
$controllerPhp = file_get_contents($root . '/siberian/app/sae/modules/Installer/controllers/Backoffice/ModuleController.php');
$po = file_get_contents($root . '/siberian/languages/base/c_backoffice.po');

/**
 * @param string $source
 * @return string[] the texts asked with p__('backoffice', '...')
 */
$backofficeTexts = function ($source) {
    preg_match_all("/p__\('backoffice', '((?:[^'\\\\]|\\\\.)*)'\)/", $source, $m);
    return array_map('stripslashes', $m[1]);
};

return [
    'a file can be chosen or dropped in one single area' => function () use ($template) {
        t_equal(1, substr_count($template, 'type="file"'), 'one file input');
        t_equal(1, substr_count($template, 'nv-file-drop'), 'one drop zone');
        t_true(strpos($template, 'Drop your module here') === false, 'the separate drop block is gone');
        t_true(strpos($template, 'Click here to send your module') === false, 'the separate picker block is gone');
    },

    'the area is a keyboard reachable button that opens the picker' => function () use ($template) {
        t_true(strpos($template, 'role="button"') !== false, 'role');
        t_true(strpos($template, 'tabindex="0"') !== false, 'focusable');
        t_true(strpos($template, 'ng-click="openFilePicker()"') !== false, 'click');
        t_true(strpos($template, 'ng-keydown="zoneKeydown($event)"') !== false, 'Enter and Space');
        t_true(strpos($template, 'aria-live="polite"') !== false, 'state announced');
    },

    'the area shows the file name and a loader while sending' => function () use ($template) {
        t_true(strpos($template, '{{ zone.file }}') !== false, 'file name');
        t_true(strpos($template, "zoneState() === 'sending'") !== false, 'sending state');
        t_true(strpos($template, "zoneState() === 'checking'") !== false, 'licence check state');
    },

    'the file is sent as soon as it is chosen' => function () use ($controllerJs) {
        t_true(preg_match('/new FileUploader\(\{[^}]*autoUpload:\s*true/s', $controllerJs) === 1, 'autoUpload');
    },

    'check for updates is a primary call to action with a label, an icon and a loader' => function () use ($template) {
        t_true(preg_match('/<button[^>]*id="check_for_updates"[^>]*btn btn-primary btn-lg/s', $template) === 1, 'primary large button');
        t_true(strpos($template, 'ng-click="downloadUpdate()"') !== false, 'same action as before');
        t_true(strpos($template, '<?php echo __("Check for updates"); ?>') !== false, 'existing, translated label');
        t_true(strpos($template, "'fa-spin': check_for_updates.loader_is_visible") !== false, 'spinner');
        t_true(strpos($template, 'check_for_updates.result') !== false, 'result next to the button');
    },

    'check for updates cannot be sent twice and always ends with a message' => function () use ($controllerJs) {
        t_true(preg_match('/downloadUpdate = function \(\) \{\s*if \(\$scope\.check_for_updates\.loader_is_visible\) \{\s*return;/', $controllerJs) === 1, 'no double request');
        t_true(strpos($controllerJs, "result = 'up_to_date'") !== false, 'up to date');
        t_true(strpos($controllerJs, "result = 'downloaded'") !== false, 'downloaded');
        t_true(strpos($controllerJs, "result = 'error'") !== false, 'error');
    },

    'silent failures now say something' => function () use ($controllerJs) {
        t_true(strpos($controllerJs, '.setText()') === false, 'no empty message (the copy step used to show one)');
        t_true(strpos($controllerJs, 'function (manifestData)') !== false, 'manifestData is a parameter, not an undefined variable');
        t_true(strpos($controllerJs, '$scope.words.licenseNeeded') !== false, 'cancelling the licence prompt is explained');
        t_true(preg_match('/onErrorItem = function[^}]*angular\.isObject\(response\)/s', $controllerJs) === 1, 'a non JSON upload error does not throw');
    },

    'module updates show a spinner while they work' => function () use ($template) {
        t_true(strpos($template, "'fa-spin': module_updates.loading") !== false, 'Check now');
        t_true(strpos($template, "module_updates.busy_code === update.code") !== false, 'Update row');
        t_true(strpos($template, 'ng-show="module_updates.loading"') !== false, 'list loading row');
    },

    'every new text of the page is in the backoffice catalog' => function () use ($backofficeTexts, $template, $controllerPhp, $po) {
        // The texts this page asked for before were already in a catalog, the new ones are listed here.
        $new = [
            'Send your module',
            'Click to choose your module, or drop it here',
            'Sending your module, please do not close the page.',
            'Checking the module licence...',
            'Module sent, review the package details below.',
            'A licence key is needed to install this module. Click to choose another file.',
            'The module could not be sent. Click to choose another file.',
            'Siberian updates',
            'Checking for updates...',
            'Update downloaded, review the package details below.',
            'Downloading the update. Please, do not refresh the page.',
            'Checking for module updates...',
            'A licence key is required to install this module, the installation was not started.',
        ];
        $asked = array_merge($backofficeTexts($template), $backofficeTexts($controllerPhp));
        foreach ($new as $text) {
            t_true(in_array($text, $asked, true), 'the page asks for: ' . $text);
            t_true(strpos($po, 'msgid "' . $text . '"') !== false, 'missing in c_backoffice.po: ' . $text);
        }
    },

    'only the words of the page changed on the PHP side' => function () use ($controllerPhp) {
        t_true(strpos($controllerPhp, "'licenseNeeded' => p__('backoffice'") !== false, 'new word');
        // The actions the page relies on are all still there.
        foreach (['load', 'downloadupdate', 'upload', 'checkpermissions', 'saveftp', 'copy', 'install', 'checkModuleLicense', 'setModuleLicense', 'checkupdates', 'terms', 'acceptterms', 'updatemodule'] as $action) {
            t_true(strpos($controllerPhp, 'function ' . $action . 'Action()') !== false, 'action ' . $action);
        }
    },

    'every URL of the page factory has a controller action (one is known to be dead)' => function () use ($factoryJs, $controllerPhp, $template) {
        preg_match_all('#Url\.get\("installer/backoffice_module/([a-z-]+)"#', $factoryJs, $m);
        $dead = [];
        foreach (array_unique($m[1]) as $url) {
            $action = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $url)))) . 'Action(';
            if (stripos($controllerPhp, 'function ' . $action) === false) {
                $dead[] = $url;
            }
        }
        // FRAGILE-OK: Installer.checkForUpdates() calls an action that never existed, the page uses downloadUpdate()
        // (the check and the download are one request). If the action is added, or the call removed, update this list.
        t_equal(['checkforupdates'], $dead, 'URLs without an action');
        t_true(strpos($template, 'checkForUpdates()') === false, 'the template never calls the dead function');
    },
];
