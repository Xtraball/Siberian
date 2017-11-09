<?php

class Application_Customization_FeaturesController extends Application_Controller_Default {

    /**
     * @var array
     */
    public $cache_triggers = array(
        "save" => array(
            "tags" => array("app_#APP_ID#"),
        ),
        "delete" => null, /** Specific, done inside the action. */
        "setisactive" => array(
            "tags" => array("homepage_app_#APP_ID#"),
        ),
        "seticon" => array(
            "tags" => array("app_#APP_ID#"),
        ),
        "settabbarname" => array(
            "tags" => array("app_#APP_ID#"),
        ),
        "settabbarsubtitle" => array(
            "tags" => array("app_#APP_ID#"),
        ),
        "seticonpositions" => array(
            "tags" => array("app_#APP_ID#"),
        ),
        "setbackgroundimage" => array(
            "tags" => array("app_#APP_ID#"),
        ),
        "setlayout" => array(
            "tags" => array("app_#APP_ID#"),
        ),
        "import" => array(
            "tags" => array("app_#APP_ID#"),
        ),
    );

    public function listAction() {
        /** This page doesn't need media optimizer (also this can lead to timeout) */
        Siberian_Media::disableTemporary();

        $this->loadPartials();
        if($this->getRequest()->isXmlHttpRequest()) {
            $html = array('html' => $this->getLayout()->getPartial('content_editor')->toHtml());
            $this->_sendHtml($html);
        }
    }

    /**
     * 29-Jan-2016
     *
     * Get links for in-app linking
     */
    public function linksAction() {
        $features = $this->getApplication()->getUsedOptions();

        $states = array();

        # Default home state
        $states[] = array(
            __("Home"),
            array(array("state" => "home")),
        );

        foreach($features as $feature) {
            try{
                $feature_model = $feature->getModel();
                if(!class_exists($feature_model)) {
                    throw new Exception("Class doesn't exists : ".$feature_model);
                }
                $feature_model = new $feature_model();

                if($feature_states = $feature_model->getInappStates($feature->getValueId())) {
                    $states[] = array(
                        __($feature->getTabbarname()),
                        $feature_states
                    );
                }
            } catch(Exception $e) {
                log_info($e->getMessage());
            }
        }

        $this->_sendJson($states, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function preloadAction() {
        $view = new Application_View_Customization_Features_List_Options();
        $option = new Application_Model_Option();
        $options = $option->findAll(array());
        foreach($options as $option) {
            $view->getIconUrl($option);
        }
        $this->_sendHtml(array("succes" => 1));
    }

    public function editAction() {

        if($type = $this->getRequest()->getParam('type')) {
            $this->getLayout()->setBaseRender('content', sprintf('application/customization/page/edit/%s.phtml', $type), 'admin_view_default');
            $html = array('html' => $this->getLayout()->render());
            $this->_sendHtml($html);
        }

    }

    public function saveAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                $delete_features = array();
                $app_id = $this->getApplication()->getId();

                if(empty($datas['option_id'])) throw new Exception(__('An error occurred while adding the option'));

                // Récupère l'option
                $option_id = $datas['option_id'];
                unset($datas['option_id']);
                $option = new Application_Model_Option();
                $option->find($option_id);
                if(!$option->getId()) throw new Exception(__('An error occurred while adding the option'));

                // Récupère les données de l'application pour cette option
                $option_value = new Application_Model_Option_Value();
                if(!empty($datas['value_id'])) {
                    $option_value->find($datas['value_id']);
                    // Test s'il n'y a pas embrouille entre les ids passés en paramètre et l'application en cours customization
                    if($option_value->getId() AND ($option_value->getOptionId() != $option->getId() OR $option_value->getAppId() != $app_id)) {
                        throw new Exception(__('An error occurred while adding the option'));
                    }
                    unset($datas['value_id']);
                }

                // Ajoute les données
                $option_value->addData(array(
                    'app_id' => $app_id,
                    'option_id' => $option->getId(),
                    'position' => $option_value->getPosition() ? $option_value->getPosition() : 0,
                    'is_visible' => 1
                ))->addData($datas);

                $option_value->setIconId($option->getDefaultIconId());

                // Sauvegarde
                $option_value->save();
                $id = $option_value->getId();
                $option_value = new Application_Model_Option_Value();
                $option_value->find($id);

                $option_value->getObject()->prepareFeature($option_value);

                if($option->onlyOnce()) {
                    $delete_features[] = $option->getId();
                }

                $row = $this->getLayout()->addPartial('row_'.$option_value->getId(), 'application_view_customization_features_list_options', 'application/customization/features/list/options/li.phtml')->setOptionValue($option_value)->setIsSortable(1)->toHtml();
                $html = array(
                    'success' => 1,
                    'page_html' => $row,
                    'path' => $option_value->getPath(null, array(), "mobile"),
                    'delete_features' => $delete_features,
                    'page_id' => $option_value->getOptionId(),
                    'use_my_account' => $this->getApplication()->usesUserAccount()
                );

            }
            catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->_sendHtml($html);
        }

    }

    public function deleteAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                if(empty($datas['value_id'])) throw new Exception(__('An error occurred while deleting the option'));

                // Récupère les données de l'application pour cette option
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);
                $app_id = $this->getApplication()->getId();

                if(!$option_value->getId() OR $option_value->getAppId() != $app_id) {
                    throw new Exception(__('An error occurred while deleting the option'));
                }

                $html = array(
                    'success' => 1,
                    'value_id' => $datas['value_id'],
                    'path' => $option_value->getPath(null, array(), "mobile"),
                    'was_folder' => false,
                    'was_category' => false,
                    'was_feature' => false
                );

                // Option folder
                if(isset($datas['category_id'])) {

                    $this->cache_triggers["delete"] = array(
                        "tags" => array(
                            "feature_paths_valueid_#VALUE_ID#",
                            "assets_paths_valueid_#VALUE_ID#",
                        ),
                    );
                    $this->_triggerCache();
                    $this->cache_triggers["delete"] = null;


                    $option_value->setFolderId(null)
                        ->setFolderCategoryId(null)
                        ->setFolderCategoryPosition(null)
                        ->save()
                    ;

                    $html['was_category'] = true;
                    $html['category'] = array('id' => $datas['category_id']);

                } else {

                    $this->cache_triggers["delete"] = array(
                        "tags" => array(
                            "app_#APP_ID#",
                        ),
                    );
                    $this->_triggerCache();
                    $this->cache_triggers["delete"] = null;

                    // Récupère l'option
                    $option = new Application_Model_Option();
                    $option->find($option_value->getOptionId());

                    $html['was_feature'] = true;

                    if($option_value->getCode() == "folder") {
                        $html['was_folder'] = true;
                    }

                    // Supprime l'option de l'application
                    $option_value->delete();

                    $html['use_my_account'] = $this->getApplication()->usesUserAccount();

                    if($option->onlyOnce()) {
                        $html['page'] = array('id' => $option->getId(), 'name' => $option->getName(), 'icon_url' => $option->getIconUrl(), 'category_id' => $option->getCategoryId());
                    }

                }
            }
            catch(Exception $e) {
                $html = array(
                    'message' => __('An error occurred while deleting the option'),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->_sendHtml($html);
        }

    }

    public function setisactiveAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {
                if(empty($datas['value_id'])) throw new Exception(__('#107: An error occurred while saving'));

                // Récupère les données de l'application pour cette option
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);
                if(isset($datas['is_active'])) {
                    $option_value->setIsActive($datas['is_active']);
                } else if(isset($datas['is_social_sharing_active'])) {
                    $option_value->setSocialSharingIsActive($datas['is_social_sharing_active']);
                } else {
                    throw new Exception(__('#108: An error occurred while saving'));
                }

                $option_value->save();

                $html = array('success' => 1, 'option_id' => $option_value->getId(), 'is_folder' => (int) ($option_value->getCode() == 'folder'));

            }
            catch(Exception $e) {
                $html = array(
//                    'message' => $e->getMessage(),
                    'message' => __('#109: An error occurred while saving'),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->_sendHtml($html);
        }

    }

    public function seticonAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                // Charge l'icône
                $icon = new Media_Model_Library_Image();
                $icon->find($datas['icon_id']);

                // Test si l'option_value_id est passé en paramètre
                if(empty($datas['option_value_id'])) {
                    throw new Exception(__('#110: An error occurred while saving'));
                }
                if(empty($datas['icon_id'])) {
                    throw new Exception(__('An error occurred while saving. The selected icon is not valid.'));
                }

                $icon_saved = $this->setIcon($datas['icon_id'], $datas['option_value_id']);
                if(!empty($icon_saved)) {

                    // Charge l'option_value
                    $option_value = new Application_Model_Option_Value();
                    $option_value->find($datas['option_value_id']);
                    $icon_color = $this->getApplication()->getBlock('tabbar')->getImageColor();

                    $icon_url = $icon_saved['icon_url'];
                    if($icon->getCanBeColorized()) {
                        Siberian_Media::disableTemporary();
                        $icon_url = $this->_getColorizedImage($icon->getImageId(), $option_value->getIconColor());
                    }

                    $icon_url_reverse_color = null;
                    if($icon_reverse_color = $option_value->getIconReverseColor()) {
                        Siberian_Media::disableTemporary();
                        $icon_url_reverse_color = $this->_getColorizedImage($icon->getImageId(), $icon_reverse_color);
                    }

                    $html = array(
                        'success' => 1,
                        'icon_id' => $icon->getId(),
                        'icon_url' => $icon_url,
                        'colored_icon_url' => $this->getUrl('template/block/colorize', array('id' => $datas['icon_id'], 'color' => str_replace('#', '', $icon_color))),
                        'colored_header_icon_url' => $icon_saved['colored_header_icon_url'],
                        'colored_reverse_color_icon_url' => $icon_url_reverse_color
                    );

                } else {
                    throw new Exception(__('#111: An error occurred while saving'));
                }

            }
            catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->_sendHtml($html);

        }

    }

    private function setIcon($icon_id, $option_value_id) {

        try {

            $custom = false;
            if(in_array($option_value_id, array("customer_account", "more_items"))) {
                $custom = true;
            }

            // Récupère l'application
            $application = $this->getApplication();

            // Charge l'icône
            $icon = new Media_Model_Library_Image();
            $icon->find($icon_id);

            if(!$icon->getId()) {
                throw new Exception(__('An error occurred while saving. The selected icon is not valid.'));
            }

            // Charge l'option_value
            if(!$custom) {
                $option_value = new Application_Model_Option_Value();
                $option_value->find($option_value_id);
                // Tout va bien, on met à jour l'icône pour cette option_value
                $option_value->setIconId($icon->getId())
                    ->setIcon(null)
                    ->save()
                ;
                $icon_url = $option_value->resetIconUrl()->getIconUrl(true);
                $colorizable = $option_value->getImage()->getCanBeColorized();

            } else {
                $app = $this->getApplication();

                switch($option_value_id) {
                    case "customer_account":
                        $app->setAccountIconId($icon->getId())->save();
                        break;
                    case "more_items":
                        $app->setMoreIconId($icon->getId())->save();
                        break;
                }

                $icon = new Media_Model_Library_Image();
                $icon->find($icon->getId());
                $icon_url = $icon->getUrl();

                $colorizable = $icon->getCanBeColorized();
            }


            $colored_header_icon_url = $icon_url;
            if($colorizable) {
                Siberian_Media::disableTemporary();
                $header_color = $application->getBlock('header')->getColor();
                $colored_header_icon_url = $this->getUrl('template/block/colorize', array('id' => $icon->getId(), 'color' => str_replace('#', '', $header_color)));
            }

            $return = array(
                'colored_header_icon_url' => $colored_header_icon_url,
                'icon_url' => $icon_url
            );

            return $return;

        } catch(Exception $e) {
            return false;
        }

    }

    public function deleteiconAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {
                $icon = new Media_Model_Library_Image();
                $icon->find($datas['icon_id']);
                if($icon->getAppId()) {
                    $icon->delete();
                } else {
                    throw new Exception(__("You may not delete a library icon"));
                }

                $html = array(
                    'success' => 1,
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

    public function seticonpositionsAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                // Récupère les positions
                $positions = $this->getRequest()->getParam('option_value');
                if(empty($positions)) {
                    throw new Exception(__('An error occurred while sorting your pages. Please try again later.'));
                }

                // Supprime les positions en trop, au cas où...
                $option_values = $this->getApplication()->getPages();
                $option_value_ids = array();
                foreach($option_values as $option_value) {
                    if($option_value->getFolderCategoryId()) continue;
                    $option_value_ids[] = $option_value->getId();
                }

//                Now, some icons can be hidden because of ACL by features, so we skip this test
//                $diff = array_diff($option_value_ids, $positions);
//                if(!empty($diff)) throw new Exception(__('An error occurred while sorting your pages. Please try again later.'));

                // Met à jour les positions des option_values
                $this->getApplication()->updateOptionValuesPosition($positions);

                // Renvoie OK
                $html = array('success' => 1);

            }
            catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->_sendHtml($html);

        }

    }

    public function settabbarnameAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                // Test les données
                if(empty($datas['option_value_id']) OR empty($datas['tabbar_name'])) {
                    throw new Siberian_Exception(__('An error occurred while saving your page name.'));
                }

                // Charge l'option_value
                $option_value = new Application_Model_Option_Value();
                $option_value->setApplication($this->getApplication());
                $option_value->find($datas['option_value_id']);

                // Test s'il n'y a pas embrouille entre l'id de l'application dans l'option_value et l'id de l'application en session
                if(!$option_value->getId()) {
                    throw new Siberian_Exception(__('An error occurred while saving your page name.'));
                }


                $option_folder = new Application_Model_Option();
                $option_folder->find(array('code' => 'folder'));
                $option_folder_id = $option_folder->getOptionId();

                if($option_value->getOptionId() == $option_folder_id) {
                    $folder = new Folder_Model_Folder();
                    $folder->find($datas['option_value_id'], 'value_id');
                    $category = new Folder_Model_Category();
                    $category->find($folder->getRootCategoryId(), 'category_id');
                    $category->setTitle($datas['tabbar_name'])->save();
                }

                /** Privacy policy special case */
                $current_option = new Application_Model_Option();
                $current_option->find($option_value->getOptionId());
                if($current_option->getCode() === "privacy_policy") {
                    $this->getApplication()->setPrivacyPolicyTitle($datas['tabbar_name'])->save();
                }

                if(in_array($option_value->getId(), array('customer_account', 'more_items'))) {
                    $code = $option_value->getId() == 'customer_account' ? 'tabbar_account_name' : 'tabbar_more_name';
                    $this->getApplication()->setData($code, $datas['tabbar_name'])->save();
                }
                else {
                    $option_value->setTabbarName($datas['tabbar_name'])
                        ->save()
                    ;
                }

                // Renvoie OK
                $html = array('success' => 1);

            }
            catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->_sendHtml($html);

        }

    }

    public function settabbarsubtitleAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                // Test les données
                if(empty($datas['option_value_id']) OR empty($datas['tabbar_subtitle'])) {
                    throw new Exception(__('An error occurred while saving your page subtitle.'));
                }

                switch($datas['option_value_id']) {
                    case "customer_account":
                            $this->getApplication()->setAccountSubtitle($datas['tabbar_subtitle'])->save();
                        break;
                    case "more_items":
                        $this->getApplication()->setMoreSubtitle($datas['tabbar_subtitle'])->save();
                        break;
                    default:
                        // Charge l'option_value
                        $option_value = new Application_Model_Option_Value();
                        $option_value->setApplication($this->getApplication());
                        $option_value->find($datas['option_value_id']);

                        // Test s'il n'y a pas embrouille entre l'id de l'application dans l'option_value et l'id de l'application en session
                        if(!$option_value->getId()) {
                            throw new Exception(__('An error occurred while saving your page subtitle.'));
                        }

                        $option_value->setTabbarSubtitle($datas['tabbar_subtitle'])
                            ->save()
                        ;
                }
                
                // Renvoie OK
                $html = array('success' => 1);

            }
            catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->_sendHtml($html);

        }

    }

    public function uploadiconAction() {
        if($datas = $this->getRequest()->getPost()) {

            $CanBeColorized = $datas['is_colorized'] == 'true' ? 1 : 0;

            # Disable media optimization for colorizable icons
            if($CanBeColorized) {
                Siberian_Media::disableTemporary();
            }

            try {
                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->savecrop($datas);
                $app_id = $this->getApplication()->getId();
                if(!empty($file)) {

                    if(in_array($datas['option_id'], array("customer_account", "more_items"))) {
                        $library_name = $datas['option_id'];
                        $library = new Media_Model_Library();
                        $library->find($library_name, "name");

                        $library_id = $library->getId();
                        $option_id = null;
                    } else{
                        $option_value = new Application_Model_Option_Value();
                        $option_value->find($datas['option_id']);

                        $library_name = $option_value->getLibrary()->getName();

                        $library_id = $option_value->getLibrary()->getId();
                        $option_id = $option_value->getOptionId();
                    }

                    $formated_library_name = Core_Model_Lib_String::format($library_name, true);
                    $base_lib_path = Media_Model_Library_Image::getBaseImagePathTo($formated_library_name, $app_id);

                    $files = Core_Model_Directory::getTmpDirectory(true).'/'.$file;


                    if(!is_dir($base_lib_path)) {
                        mkdir($base_lib_path, 0777, true);
                    }
                    if(!copy($files, $base_lib_path.'/'.$file)) {
                        throw new exception(__('An error occurred while saving your picture. Please try againg later.'));
                    } else {

                        $icon_lib = new Media_Model_Library_Image();
                        $icon_lib->setLibraryId($library_id)
                            ->setLink('/'.$formated_library_name.'/'.$file)
                            ->setOptionId($option_id)
                            ->setAppId($app_id)
                            ->setCanBeColorized($CanBeColorized)
                            ->setPosition(0)
                            ->save();

                        if(in_array($datas['option_id'], array("customer_account", "more_items"))) {
                        } else {
                            $option_value
                                ->setIcon('/'.$formated_library_name.'/'.$file)
                                ->setIconId($icon_lib->getImageId())
                                ->save();
                        }

                        $icon_saved = $this->setIcon($icon_lib->getImageId(), $datas['option_id']);

                        // Charge l'option_value
                        //$option_value = new Application_Model_Option_Value();
                        //$option_value->find($datas['option_id']);
                        $icon_url = $icon_lib->getUrl();
                        if($CanBeColorized) {
                            $header_color = $this->getApplication()->getBlock('header')->getColor();
                            $icon_url = $this->getUrl('template/block/colorize', array('id' => $icon_lib->getImageId(), 'color' => str_replace('#', '', $header_color)));
                        }

                        $icon_color = $this->getApplication()->getBlock('header')->getBackgroundColor();

                        $html = array (
                            'success' => 1,
                            'file' => '/'.$formated_library_name.'/'.$file,
                            'icon_id' => $icon_lib->getImageId(),
                            'colorizable' => a,
                            'icon_url' => $icon_url,
                            'colored_icon_url' => $this->getUrl('template/block/colorize', array('id' => $icon_lib->getImageId(), 'color' => str_replace('#', '', $icon_color))),
                            'colored_header_icon_url' => $icon_saved['colored_header_icon_url'],
                            'message' => '',
                            'message_button' => 1,
                            'message_loader' => 1
                        );
                    }
                }

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

    public function setbackgroundimageAction() {
        if($datas = $this->getRequest()->getPost()) {

            try {

                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['option_id']);
                if(!$option_value->getId()) throw new Exception(__("An error occurred while saving your picture. Please try againg later."));

                // Récupère l'option
                $option = new Application_Model_Option();
                $option->find($option_value->getOptionId());

                $save_path = '/feature/'.$option->getId().'/background/';
                $relative_path = Application_Model_Application::getImagePath().$save_path;
                $folder = Application_Model_Application::getBaseImagePath().$save_path;

                $datas['dest_folder'] = $folder;

                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->savecrop($datas);

                $option_value->setBackgroundImage($save_path.$file)->save();

                $datas = array(
                    'success' => 1,
                    'file' => $relative_path.$file,
                );
            } catch (Exception $e) {
                $datas = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }

            $this->_sendHtml($datas);
        }
    }

    public function deletebackgroundimageAction() {
        if($datas = $this->getRequest()->getParams()) {
            try {
                if(empty($datas['value_id'])) throw new Exception(__('An error occurred while deleting your picture'));

                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);
                if(!$option_value->getId()) throw new Exception(__('An error occurred while deleting your picture'));
                $option_value->setBackgroundImage(null)->save();

                $datas = array(
                    'success' => 1,
                    'background_image_url' => $option_value->reload()->getBackgroundImageUrl()
                );
            } catch (Exception $e) {
                $datas = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }

            $this->_sendHtml($datas);
        }
    }

    public function setlayoutAction() {

        if($data = $this->getRequest()->getPost()) {

            try {

                if(empty($data["layout_id"]) OR !$this->getCurrentOptionValue() OR $this->getCurrentOptionValue()->getAppId() != $this->getApplication()->getId()) {
                    throw new Exception(__(""));
                }

                $layouts = $this->getCurrentOptionValue()->getLayouts();
                $layout_exists = false;
                foreach($layouts as $layout) {
                    if($layout->getCode() == $data["layout_id"]) {
                        $layout_exists = true;
                        break;
                    }
                }

                $this->getCurrentOptionValue()
                    ->setLayoutId($data["layout_id"])
                    ->save()
                ;

                $data = array(
                    "success" => 1
                );

            } catch (Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendHtml($data);

        }

    }

    /**
     *
     */
    public function importAction() {
        try {

            $data = array(
                "success" => 1,
                "message" => __("Import success."),
            );

            if (empty($_FILES) || empty($_FILES['files']['name'])) {
                throw new Exception("#486-01: No file sent.");
            } else {

                $tmp = Core_Model_Directory::getTmpDirectory(true);
                $tmp_path = $tmp."/".$_FILES['files']['name'][0];
                if(!rename($_FILES['files']['tmp_name'][0], $tmp_path)) {
                    throw new Exception("#486-02: Unable to write file.");
                } else {
                    /** Detect if it's a simple feature or a complete template Application */
                    $filetype = pathinfo($tmp_path, PATHINFO_EXTENSION);
                    switch($filetype) {
                        case "yml":
                                $this->importFeature($tmp_path);

                                $data["message"] = __("Feature successfuly imported.");
                            break;
                        case"zip":
                                if(!$this->getRequest()->getParam("confirm", false)) {
                                    $data = array(
                                        "confirm" => 1,
                                        "message" => __("Your are about to replace the current application template, colors & features.\nAre you sure ?"),
                                    );
                                } else {
                                    $this->importApplication($tmp_path);

                                    $data["message"] = __("Application template successfully imported.");
                                }
                            break;
                    }
                }
            }

        } catch(Exception $e) {
            $data = array(
                "error" => 1,
                "message" => $e->getMessage()
            );
        }

        $this->_sendHtml($data);
    }

    private function importApplication($path) {
        /** Unzip the archive */
        $folder = Core_Model_Directory::unzip($path);

        /** Clean-up after upload. */
        Core_Model_Directory::delete($folder);
        Core_Model_Directory::delete($path);
    }

    /**
     * Import a single feature.
     *
     * @param $path
     * @throws Exception
     */
    private function importFeature($path) {
        $option_value_model = new Application_Model_Option_Value();
        $option_value = $option_value_model->readOption($path);

        $application = $this->getApplication();
        $existing_options = $application->getOptions();

        if ($option_value->getCode()) {
            $option_model = new Application_Model_Option();
            $option = $option_model->find($option_value->getCode(), "code");

            foreach($existing_options as $existing_option) {
                if(($existing_option->getCode() == $option->getCode()) && $existing_option->getOnlyOnce()) {
                    throw new Exception("#486-05: You can have only one feature '{$option->getName()}'.");
                }
            }

            if (!$option->getId()) {
                throw new Exception("#486-03: This feature is not available for you.");
            } else {
                if (Siberian_Exporter::isRegistered($option->getCode())) {
                    $classname = Siberian_Exporter::getClass($option->getCode());
                    $importer = new $classname();
                    $importer->importAction($path);
                } else {
                    throw new Exception("#486-04: Sorry this feature doesn't expose its import interface.");
                }
            }
        }
    }

    /**
     * Modal dialog for import/export
     */
    public function exportmodalAction() {
        $layout = $this->getLayout();
        $layout->setBaseRender('modal', 'html/modal.phtml', 'core_view_default')
            ->setTitle(__('Import / Export'))
            ->setBorderColor("border-blue")
        ;
        $layout->addPartial('modal_content', 'admin_view_default', 'application/customization/features/export.phtml');
        $html = array('modal_html' => $layout->render());

        $this->_sendHtml($html);
    }

    /**
     * Export the application to YAML
     */
    public function exportAction() {
        $application = $this->getApplication();
        $options = $application->getOptions();
        $request = $this->getRequest();

        $application_form_export = new Application_Form_Export();
        $application_form_export->addOptions($application);
        $application_form_export->addTemplate();

        # Export as Template
        $is_template = $request->getParam("is_template");
        if($is_template) {
            $application_form_export->isTemplate();
        }
        $template_name = $request->getParam("template_name", __("MyTemplate"));
        $template_version = $request->getParam("template_version", "1.0");
        $template_description = $request->getParam("template_description", __("My custom template"));

        if($application_form_export->isValid($request->getParams())) {
            # Folder
            $folder_name = "export-app-".$application->getId()."-".date("Y-m-d_h-i-s")."-".uniqid();
            $tmp = Core_Model_Directory::getBasePathTo("var/tmp/");
            $tmp_directory = $tmp."/".$folder_name;
            $options_directory = $tmp_directory."/options";
            mkdir($options_directory, 0777, true);

            $selected_options = $request->getParam("options");
            foreach($options as $option) {
                if(isset($selected_options[$option->getId()]) && $selected_options[$option->getId()]) {
                    if(Siberian_Exporter::isRegistered($option->getCode())) {
                        $exporter_class = Siberian_Exporter::getClass($option->getCode());
                        if(class_exists($exporter_class) && method_exists($exporter_class, "exportAction")) {
                            $tmp_class = new $exporter_class();
                            $export_type = $selected_options[$option->getId()];
                            $dataset = $tmp_class->exportAction($option, $export_type);
                            file_put_contents("{$options_directory}/{$option->getPosition()}-{$option->getCode()}.yml", $dataset);
                        }
                    }
                }
            }

            /** Application */
            $application_dataset = $application->toYml();
            file_put_contents("{$tmp_directory}/application.yml", $application_dataset);

            /** package.json */
            $package = array(
                "name" => ($is_template) ? $template_name : $folder_name,
                "decription" => ($is_template) ? $template_description : "User exported application template.",
                "version" => ($is_template) ? $template_version : "1.0",
                "flavor" => Siberian_Exporter::FLAVOR,
                "type" => "template",
                "dependencies" => array(
                    "system" => array(
                        "type" => "SAE",
                        "version" => Siberian_Exporter::MIN_VERSION,
                    ),
                ),
            );

            file_put_contents("{$tmp_directory}/package.json", Siberian_Json::encode($package));

            $zip = Core_Model_Directory::zip($tmp_directory, $tmp."/".$folder_name.".zip");
            $base = Core_Model_Directory::getBasePathTo("");
            $url = $this->getUrl().str_replace($base, "",  $zip);

            if(file_exists($zip)) {
                $data = array(
                    "success" => 1,
                    "message" => __("Downloading your package."),
                    "type" => "download",
                    "url" => $url
                );
            } else {
                $data = array(
                    "error" => 1,
                    "message" => __("#498-01: An error occured while exporting your application.")
                );
            }

        } else {
            $data = array(
                "error" => 1,
                "message" => $application_form_export->getTextErrors(),
                "errors" => $application_form_export->getTextErrors(true)
            );
        }

        $this->_sendHtml($data);
    }

}
