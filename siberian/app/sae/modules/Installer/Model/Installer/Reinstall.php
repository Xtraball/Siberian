<?php

/**
 * Class Installer_Model_Installer_Reinstall
 *
 * Decides whether a module may be installed again on the very same version.
 * Opt-in only: the regular flow still installs when the package is newer.
 */
class Installer_Model_Installer_Reinstall
{
    /**
     * @param bool $isInstalled
     * @param string|null $installedVersion
     * @param string|null $packageVersion
     * @return bool
     */
    public static function isAllowed($isInstalled, $installedVersion, $packageVersion): bool
    {
        if (!$isInstalled || empty($installedVersion) || empty($packageVersion)) {
            return false;
        }

        return version_compare((string) $packageVersion, (string) $installedVersion, '==');
    }
}
