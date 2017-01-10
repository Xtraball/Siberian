<?php

class System_Backoffice_Config_DesignController extends System_Controller_Backoffice_Default {

    protected $_codes = array(
        "editor_design",
        "favicon",
        "logo"
    );

    public function loadAction() {

        $html = array(
            "title" => __("Appearance"),
            "icon" => "fa-pencil",
            "message" => array(
                "flat" => __("<b>File types:</b> %s<br /><b>Recommended size:</b> %s", "png, jpg, jpeg, gif", "345x85px"),
                "siberian" => __("<b>File types:</b> %s<br /><b>Recommended size:</b> %s", "png, jpg, jpeg, gif", "150x50px")
            )
        );

        $this->_sendHtml($html);

    }

    public function uploadAction() {

        if($code = $this->getRequest()->getPost("code")) {

            try {

                if(empty($_FILES) || empty($_FILES['file']['name'])) {
                    throw new Exception("No file has been sent");
                }

                $path = Core_Model_Directory::getPathTo(System_Model_Config::IMAGE_PATH);
                $base_path = Core_Model_Directory::getBasePathTo(System_Model_Config::IMAGE_PATH);

                if(!is_dir($base_path)) {
                    mkdir($base_path, 0777, true);
                }

                $adapter = new Zend_File_Transfer_Adapter_Http();

                $adapter->setDestination($base_path);

                if($adapter->receive()) {

                    $file = $adapter->getFileInfo();

                    $config = new System_Model_Config();
                    $config->find($code, "code");
                    $config->setValue($path.DS.$file['file']['name'])->save();

                    $message = sprintf("Your %s has been successfully saved", $code);
                    $this->_sendHtml(array(
                        "success" => 1,
                        "message" => __($message)
                    ));

                } else {
                    $messages = $adapter->getMessages();
                    if(!empty($messages)) {
                        $message = implode("\n", $messages);
                    } else {
                        $message = __("An error occurred during the process. Please try again later.");
                    }

                    throw new Exception($message);
                }
            } catch(Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }

        }

    }

    protected function _findconfig() {
        $data = parent::_findconfig();
        $data["designs"] = Core_Model_Directory::getDesignsFor("desktop");
        return $data;
    }

}
