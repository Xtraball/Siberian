<?php

class Application_Customization_Publication_AppController extends Application_Controller_Default {

    /**
     * @var array
     */
    public $cache_triggers = array(
        "iconspost" => array(
            "tags" => array("app_#APP_ID#"),
        ),
        "saveicon" => array(
            "tags" => array("app_#APP_ID#"),
        ),
    );

    public function indexAction() {
        $this->loadPartials();

        if ($this->getRequest()->isXmlHttpRequest()) {
            $html = array('html' => $this->getLayout()->getPartial('content_editor')->toHtml());
            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function iconspostAction() {
        $values = $this->getRequest()->getPost();

        $form = new Application_Form_Customization_Publication_App();
        if($form->isValid($values)) {

            $application = $this->getApplication();

            # App icon
            if(isset($values["icon"]) && !file_exists(Core_Model_Directory::getBasePathTo("images/application".$values["icon"]))) {
                $path_icon = Siberian_Feature::moveUploadedIcon($application->getId(), Core_Model_Directory::getTmpDirectory() . "/" . $values['icon']);
                $application->setData("icon", $path_icon);
            }

            # Android push icon
            if(isset($values["android_push_icon"]) && !file_exists(Core_Model_Directory::getBasePathTo("images/application".$values["android_push_icon"]))) {
                $path_android_push_icon = Siberian_Feature::moveUploadedIcon($application->getId(), Core_Model_Directory::getTmpDirectory() . "/" . $values['android_push_icon']);
                Core_Model_Lib_Image::sColorize(Core_Model_Directory::getBasePathTo("images/application".$path_android_push_icon), "FFFFFF");
                $application->setData("android_push_icon", $path_android_push_icon);
            }

            # Android push image
            if(isset($values["android_push_image"]) && $values["android_push_image"] == "_delete_") {
                $path = Core_Model_Directory::getBasePathTo("images/application".$application->getData("android_push_image"));
                if(file_exists($path)) {
                    unlink($path);
                }
                $application->setData("android_push_image", null);

            } else if(isset($values["android_push_image"]) && !file_exists(Core_Model_Directory::getBasePathTo("images/application".$values["android_push_image"]))) {
                $path_android_push_image = Siberian_Feature::moveUploadedIcon($application->getId(), Core_Model_Directory::getTmpDirectory() . "/" . $values['android_push_image']);
                $application->setData("android_push_image", $path_android_push_image);
            }

            # Android push color
            $icon_color = strtolower($values["android_push_color"]);
            if(!preg_match("/^#[a-f0-9]{6}$/", $icon_color)) {
                $icon_color = "#0099c7";
            }
            $application->setData("android_push_color", $icon_color);

            $application->save();

            $data = array(
                "success" => 1,
                "message" => __("Success."),
            );
        } else {
            /** Do whatever you need when form is not valid */
            $data = array(
                "error" => 1,
                "message" => $form->getTextErrors(),
                "errors" => $form->getTextErrors(true),
            );
        }

        $this->_sendJson($data);
    }

    public function iconAction() {
        $this->getLayout()->setBaseRender('content', 'application/customization/publication/app/icon.phtml', 'admin_view_default');
        $html = array('html' => $this->getLayout()->render());
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    public function startupAction() {
        $this->getLayout()->setBaseRender('content', 'application/customization/publication/app/startup.phtml', 'admin_view_default');
        $html = array('html' => $this->getLayout()->render());
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    public function saveiconAction() {
        if ($datas = $this->getRequest()->getPost()) {
            $html = '';
            try {
                if(!empty($datas['file'])) {

                    $icon_relative_path = '/'.$this->getApplication()->getId().'/icon/';
                    $folder = Application_Model_Application::getBaseImagePath().$icon_relative_path;
                    $datas['dest_folder'] = $folder;
                    $datas['new_name'] = $datas['file'];

                    $uploader = new Core_Model_Lib_Uploader();
                    $file = $uploader->savecrop($datas);

                    $format = pathinfo($file, PATHINFO_EXTENSION);

                    //Icon must be forced to png
                    if($format != "png") {
                        switch ($format) {
                            case 'jpg':
                            case 'jpeg':
                                $image = imagecreatefromjpeg($folder . $file);
                                break;
                            case 'gif':
                                $image = imagecreatefromgif($folder . $file);
                                break;
                        }
                        $new_name = uniqid().".png";
                        imagepng($image, $folder.$new_name);
                        $this->getApplication()->setIcon($icon_relative_path.$new_name)->save();
                    } else {
                        $this->getApplication()->setIcon($icon_relative_path.$file)->save();
                    }

                    $html = array(
                        'success' => 1,
                        'file' => $this->getApplication()->getIcon(128)
                    );
                }
                else {
                    $this->getApplication()->setIcon(null)->save();
                }
            } catch (Exception $e) {
                $html = array(
                    'message' => $e->getMessage()
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function savestartupAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {
                $application = $this->getApplication();
                $relative_path = '/'.$application->getId().'/startup_image/';
                $filetype = $this->getRequest()->getParam('filetype');
                $folder = Application_Model_Application::getBaseImagePath().$relative_path;
                $datas['dest_folder'] = $folder;
                $datas['new_name'] = $datas['file'];

                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->savecrop($datas);
                $url = "";

                $format = pathinfo($file, PATHINFO_EXTENSION);

                //Startup images must be forced to png
                if($format != "png") {
                    switch ($format) {
                        case 'jpg':
                        case 'jpeg':
                            $image = imagecreatefromjpeg($folder . $file);
                            break;
                        case 'gif':
                            $image = imagecreatefromgif($folder . $file);
                            break;
                    }
                    $new_name = uniqid().".png";
                    imagepng($image, $folder.$new_name);
                    $file = $new_name;
                }

                if($filetype == "standard") $application->setData("startup_image", $relative_path.$file);
                else $application->setData("startup_image_".$filetype, $relative_path.$file);

                $application->save();

                $datas = array(
                    'success' => 1,
                    'file' => $application->getStartupImageUrl($filetype)
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

    protected function _createIcon($datas) {

        // Créé l'icône
        $image = imagecreatetruecolor(256, 256);

        // Rempli la couleur de fond
        $rgb = $this->_hex2RGB(000000);
        $background_color = imagecolorallocate($image, $rgb['red'], $rgb['green'], $rgb['blue']);
        imagefill($image, 0, 0, $background_color);
        $targ_w = $targ_h = 256;
        if(!empty($datas['icon']['file'])) {
            //Applique l'image
            $logo_relative_path = '/logo/';
            $folder = Application_Model_Application::getBaseImagePath().$logo_relative_path;
            if (!is_dir($folder))
                mkdir($folder, 0777, true);

            $src = Core_Model_Directory::getTmpDirectory(true).'/'.$datas['icon']['file'];
            $source = imagecreatefromstring(file_get_contents($src));
        }
        $dest = ImageCreateTrueColor($targ_w, $targ_h);
        imagecopyresampled($dest,$image,0,0,0,0,$targ_w,$targ_h,$targ_w,$targ_h);
        if($datas['icon']['file'] != '') {
            imagecopyresampled($dest,$source,0,0,$datas['icon']['x1'],$datas['icon']['y1'],$targ_w,$targ_h,$datas['icon']['w'],$datas['icon']['h']);
        }

        return $dest;
    }

    protected function _hex2RGB($hexStr, $returnAsString = false, $seperator = ',') {

        $hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr);
        $rgbArray = array();

        if (strlen($hexStr) == 6) {
            $colorVal = hexdec($hexStr);
            $rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
            $rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
            $rgbArray['blue'] = 0xFF & $colorVal;
        } elseif (strlen($hexStr) == 3) {
            $rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
            $rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
            $rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
        } else {
            return false;
        }

        return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray;
    }

}
