<?php

/**
 * Class Siberian_Controller_Dispatcher_Standard
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.18.17
 */
class Siberian_Controller_Dispatcher_Standard extends Zend_Controller_Dispatcher_Standard
{

    /**
     * @var array
     */
    protected $_moduleDirectories = [];

    /**
     * @param string $path
     * @param null $module
     * @return $this|Zend_Controller_Dispatcher_Standard
     * @throws Zend_Loader_Exception
     */
    public function addControllerDirectory($path, $module = null)
    {
        if (null === $module) {
            $module = $this->_defaultModule;
        }

        $module = (string)$module;
        $path = rtrim((string)$path, '/\\');

        $autoloader = Zend_Loader_Autoloader::getInstance();
        $exists = $autoloader->getNamespaceAutoloaders($module);
        if (empty($exists)) {
            $autoloader->registerNamespace($module);
        }

        $this->_moduleDirectories[] = $module;
        $this->_controllerDirectory[$module] = $path;
        $this->_controllerDirectory[strtolower($module)] = $path;

        return $this;
    }

    /**
     * @return array
     */
    public function getModuleDirectories(): array
    {
        return $this->_moduleDirectories;
    }

    /**
     * @return array
     */
    public function getSortedModuleDirectories(): array
    {
        $dirs = $this->_moduleDirectories;
        $dirs = array_unique($dirs);
        sort($dirs);
        unset(
            $dirs[array_search('Core', $dirs, false)],
            $dirs[array_search('Application', $dirs, false)],
            $dirs[array_search('Media', $dirs, false)],
            $dirs[array_search('Acl', $dirs, false)]
        );
        $dirs = array_reverse($dirs);
        $dirs[] = 'Media';
        $dirs[] = 'Application';
        $dirs[] = 'Core';
        $dirs = array_reverse($dirs);
        $dirs[] = 'Acl';

        return array_values($dirs);
    }


    /**
     * Returns TRUE if the Zend_Controller_Request_Abstract object can be
     * dispatched to a controller.
     *
     * Use this method wisely. By default, the dispatcher will fall back to the
     * default controller (either in the module specified or the global default)
     * if a given controller does not exist. This method returning false does
     * not necessarily indicate the dispatcher will not still dispatch the call.
     *
     * @param Zend_Controller_Request_Abstract $action
     * @return boolean
     */
    public function isDispatchable(Zend_Controller_Request_Abstract $request): bool
    {
        $className = $this->getControllerClass($request);
        if (!$className) {
            return false;
        }

        $finalClass = $className;
        if (($this->_defaultModule != $this->_curModule)
            || $this->getParam('prefixDefaultModule')) {
            $finalClass = $this->formatClassName($this->_curModule, $className);
        }
        if (class_exists($finalClass, false)) {
            return true;
        }


        $fileSpec = $this->classToFilename($className);
        $dispatchDir = $this->getDispatchDirectory();
        $test = $dispatchDir . DIRECTORY_SEPARATOR . $fileSpec;

        /** @migration inheritance Search for class in another edition */
        return self::isReadableInherit($test);
    }

    /**
     * @param string $className
     * @return string
     * @throws Zend_Controller_Dispatcher_Exception
     */
    public function loadClass($className)
    {
        $finalClass = $className;
        if (($this->_defaultModule != $this->_curModule)
            || $this->getParam('prefixDefaultModule')) {
            $finalClass = $this->formatClassName($this->_curModule, $className);
        }
        if (class_exists($finalClass, false)) {
            return $finalClass;
        }

        $dispatchDir = $this->getDispatchDirectory();
        $loadFile = $dispatchDir . DIRECTORY_SEPARATOR . $this->classToFilename($className);

        /** @migration inheritance Search for class in another edition */
        if (self::isReadableInherit($loadFile)) {
            $classPath = self::isReadableInherit($loadFile, true);
            include_once $classPath;
        } else {
            require_once 'Zend/Controller/Dispatcher/Exception.php';
            throw new Zend_Controller_Dispatcher_Exception('Siberian:: Cannot load controller class "' . $className . '" from file "' . $loadFile . "'");
        }

        if (!class_exists($finalClass, false)) {
            require_once 'Zend/Controller/Dispatcher/Exception.php';
            throw new Zend_Controller_Dispatcher_Exception('Siberian:: Invalid controller class ("' . $finalClass . '")');
        }

        return $finalClass;
    }

    /**
     * @param $path
     * @param bool $return_path
     * @return bool|mixed
     */
    public static function isReadableInherit($path, $return_path = false)
    {
        if (Zend_Loader::isReadable($path)) {
            return $return_path ? $path : true;
        }

        $type = strtolower(Siberian_Version::TYPE);
        $editions = array_reverse(Siberian_Cache::$editions[$type]);
        foreach ($editions as $edition) {
            // Trying local inheritance!
            $tmpPath = preg_replace("#app/\w+/modules#i", "app/{$edition}/modules", $path);
            $disableFlag = preg_replace('#/controllers/.*#i', '/module.disabled', $tmpPath);

            // Skip disabled modules from app/local!
            if (stripos($disableFlag, '/app/local/modules/') !== false &&
                is_readable($disableFlag)) {
                continue;
            }

            if (Zend_Loader::isReadable($tmpPath)) {
                return $return_path ? $tmpPath : true;
            }
        }

        # Otherwise return boolean
        return Zend_Loader::isReadable($path);
    }
}
