<?php

class Application_Customization_FeaturesController extends Application_Controller_Default {

    public function listAction() {
        $this->loadPartials();
        if($this->getRequest()->isXmlHttpRequest()) {
            $html = array('html' => $this->getLayout()->getPartial('content_editor')->toHtml());
            $this->_sendHtml($html);
        }
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

                if(empty($datas['option_id'])) throw new Exception($this->_('An error occurred while adding the option'));

                // Récupère l'option
                $option_id = $datas['option_id'];
                unset($datas['option_id']);
                $option = new Application_Model_Option();
                $option->find($option_id);
                if(!$option->getId()) throw new Exception($this->_('An error occurred while adding the option'));

                // Récupère les données de l'application pour cette option
                $option_value = new Application_Model_Option_Value();
                if(!empty($datas['value_id'])) {
                    $option_value->find($datas['value_id']);
                    // Test s'il n'y a pas embrouille entre les ids passés en paramètre et l'application en cours customization
                    if($option_value->getId() AND ($option_value->getOptionId() != $option->getId() OR $option_value->getAppId() != $app_id)) {
                        throw new Exception($this->_('An error occurred while adding the option'));
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

                // Sauvegarde
                $option_value->save();
                $id = $option_value->getId();
                $option_value = new Application_Model_Option_Value();
                $option_value->find($id);

                $option_value->getObject()->prepareFeature($option_value);

                if($option->onlyOnce()) $delete_features[] = $option->getId();

                // Renvoi le nouveau code HTML
//                $this->getLayout()->setBaseRender('content', 'application/customization/page/list.phtml', 'admin_view_default');
                $row = $this->getLayout()->addPartial('row_'.$option_value->getId(), 'application_view_customization_features_list_options', 'application/customization/features/list/options/li.phtml')->setOptionValue($option_value)->setIsSortable(1)->toHtml();
                $html = array(
                    'success' => 1,
                    'page_html' => $row,
                    'path' => $option_value->getPath(null, array(), "mobile"),
                    'delete_features' => $delete_features,
                    'page_id' => $option_value->getOptionId(),
                    'use_user_account' => $this->getApplication()->usesUserAccount()
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

                if(empty($datas['value_id'])) throw new Exception($this->_('An error occurred while deleting the option'));

                // Récupère les données de l'application pour cette option
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);
                $app_id = $this->getApplication()->getId();

                if(!$option_value->getId() OR $option_value->getAppId() != $app_id) {
                    throw new Exception($this->_('An error occurred while deleting the option'));
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

                    $option_value->setFolderId(null)
                        ->setFolderCategoryId(null)
                        ->setFolderCategoryPosition(null)
                        ->save()
                    ;

                    $html['was_category'] = true;
                    $html['category'] = array('id' => $datas['category_id']);

                } else {

                    // Récupère l'option
                    $option = new Application_Model_Option();
                    $option->find($option_value->getOptionId());

                    $html['was_feature'] = true;
                    $html['use_user_account'] = $this->getApplication()->usesUserAccount();

                    if($option_value->getCode() == "folder") {
                        $html['was_folder'] = true;
                    }

                    // Supprime l'option de l'application
                    $option_value->delete();

                    if($option->onlyOnce()) {
                        $html['page'] = array('id' => $option->getId(), 'name' => $option->getName(), 'icon_url' => $option->getIconUrl(), 'category_id' => $option->getCategoryId());
                    }

                    // Renvoi le nouveau code HTML
//                    $this->getLayout()->setBaseRender('content', 'application/customization/page/list.phtml', 'admin_view_default');

                }
            }
            catch(Exception $e) {
                $html = array(
                    'message' => $this->_('An error occurred while deleting the option'),
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
                if(empty($datas['value_id'])) throw new Exception($this->_('#107: An error occurred while saving'));

                // Récupère les données de l'application pour cette option
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);
                if(isset($datas['is_active'])) {
                    $option_value->setIsActive($datas['is_active']);
                } else if(isset($datas['is_social_sharing_active'])) {
                    $option_value->setSocialSharingIsActive($datas['is_social_sharing_active']);
                } else {
                    throw new Exception($this->_('#108: An error occurred while saving'));
                }

                $option_value->save();

                $html = array('success' => 1, 'option_id' => $option_value->getId(), 'is_folder' => (int) ($option_value->getCode() == 'folder'));

            }
            catch(Exception $e) {
                $html = array(
//                    'message' => $e->getMessage(),
                    'message' => $this->_('#109: An error occurred while saving'),
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
                if(empty($datas['option_value_id'])) throw new Exception($this->_('#110: An error occurred while saving'));
                if(empty($datas['icon_id'])) throw new Exception($this->_('An error occurred while saving. The selected icon is not valid.'));

                $icon_saved = $this->setIcon($datas['icon_id'], $datas['option_value_id']);
                if(!empty($icon_saved)) {

                    // Charge l'option_value
                    $option_value = new Application_Model_Option_Value();
                    $option_value->find($datas['option_value_id']);
                    $icon_color = $this->getApplication()->getBlock('tabbar')->getImageColor();

                    $icon_url = $icon_saved['icon_url'];
                    if($icon->getCanBeColorized()) {
                        $icon_url = $this->_getColorizedImage($icon->getImageId(), $option_value->getIconColor());
                    }

                    $html = array(
                        'success' => 1,
                        'icon_id' => $icon->getId(),
                        'icon_url' => $icon_url,
                        'colored_icon_url' => $this->getUrl('template/block/colorize', array('id' => $option_value->getIconId(), 'color' => str_replace('#', '', $icon_color))),
                        'colored_header_icon_url' => $icon_saved['colored_header_icon_url']
                    );

                } else {
                    throw new Exception($this->_('#111: An error occurred while saving'));
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

            // Récupère l'application
            $application = $this->getApplication();

            // Charge l'option_value
            $option_value = new Application_Model_Option_Value();
            $option_value->find($option_value_id);

            // Charge l'icône
            $icon = new Media_Model_Library_Image();
            $icon->find($icon_id);

            if(!$icon->getId() OR $icon->getLibraryId() != $option_value->getLibraryId() && $option_value->getCode() != 'folder') {
                throw new Exception($this->_('An error occurred while saving. The selected icon is not valid.'));
            }

            // Tout va bien, on met à jour l'icône pour cette option_value
            $option_value->setIconId($icon->getId())
                ->setIcon(null)
                ->save()
            ;

            $icon_url = $option_value->resetIconUrl()->getIconUrl(true);
            $colored_header_icon_url = $icon_url;
            if($option_value->getImage()->getCanBeColorized()) {
                $header_color = $application->getBlock('header')->getColor();
                $colored_header_icon_url = $this->getUrl('template/block/colorize', array('id' => $option_value->getIconId(), 'color' => str_replace('#', '', $header_color)));
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
                    throw new Exception($this->_("You may not delete a library icon"));
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
                if(empty($positions)) throw new Exception($this->_('An error occurred while sorting your pages. Please try again later.'));

                // Supprime les positions en trop, au cas où...
                $option_values = $this->getApplication()->getPages();
                $option_value_ids = array();
                foreach($option_values as $option_value) {
                    if($option_value->getFolderCategoryId()) continue;
                    $option_value_ids[] = $option_value->getId();
                }

//                Now, some icons can be hidden because of ACL by features, so we skip this test
//                $diff = array_diff($option_value_ids, $positions);
//                if(!empty($diff)) throw new Exception($this->_('An error occurred while sorting your pages. Please try again later.'));

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
                    throw new Exception($this->_('An error occurred while saving your page name.'));
                }

                // Charge l'option_value
                $option_value = new Application_Model_Option_Value();
                $option_value->setApplication($this->getApplication());
                $option_value->find($datas['option_value_id']);

                // Test s'il n'y a pas embrouille entre l'id de l'application dans l'option_value et l'id de l'application en session
                if(!$option_value->getId()) {
                    throw new Exception($this->_('An error occurred while saving your page name.'));
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

    public function uploadiconAction() {
        if($datas = $this->getRequest()->getPost()) {

            try {
                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->savecrop($datas);
                $app_id = $this->getApplication()->getId();
                if(!empty($file)) {

                    $option_value = new Application_Model_Option_Value();
                    $option_value->find($datas['option_id']);

                    $library_name = $option_value->getLibrary()->getName();
                    $formated_library_name = Core_Model_Lib_String::format($library_name, true);
                    $base_lib_path = Media_Model_Library_Image::getBaseImagePathTo($formated_library_name, $app_id);

                    $files = Core_Model_Directory::getTmpDirectory(true).'/'.$file;

                    $CanBeColorized = $datas['is_colorized'] == 'true' ? 1 : 0;

                    if(!is_dir($base_lib_path)) mkdir($base_lib_path, 0777, true);
                    if(!copy($files, $base_lib_path.'/'.$file)) {
                        throw new exception($this->_('An error occurred while saving your picture. Please try againg later.'));
                    } else {

                        $icon_lib = new Media_Model_Library_Image();
                        $icon_lib->setLibraryId($option_value->getLibraryId())
                            ->setLink('/'.$formated_library_name.'/'.$file)
                            ->setOptionId($option_value->getOptionId())
                            ->setAppId($app_id)
                            ->setCanBeColorized($CanBeColorized)
                            ->setPosition(0)
                            ->save();

                        $option_value
                            ->setIcon('/'.$formated_library_name.'/'.$file)
                            ->setIconId($icon_lib->getImageId())
                            ->save();

                        $icon_saved = $this->setIcon($icon_lib->getImageId(), $datas['option_id']);

                        // Charge l'option_value
                        $option_value = new Application_Model_Option_Value();
                        $option_value->find($datas['option_id']);
                        $icon_color = $this->getApplication()->getBlock('tabbar')->getImageColor();
                        $icon_url = $this->_getColorizedImage($icon_lib->getImageId(), $icon_color);

                        $html = array (
                            'success' => 1,
                            'file' => '/'.$formated_library_name.'/'.$file,
                            'icon_id' => $icon_lib->getImageId(),
                            'colorizable' => $CanBeColorized,
                            'icon_url' => $icon_url,
                            'colored_icon_url' => $this->getUrl('template/block/colorize', array('id' => $option_value->getIconId(), 'color' => str_replace('#', '', $icon_color))),
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
                if(!$option_value->getId()) throw new Exception($this->_("An error occurred while saving your picture. Please try againg later."));

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
                if(empty($datas['value_id'])) throw new Exception($this->_('An error occurred while deleting your picture'));

                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);
                if(!$option_value->getId()) throw new Exception($this->_('An error occurred while deleting your picture'));
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
                    throw new Exception($this->_(""));
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

}
