<?php

/**
 * Class System_Backoffice_Config_DesignController
 */
class System_Backoffice_Config_DesignController extends System_Controller_Backoffice_Default
{

    protected $_codes = [
        "editor_design",
        "favicon",
        "logo",
        "backoffice_theme",
    ];

    public function loadAction()
    {
        $html = [
            "title" => sprintf('%s > %s',
                __('Appearance'),
                __('General')),
            "icon" => "fa-pencil",
        ];

        // Hides new themes message!
        __set('display_backoffice_theme', true);

        $this->_sendJson($html);
    }

    public function uploadAction()
    {

        if ($code = $this->getRequest()->getPost("code")) {

            try {

                if (__getConfig('is_demo')) {
                    // Demo version
                    throw new Exception("This is a demo version, both the favicon and the logo can't be changed.");
                }

                if (empty($_FILES) || empty($_FILES['file']['name'])) {
                    throw new Exception("No file has been sent");
                }

                $path = Core_Model_Directory::getPathTo(System_Model_Config::IMAGE_PATH);
                $base_path = Core_Model_Directory::getBasePathTo(System_Model_Config::IMAGE_PATH);

                if (!is_dir($base_path)) {
                    mkdir($base_path, 0777, true);
                }

                $adapter = new Zend_File_Transfer_Adapter_Http();

                $adapter->setDestination($base_path);

                if ($adapter->receive()) {

                    $file = $adapter->getFileInfo();

                    $config = new System_Model_Config();
                    $config->find($code, "code");
                    $config->setValue($path . DS . $file['file']['name'])->save();

                    $message = sprintf("Your %s has been successfully saved", $code);
                    $this->_sendHtml([
                        "success" => 1,
                        "message" => __($message)
                    ]);

                } else {
                    $messages = $adapter->getMessages();
                    if (!empty($messages)) {
                        $message = implode("\n", $messages);
                    } else {
                        $message = __("An error occurred during the process. Please try again later.");
                    }

                    throw new Exception($message);
                }
            } catch (Exception $e) {
                $data = [
                    "error" => 1,
                    "message" => $e->getMessage()
                ];
            }

        }

    }

    protected function _findconfig()
    {
        $data = parent::_findconfig();
        $data["designs"] = Core_Model_Directory::getDesignsFor("desktop");
        return $data;
    }

}
