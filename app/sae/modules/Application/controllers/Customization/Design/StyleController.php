<?php

class Application_Customization_Design_StyleController extends Application_Controller_Default {

    public function editAction() {
        $this->loadPartials();
        if($this->getRequest()->isXmlHttpRequest()) {
            $html = array('html' => $this->getLayout()->getPartial('content_editor')->toHtml());
            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function formoptionsAction() {
        if($datas = $this->getRequest()->getPost()) {



            $html = array(
                "success" => 1,
                "message" => __("Options saved"),
            );

            $this->getLayout()->setHtml(Siberian_Json::encode($html));
        }
    }

    public function changelayoutAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {
                $html = array();

                if(empty($datas['layout_id'])) throw new Exception($this->_('An error occurred while changing your layout.'));

                $layout = new Application_Model_Layout_Homepage();
                $layout->find($datas['layout_id']);

                if(!$layout->getId()) throw new Exception($this->_('An error occurred while changing your layout.'));

                $html = array('success' => 1);

                if($layout->getId() != $this->getApplication()->getLayoutId()) {

                    $visibility = $layout->getVisibility();

                    switch($layout->getVisibility()) {
                        case Application_Model_Layout_Homepage::VISIBILITY_ALWAYS:
                        case Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE:
                            $visibility = Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE;
                            break;
                        case Application_Model_Layout_Homepage::VISIBILITY_TOGGLE:
                            $visibility = Application_Model_Layout_Homepage::VISIBILITY_TOGGLE;
                            break;
                    }

                    $this->getApplication()
                        ->setLayoutId($datas['layout_id'])
                        ->setLayoutVisibility($visibility)
                        ->save()
                    ;
                    $html['reload'] = 1;
                    $html["display_layout_options"] = $layout->getVisibility() == Application_Model_Layout_Homepage::VISIBILITY_ALWAYS;
                    $html["layout_id"] = $layout->getId();
                    $html["layout_visibility"] = $this->getApplication()->getLayoutVisibility();
                }

            }
            catch(Exception $e) {
//                $html = array('message' => 'Une erreur est survenue lors de la sauvegarde, merci de réessayer ultérieurement');
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1,
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));

        }

    }

    public function changelayoutvisibilityAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {
                if(empty($datas['layout_id'])) throw new Exception($this->_('An error occurred while changing your layout.'));

                $layout = new Application_Model_Layout_Homepage();
                $layout->find($datas['layout_id']);

                if(!$layout->getId()) throw new Exception($this->_('An error occurred while changing your layout.'));

                $html = array();

                if($layout->getId() == $this->getApplication()->getLayoutId()) {

                    $html["success"] = 1;

                    $visibility = $layout->getVisibility();

                    if($layout->getVisibility() == Application_Model_Layout_Homepage::VISIBILITY_ALWAYS) {
                        $visibility = !empty($datas["layout_is_visible_in_all_the_pages"]) ?
                            Application_Model_Layout_Homepage::VISIBILITY_ALWAYS :
                            Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE;
                    }

                    $this->getApplication()
                        ->setLayoutId($datas['layout_id'])
                        ->setLayoutVisibility($visibility)
                        ->save()
                    ;
                    $html['reload'] = 1;
                    $html["display_layout_options"] = $layout->getVisibility() == Application_Model_Layout_Homepage::VISIBILITY_ALWAYS;
                    $html["layout_id"] = $layout->getId();
                    $html["layout_visibility"] = $this->getApplication()->getLayoutVisibility();
                }

            }
            catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1,
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));

        }

    }

    public function changeiosstatusbarvisibilityAction() {

        try {
            $html = array();

            $is_hidden = $this->getRequest()->getPost('ios_status_bar_is_hidden') ? 1 : 0;

            $this->getApplication()
                ->setIosStatusBarIsHidden($is_hidden)
                ->save()
            ;

            $html["success"] = 1;
            $html['reload'] = 1;

        } catch(Exception $e) {
            $html = array(
                'message' => $this->_('An error occurred while hidding the iOS Status Bar.'),
                'message_button' => 1,
                'message_loader' => 1,
            );
        }

        $this->getLayout()->setHtml(Zend_Json::encode($html));

    }

    public function changeandroidstatusbarvisibilityAction() {

        try {
            $html = array();

            $is_hidden = $this->getRequest()->getPost('android_status_bar_is_hidden') ? 1 : 0;

            $this->getApplication()
                ->setAndroidStatusBarIsHidden($is_hidden)
                ->save()
            ;

            $html["success"] = 1;
            $html['reload'] = 1;

        } catch(Exception $e) {
            $html = array(
                'message' => $this->_('An error occurred while hidding the Android Status Bar.'),
                'message_button' => 1,
                'message_loader' => 1,
            );
        }

        $this->getLayout()->setHtml(Zend_Json::encode($html));

    }

    public function mutualizebackgroundimagesAction() {

        try {
            $this->getApplication()
                ->setUseHomepageBackgroundImageInSubpages((int) $this->getRequest()->getPost('use_homepage_background_image_in_subpages', 0))
                ->save()
            ;

            $html = array('success' => '1');

        }
        catch(Exception $e) {
            $html = array('message' => $e->getMessage());
        }

        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    public function savehomepageAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                $application = $this->getApplication();
                $filetype = $this->getRequest()->getParam('filetype');
                $relative_path = '/'.$this->getApplication()->getId().'/homepage_image/'.$filetype.'/';
                $folder = Application_Model_Application::getBaseImagePath().$relative_path;
                $datas['dest_folder'] = $folder;
                $datas['ext'] = 'jpg';
                
                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->savecrop($datas);
                $url = "";

                switch($filetype) {
                    case "standard":
                        $application->setBackgroundImage($relative_path.$file);
                        break;
                    case "hd":
                        $application->setBackgroundImageHd($relative_path.$file);
                        break;
                    case "tablet":
                        $application->setBackgroundImageTablet($relative_path.$file);
                        break;
                }
                
                $application->save();

                $url = $application->getHomepageBackgroundImageUrl($filetype);

                $datas = array(
                    'success' => 1,
                    'file' => $url
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

    public function deletehomepageAction() {
        $filetype = $this->_request->getparam('filetype');
        try {
            if($filetype == 'bg') {
                $this->getApplication()->setHomepageBackgroundImageRetinaLink(null);
                $this->getApplication()->setHomepageBackgroundImageLink(null);
                $this->getApplication()->setHomepageBackgroundImageId(null);
            } else if($filetype == 'icon') {
                $this->getApplication()->setHomepageLogoLink(null);
            }
            $this->getApplication()->save();
            $html = array('success' => '1');
        } catch(Exception $e) {
            $html = array('message' => $e->getMessage());
        }
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    /**
     * HOMEPAGE SLIDER
     */
    public function changehomepageslidervisibilityAction() {

        if($datas = $this->getRequest()->getPost()) {
            try {
                $this->getApplication()
                    ->setHomepageSliderIsVisible($datas['slider_is_visible'])
                    ->save()
                ;

                $html = array(
                    "success" => 1,
                    "reload" => 1
                );
            }catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function changehomepageslidersizeAction() {

            if($datas = $this->getRequest()->getPost()) {
                try {
                    $this->getApplication()
                        ->setHomepageSliderSize($datas['slider_size'])
                        ->save()
                    ;

                    $html = array(
                        "success" => 1,
                        "reload" => 1
                    );
                }catch(Exception $e) {
                    $html = array(
                        'message' => $e->getMessage(),
                    );
                }

                $this->getLayout()->setHtml(Zend_Json::encode($html));
            }
        }

    public function changehomepagesliderloopsystemAction() {

        if($datas = $this->getRequest()->getPost()) {
            try {
                $this->getApplication()
                    ->setHomepageSliderLoopAtBeginning($datas['slider_loop_at_beginning'])
                    ->save()
                ;

                $html = array(
                    "success" => 1,
                    "reload" => 1
                );
            }catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function setimagessliderdurationAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {
                if(isset($datas['slider_image_duration'])) {
                    if(is_numeric($datas['slider_image_duration'])) {
                        $application = $this->getApplication();
                        $application->setHomepageSliderDuration($datas['slider_image_duration'])->save();
                    } else {
                        throw new Exception($this->_('Please enter a number for the duration.'));
                    }
                }

                $html = array(
                    'success' => 1,
                    'reload' => 1
                );
            } catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->_sendHtml($html);
        }
    }

    public function savesliderimagesAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {
                $url = "";
                $image_id = null;

                $application = $this->getApplication();

                $relative_path = '/'.$application->getId().'/slider_images/';
                $folder = Application_Model_Application::getBaseImagePath().$relative_path;
                $datas['dest_folder'] = $folder;

                $uploader = new Core_Model_Lib_Uploader();
                if($file = $uploader->savecrop($datas)) {
                    $url = Application_Model_Application::getImagePath() . $relative_path . $file;

                    $library = new Media_Model_Library();
                    $library->find($application->getHomepageSliderLibraryId());

                    if(!$library->getId()) {
                        $library->setName('homepage_slider_' . $application->getId())->save();
                        $application->setHomepageSliderLibraryId($library->getId())->save();
                    }

                    $image = new Media_Model_Library_Image();
                    $image->setLibraryId($library->getId())
                        ->setLink($url)
                        ->setAppId($application->getId())
                        ->save()
                    ;

                    $image_id = $image->getId();
                }

                $datas = array(
                    'success' => 1,
                    'file' => array(
                        "id" => $image_id,
                        "url" => $url
                    )
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

    public function setsliderimagepositionsAction() {

        try {

            $image_positions = $this->getRequest()->getParam('slider_image');
            if(empty($image_positions)) throw new Exception($this->_('An error occurred while sorting your slider images. Please try again later.'));

            $image = new Media_Model_Library_Image();
            $image->updatePositions($image_positions);

            $html = array(
                'success' => 1,
                'reload' => 1
            );

        } catch(Exception $e) {
            $html = array(
                'message' => $e->getMessage(),
                'message_button' => 1,
                'message_loader' => 1
            );
        }

        $this->_sendHtml($html);
    }

    public function deletesliderimageAction() {

        try {

            $image_id = $this->_request->getparam('image_id');

            $library_image = new Media_Model_Library_Image();
            $library_image->find($image_id);

            $file = Core_Model_Directory::getBasePathTo($library_image->getLink());

            $library_image->delete();

            if(file_exists($file)) {
                if(unlink($file)) {
                    $html = array(
                        'success' => 1,
                        'reload' => 1
                    );
                } else {
                    throw new Exception($this->_("An error occurred while deleting your picture"));
                }
            }

        } catch(Exception $e) {
            $html = array('message' => $e->getMessage());
        }

        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    public function savefontAction() {
        if($datas = $this->getRequest()->getPost()) {

            try {
                if(!empty($datas['font_family'])) $this->getApplication()->setFontFamily($datas['font_family']);

                $application = $this->getApplication();

                $application->save();

                if($application->useIonicDesign()) {
                    Template_Model_Design::generateCss($application, false, false, true);
                }

                $html = array('success' => '1');

            }
            catch(Exception $e) {
                $html = array('message' => $e->getMessage());
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function savelocaleAction() {
        if($datas = $this->getRequest()->getPost()) {

            try {
                if(!empty($datas['locale'])) $this->getApplication()->setLocale($datas['locale']);
                $this->getApplication()->save();
                $html = array('success' => '1');
            }
            catch(Exception $e) {
                $html = array('message' => $e->getMessage());
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

}

