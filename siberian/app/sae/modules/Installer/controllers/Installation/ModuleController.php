<?php

/**
 * Class Installer_Installation_ModuleController
 */
class Installer_Installation_ModuleController extends Installer_Controller_Installation_Default
{
    /**
     * @throws Zend_Db_Profiler_Exception
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Exception
     */
    public function installAction()
    {
        if ($module = $this->getRequest()->getParam('name')) {
            $installer = new Installer_Model_Installer();
            $installer->setModuleName($module)
                ->install()
                ->insertData();
        }

        // Clear opcache in between updates in case it's required!
        if (method_exists(Siberian\Cache::class, 'clearOpCache')) {
            \Siberian_Cache::clearOpCache();
        }
    }

}