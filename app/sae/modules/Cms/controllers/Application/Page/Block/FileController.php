<?php

class Cms_Application_Page_Block_FileController extends Application_Controller_Default {

    public function uploadAction() {

        if (!empty($_FILES)) {

            try {

                $folder = Core_Model_Directory::getTmpDirectory(true).'/';
                $relative_folder = Core_Model_Directory::getPathTo(Core_Model_Directory::getTmpDirectory(false)).'/';

                $params = array();
                $params['validators'] = array(
                    'Extension' => array('jpg', 'png', 'jpeg', 'gif', 'pdf', 'case' => false)
                );
                $params['destination_folder'] = $folder;
                $params['uniq'] = 1;
                $params['desired_name'] = $_FILES["file"]["name"];

                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->upload($params);

                $datas = array(
                    'success' => 1,
                    'path_to_tmp_file' => $relative_folder.$file,
                    'file' => $file
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
