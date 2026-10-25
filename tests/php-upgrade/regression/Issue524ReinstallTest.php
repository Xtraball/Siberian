<?php
/**
 * #524: reinstalling a module on the very same version is an opt-in. Without the `reinstall` parameter the install
 * flow is exactly what it was: only modules whose package.json is newer than the database are installed.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
$installer = $siberian . '/app/sae/modules/Installer';
$controllerFile = $installer . '/controllers/Backoffice/ModuleController.php';
$moduleFile = $installer . '/Model/Installer/Module.php';
$helperFile = $installer . '/Model/Installer/Reinstall.php';
$factoryFile = $siberian . '/app/sae/design/desktop/backoffice/js/factories/installer.js';
$uiFile = $siberian . '/app/sae/design/desktop/backoffice/js/controllers/installer.js';

if (!class_exists('Installer_Model_Installer_Reinstall', false)) {
    require_once $helperFile;
}

return [
    '524 only the same version of an installed module can be reinstalled' => function () {
        $allowed = ['Installer_Model_Installer_Reinstall', 'isAllowed'];
        t_true($allowed(true, '1.2.3', '1.2.3'), 'same version');
        t_true($allowed(true, '1.2.3', '1.2.3.0'), 'same version, other notation');
        t_true(!$allowed(true, '1.2.3', '1.2.4'), 'newer package goes through the regular update');
        t_true(!$allowed(true, '1.2.3', '1.2.2'), 'older package is never a reinstall');
        t_true(!$allowed(false, '0.0.1', '0.0.1'), 'a module that is not installed is a regular install');
        t_true(!$allowed(true, '', '1.0.0'), 'unknown installed version');
        t_true(!$allowed(true, '1.0.0', null), 'unknown package version');
    },

    '524 the install action reads reinstall and defaults to nothing' => function () use ($controllerFile) {
        $code = file_get_contents($controllerFile);
        t_true(strpos($code, "getParam('reinstall', '')") !== false, 'optional parameter, empty by default');
        t_true(strpos($code, '$reinstall !== \'\' &&') !== false, 'guarded by the parameter');
        t_true(strpos($code, 'Installer_Model_Installer_Reinstall::isAllowed(') !== false, 'goes through the helper');
        t_true(strpos($code, "if (\$module->canUpdate()) {\n                    self::\$MODULES[] = \$module->getName();\n                } else if") !== false,
            'the regular canUpdate() branch is kept first');
    },

    '524 the force flag is off by default and only touches canUpdate' => function () use ($moduleFile) {
        $code = file_get_contents($moduleFile);
        t_true(strpos($code, 'protected $_forceReinstall = false;') !== false, 'off by default');
        t_true(strpos($code, "return \$this->_forceReinstall ||\n            version_compare(\$this->_lastVersion, \$this->getVersion(), '>');") !== false,
            'canUpdate keeps the version comparison');
        t_equal(1, substr_count($code, '_forceReinstall = $force'), 'only forceReinstall() sets it');
    },

    '524 package details tell the UI about it, without breaking the upload' => function () use ($controllerFile) {
        $code = file_get_contents($controllerFile);
        t_true(strpos($code, "['can_reinstall']") !== false, 'can_reinstall is returned');
        t_true(strpos($code, "['installed_version']") !== false, 'installed_version is returned');
        t_true((bool) preg_match('/try \{\s*\$installed = .*?catch \(\\\\Exception \$e\)/s', $code), 'best effort, wrapped');
    },

    '524 the UI sends the parameter only after a confirmation' => function () use ($factoryFile, $uiFile) {
        $factory = file_get_contents($factoryFile);
        t_true(strpos($factory, 'factory.reinstall = null;') !== false, 'null by default');
        t_true(strpos($factory, 'if (factory.reinstall) {') !== false, 'sent only when set');
        $ui = file_get_contents($uiFile);
        t_true(strpos($ui, 'if (!$scope.package_details.can_reinstall) {') !== false, 'no prompt for regular installs');
        t_true(strpos($ui, 'Installer.reinstall = $scope.package_details._name;') !== false, 'set on confirm only');
        t_equal(1, substr_count($ui, 'Installer.reinstall = $scope.package_details._name;'), 'nowhere else');
    },
];
