<?php

use Siberian\Exception;
use Siberian\File;

/**
 * Class Application_Model_Device_Abstract
 */
abstract class Application_Model_Device_Abstract extends Core_Model_Default
{
    /**
     * @var string
     */
    public $_os_name = "__unset__";

    /**
     * @var
     */
    public $_logger;

    /**
     * @return string
     */
    public function getOsName()
    {
        return $this->_os_name;
    }

    /**
     * @param $device
     * @param null $version
     * @param null $buildNumber
     * @return int
     * @throws Exception
     */
    public static function validatedVersion($device, $version = null, $buildNumber = null): int
    {
        $versionName = $version ?? $device->getVersion();
        $buildNumber = (int)($buildNumber ?? $device->getBuildNumber());
        $parts = explode('.', $versionName);

        if (count($parts) !== 2) {
            throw new Exception('[Android version] The version number must contain two parts, like 1.0, 2.5, 12.0, etc...');
        }

        if ($buildNumber <= 0) {
            $buildNumber = 1;
        }

        $major = (int)$parts[0];
        $minor = (int)$parts[1];

        if ($major > 1000 || $major < 1) {
            throw new Exception('[Android version] The first part must be between 1 and 1000');
        }
        if ($minor > 999 || $minor < 0) {
            throw new Exception('[Android version] The second part must be between 1 and 999');
        }
        if ($buildNumber > 999 || $buildNumber <= 0) {
            throw new Exception('[Android version] The build number part must be between 1 and 999');
        }

        $completeVersion = $major .
            str_pad($minor, 3, '0', STR_PAD_LEFT) .
            str_pad($buildNumber, 3, '0', STR_PAD_LEFT);

        // 2100 000 000
        if ((int)$completeVersion > 2100000000) {
            throw new Exception('[Android version] The versionCode must not exceed 2100000000');
        }

        return $completeVersion;
    }

    /**
     * @param $versionCode
     * @return string
     */
    public static function formatVersionCode($versionCode): string
    {
        return implode_polyfill(' ',
            str_split(
                str_pad($versionCode, 12, ' ', STR_PAD_LEFT),
                3)
        );
    }

    /**
     * @param $version
     * @return $this
     * @throws Exception
     */
    public function setVersion($version): self
    {
        $this->setData('version', $version);
        self::validatedVersion($this);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResources()
    {
        $umask = umask(0);

        $resource = $this->prepareResources();
        umask($umask);

        return $resource;
    }

    /**
     * @param $replacements
     * @param $file
     * @param bool $regex
     * @throws \Siberian\Exception
     */
    public function __replace($replacements, $file, $regex = false)
    {
        $contents = file_get_contents($file);
        if (!$contents) {
            throw new \Siberian\Exception(__('An error occurred while editing file (%s).', $file));
        }

        foreach ($replacements as $search => $replace) {
            if ($regex) {
                $contents = preg_replace($search, $replace, $contents);
            } else {
                $contents = str_replace($search, $replace, $contents);
            }

        }
        File::putContents($file, $contents);
    }

    /**
     * Archive the generated project.
     *
     * @return string
     * @throws Exception
     */
    protected function zipFolder()
    {

        $folder = $this->_dest_source;
        if (!isset($this->_dest_archive)) {
            $dest = "{$this->_dest_source}/{$this->_zipname}.zip";
        } else {
            $dest = "{$this->_dest_archive}/{$this->_zipname}.zip";
        }

        Core_Model_Directory::zip($folder, $dest);

        if (!file_exists($dest)) {
            throw new Exception("An error occurred during the creation of the archive ({$dest})");
        }

        return $dest;
    }
}
