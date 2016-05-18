<?php

class Installer_Installation_ModuleController extends Installer_Controller_Installation_Default {

    public function installAction() {

        if($module = $this->getRequest()->getParam('name')) {

            foreach($dirs as $module) {
                $installer = new Installer_Model_Installer();
                $installer->setModuleName($module)
                    ->install()
                ;
            }
            die('done');
        }
        die('pas ok');

    }

}