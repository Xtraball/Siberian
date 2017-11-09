<?php

class Application_Customization_Features_Edit_LogoController extends Application_Controller_Default {

    /**
     * @var array
     */
    public $cache_triggers = array(
        "save" => array(
            "tags" => array("app_#APP_ID#"),
        ),
    );

    public function saveAction() {
        if($datas = $this->getRequest()->getPost()) {
            try {
                $logo_relative_path = '/logo/';
                $folder = Application_Model_Application::getBaseImagePath().$logo_relative_path;

                $datas['dest_folder'] = $folder;
                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->savecrop($datas);

                $this->_application->setLogo($logo_relative_path.$file);
                $this->_application->save();

                $datas = array(
                    'success' => 1,
                    'file' => Application_Model_Application::getImagePath().$logo_relative_path.$file
                );
            } catch (Exception $e) {
                $datas = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }
            $this->getLayout()->setHtml(Zend_Json::encode($datas));
        }
    }
}
