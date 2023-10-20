<?php

/**
 * Class System_Backoffice_Config_DesignController
 */
class System_Backoffice_Config_DesignController extends System_Controller_Backoffice_Default
{

    protected $_codes = [
        "editor_design",
        "favicon",
        "favicon_backoffice",
        "logo",
        "logo_backoffice",
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
        try {

            $request = $this->getRequest();
            $code = $request->getPost("code", null);

            if (__getConfig('is_demo')) {
                // Demo version
                throw new Exception("This is a demo version, both the favicon and the logo can't be changed.");
            }

            if (empty($code)) {
                throw new Exception("Unknown file to upload.");
            }

            if (empty($_FILES) || empty($_FILES['file']['name'])) {
                throw new Exception("No file has been sent");
            }

            $path = rpath(System_Model_Config::IMAGE_PATH);
            $base_path = path(System_Model_Config::IMAGE_PATH);

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

                $payload = [
                    "success" => true,
                    "message" => __($message)
                ];

            } else {
                $messages = $adapter->getMessages();
                if (!empty($messages)) {
                    $message = implode_polyfill("\n", $messages);
                } else {
                    $message = __("An error occurred during the process. Please try again later.");
                }

                throw new Exception($message);
            }
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }


        $this->_sendJson($payload);
    }

    protected function _findconfig()
    {
        $data = parent::_findconfig();
        $data["designs"] = Core_Model_Directory::getDesignsFor("desktop");
        return $data;
    }

}
