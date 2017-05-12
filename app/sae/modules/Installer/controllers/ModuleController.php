<?php

class Installer_ModuleController extends Backoffice_Controller_Default {

    public function indexAction() {
        $this->loadPartials();
    }

    public function getfeatureAction() {
        if(APPLICATION_ENV == "development") {
            $module = $this->getRequest()->getParam("mod");
            $feature = $this->getRequest()->getParam("feat");

            $module_obj = new Installer_Model_Installer_Module();
            $module_obj->prepare($module);
            $feature_json = $module_obj->getFeature($feature);

            if($feature_json) {
                http_response_code(200);
                header("Content-Type: text/javascript");
                die(Siberian_Assets::compileFeature($feature_json));
            }
            http_response_code(404);
            die("Not found");
        }
    }

    public function installAction() {

        $module_names = Zend_Controller_Front::getInstance()->getDispatcher()->getSortedModuleDirectories();
        $modules = array();
        foreach($module_names as $module_name) {
            $module = new Installer_Model_Installer_Module();
            $module->prepare($module_name);
            if($module->canUpdate()) {
                $modules[] = $module->getName();
            }
        }

        $this->loadPartials();
        $this->getLayout()->getPartial('content')->setModules($modules);
    }

    public function installpostAction() {

        try {
            $html = array('success' => 1);
            if($module = $this->getRequest()->getParam('name')) {

                $installer = new Installer_Model_Installer();
                $installer->setModuleName($module)
                    ->install()
                ;

                $html = array('success' => 1);

            } else {
                throw new Exception($this->_("No directory provided"));
            }

        } catch(Exception $e) {
            $html = array(
                'error' => 1,
                'message' => $e->getMessage()
            );
        }

        $this->_sendHtml($html);
    }

    public function uploadAction() {

        try {
            if(empty($_FILES) || empty($_FILES['module']['name'])) {
                throw new Exception("No file has been sent");
            }

            $adapter = new Zend_File_Transfer_Adapter_Http();
            $adapter->setDestination(Core_Model_Directory::getTmpDirectory(true));

            if($adapter->receive()) {
                $file = $adapter->getFileInfo();

                $parser = new Installer_Model_Installer_Module_Parser();
                if($parser->setFile($file['module']['tmp_name'])->check()) {
                    $infos = pathinfo($file['module']['tmp_name']);
                    $filename = $infos['filename'];
                    $this->_redirect('installer/module/install', array('module_name' => $filename));
                } else {
                    $messages = $parser->getErrors();
                    $message = implode("\n", $messages);
                    throw new Exception($this->_($message));
                }

            } else {
                $messages = $adapter->getMessages();
                if(!empty($messages)) {
                    $message = implode("\n", $messages);
                } else {
                    $message = $this->_("An error occurred during the process. Please try again later.");
                }

                throw new Exception($message);
            }
        } catch(Exception $e) {
            $this->getSession()->addError($e->getMessage());
            $this->_redirect('installer/module');
        }

    }

}