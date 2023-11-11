<?php

use Siberian\Exception;
use Siberian\File;

/**
 * Class Application_Customization_FeaturesController
 */
class Application_Customization_FeaturesController extends Application_Controller_Default
{

    /**
     * @var array
     */
    public $cache_triggers = [
        "save" => [
            "tags" => [
                "app_#APP_ID#"
            ],
            "outputTags" => [
                "cache_admin",
                "admin_#ADMIN_ID#",
                "app_#APP_ID#"
            ],
        ],
        "delete" => [
            "outputTags" => [
                "cache_admin",
                "admin_#ADMIN_ID#",
                "app_#APP_ID#"
            ],
        ],
        "setisactive" => [
            "tags" => ["homepage_app_#APP_ID#"],
        ],
        "seticon" => [
            "tags" => ["app_#APP_ID#"],
        ],
        "settabbarname" => [
            "tags" => ["app_#APP_ID#"],
        ],
        "settabbarsubtitle" => [
            "tags" => ["app_#APP_ID#"],
        ],
        "seticonpositions" => [
            "tags" => ["app_#APP_ID#"],
        ],
        "setbackgroundimage" => [
            "tags" => ["app_#APP_ID#"],
        ],
        "setlayout" => [
            "tags" => ["app_#APP_ID#"],
        ],
        "import" => [
            "tags" => ["app_#APP_ID#"],
        ],
    ];

    /**
     *
     */
    public function listAction()
    {
        /** This page doesn't need media optimizer (also this can lead to timeout) */
        Siberian_Media::disableTemporary();

        $this->loadPartials();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $html = ['html' => $this->getLayout()->getPartial('content_editor')->toHtml()];
            $this->_sendHtml($html);
        }
    }

    /**
     * 29-Jan-2016
     *
     * Get links for in-app linking
     */
    public function linksAction()
    {
        $features = $this->getApplication()->getUsedOptions();

        $states = [];

        # Default home state
        $states[] = [
            __("Home"),
            [["state" => "home"]],
        ];

        foreach ($features as $feature) {
            try {
                $feature_model = $feature->getModel();
                if (!class_exists($feature_model)) {
                    throw new Exception("Class doesn't exists : " . $feature_model);
                }
                $feature_model = new $feature_model();

                if ($feature_states = $feature_model->getInappStates($feature->getValueId())) {
                    $states[] = [
                        __($feature->getTabbarname()),
                        $feature_states
                    ];
                }
            } catch (\Exception $e) {
                log_info($e->getMessage());
            }
        }

        $this->_sendJson($states, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     *
     */
    public function preloadAction()
    {
        $view = new Application_View_Customization_Features_List_Options();
        $option = new Application_Model_Option();
        $options = $option->findAll([]);
        foreach ($options as $option) {
            $view->getIconUrl($option);
        }
        $this->_sendHtml(["succes" => 1]);
    }

    /**
     *
     */
    public function editAction()
    {

        if ($type = $this->getRequest()->getParam('type')) {
            $this->getLayout()->setBaseRender('content', sprintf('application/customization/page/edit/%s.phtml', $type), 'admin_view_default');
            $html = ['html' => $this->getLayout()->render()];
            $this->_sendHtml($html);
        }

    }

    /**
     *
     */
    public function saveAction()
    {
        try {
            $request = $this->getRequest();
            $datas = $request->getPost();
            $application = $this->getApplication();
            $appId = $application->getId();
            $deleteFeatures = [];

            if (empty($datas)) {
                throw new Exception(__('An error occurred while adding the option'));
            }

            if (empty($datas['option_id'])) {
                throw new Exception(__('An error occurred while adding the option'));
            }

            // Récupère l'option
            $option_id = $datas['option_id'];
            unset($datas['option_id']);
            $option = new Application_Model_Option();
            $option->find($option_id);
            if (!$option->getId()) {
                throw new Exception(__('An error occurred while adding the option'));
            }

            // Récupère les données de l'application pour cette option
            $optionValue = (new Application_Model_Option_Value());
            if (!empty($datas['value_id'])) {
                $optionValue = $optionValue->find($datas['value_id']);
                // Test s'il n'y a pas embrouille entre les ids passés en paramètre et l'application en cours customization
                if ($optionValue->getId() &&
                    ($optionValue->getOptionId() != $option->getId() || $optionValue->getAppId() != $appId)) {
                    throw new Exception(__('An error occurred while adding the option'));
                }
                unset($datas['value_id']);
            }

            // Ajoute les données
            $optionValue->addData([
                'app_id' => $appId,
                'option_id' => $option->getId(),
                'position' => $optionValue->getPosition() ? $optionValue->getPosition() : 0,
                'is_visible' => 1
            ])->addData($datas);

            $optionValue->setIconId($option->getDefaultIconId());

            // Sauvegarde
            $optionValue->save();
            $id = $optionValue->getId();
            $optionValue = new Application_Model_Option_Value();
            $optionValue->find($id);

            $optionValue->getObject()->prepareFeature($optionValue);

            if ($option->onlyOnce()) {
                $deleteFeatures[] = $option->getId();
            }

            $row = $this
                ->getLayout()
                ->addPartial(
                    'row_' . $optionValue->getId(),
                    'application_view_customization_features_list_options',
                    'application/customization/features/list/options/li.phtml')
                ->setOptionValue($optionValue)
                ->setIsSortable(1)
                ->toHtml();

            $useMysAccount = $application->usesUserAccount();
            $rowAccount = null;
            if ($useMysAccount) {
                $myAccount = $application->getMyAccount();

                if ($myAccount !== false) {
                    $rowAccount = $this
                        ->getLayout()
                        ->addPartial(
                            'row_' . $myAccount->getId(),
                            'application_view_customization_features_list_options',
                            'application/customization/features/list/options/li.phtml')
                        ->setOptionValue($myAccount)
                        ->setIsSortable(1)
                        ->toHtml();
                }
            }

            $payload = [
                'success' => true,
                'page_html' => $row,
                'path' => $optionValue->getPath(null, [], "mobile"),
                'delete_features' => $deleteFeatures,
                'page_id' => $optionValue->getOptionId(),
                'use_my_account' => $useMysAccount,
                'my_account' => $rowAccount
            ];

        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);

    }

    /**
     *
     */
    public function deleteAction()
    {
        try {
            if (!$this->_canAccess("delete_feature")) {
                throw new Exception(__("You are not allowed to delete a feature!"));
            }

            $request = $this->getRequest();
            $params = $request->getPost();
            if (empty($params['value_id'])) {
                throw new Exception(p__("application",
                    "The feature you are trying to remove doesn't exists."));
            }

            $application = $this->getApplication();
            $appId = $application->getId();

            // Fetching current options of the Application
            $optionValue = (new Application_Model_Option_Value())
                ->find($params['value_id']);

            if (!$optionValue->getId() ||
                (int) $optionValue->getAppId() !== (int) $appId) {
                throw new Exception(p__("application",
                    "The feature you are trying to remove doesn't belong to this Application."));
            }

            $payload = [
                "success" => true,
                "value_id" => $params["value_id"],
                "path" => $optionValue->getPath(null, [], "mobile"),
                "was_folder" => false,
                "was_category" => false,
                "was_feature" => false
            ];

            // Option folder (safer to get the REAL categoryId if there is one)
            if ($optionValue->getFolderCategoryId()) {
                $this->cache_triggers['delete'] = [
                    'tags' => [
                        'feature_paths_valueid_#VALUE_ID#',
                        'assets_paths_valueid_#VALUE_ID#',
                    ],
                ];
                $this->_triggerCache();
                $this->cache_triggers['delete'] = null;

                $optionValue
                    ->setFolderId(null)
                    ->setFolderCategoryId(null)
                    ->setFolderCategoryPosition(null)
                    ->save();

                $payload['was_category'] = true;
                $payload['category'] = [
                    'id' => $params['category_id']
                ];
            } else {
                $this->cache_triggers['delete'] = [
                    'tags' => [
                        'app_#APP_ID#',
                    ],
                ];
                $this->_triggerCache();
                $this->cache_triggers["delete"] = null;

                // Fetching the option
                $option = (new Application_Model_Option())
                    ->find($optionValue->getOptionId());

                $payload['was_feature'] = true;

                if (in_array($optionValue->getCode(), [
                    'folder',
                    'folder_v2',
                ])) {
                    $payload['was_folder'] = true;

                    // As it was a folder/folder_v2 we must extract features from the folder.
                    Application_Model_Option_Value::extractFromFolder($optionValue->getId());
                }

                // Prevents My Account delete when a features still requires it.
                if ($application->usesUserAccount() &&
                    $optionValue->getCode() === "tabbar_account") {

                    $options = $application->getOptions();

                    $canDelete = false;
                    foreach ($options as $option) {
                        if ($option->getCode() === "tabbar_account" &&
                            $optionValue->getValueId() != $option->getValueId()) {
                            // Ok we have another my account, we can delete!
                            $canDelete = true;
                        }
                    }

                    if (!$canDelete) {
                        throw new Exception(__("A feature requires My account, you can't delete it."));
                    }

                    // Removing the option
                    $optionValue->delete();
                } else {
                    // Removing the option
                    $optionValue->delete();
                }

                $payload['use_my_account'] = $this->getApplication()->usesUserAccount();

                if ($option->onlyOnce()) {
                    $payload['page'] = [
                        'id' => $option->getId(),
                        'name' => $option->getName(),
                        'icon_url' => $option->getIconUrl(),
                        'category_id' => $option->getCategoryId()
                    ];
                }
            }
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
                'trace' => $e->getTrace()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function setisactiveAction()
    {

        if ($datas = $this->getRequest()->getPost()) {

            try {
                if (empty($datas['value_id'])) throw new Exception(__('#107: An error occurred while saving'));

                // Récupère les données de l'application pour cette option
                $optionValue = new Application_Model_Option_Value();
                $optionValue->find($datas["value_id"]);
                if (isset($datas["is_active"])) {
                    $optionValue->setIsActive($datas["is_active"]);
                } else if (isset($datas['is_visible'])) {
                    $optionValue->setIsVisible($datas["is_visible"]);
                } else if (isset($datas["is_social_sharing_active"])) {
                    $optionValue->setSocialSharingIsActive($datas["is_social_sharing_active"]);
                } else {
                    throw new Exception(__('#108: An error occurred while saving'));
                }

                $optionValue->save();

                $html = ['success' => 1, 'option_id' => $optionValue->getId(), 'is_folder' => (int)($optionValue->getCode() == 'folder')];

            } catch (\Exception $e) {
                $html = [
//                    'message' => $e->getMessage(),
                    'message' => __('#109: An error occurred while saving'),
                    'message_button' => 1,
                    'message_loader' => 1
                ];
            }

            $this->_sendHtml($html);
        }

    }

    /**
     *
     */
    public function seticonAction()
    {
        try {
            $request = $this->getRequest();
            $datas = $request->getPost();

            $iconSaved = $this->setIcon($datas['icon_id'], $datas['option_value_id']);
            if (!empty($iconSaved)) {

                $valueId = $iconSaved['value_id'];
                $iconId = $iconSaved['icon_id'];
                $iconUrl = $iconSaved['icon_url'];
                $iconUrlColorized = $iconSaved['icon_url_colorized'];
                $iconUrlColorizedApp = $iconSaved['icon_url_colorized_app'];

                $payload = [
                    'success' => true,
                    'value_id' => $valueId,
                    'icon_id' => $iconId,
                    'icon_url' => $iconUrl,
                    'icon_url_colorized' => $iconUrlColorized,
                    'icon_url_colorized_app' => $iconUrlColorizedApp,
                ];

            } else {
                throw new Exception(__('#111: An error occurred while saving'));
            }

        } catch (\Exception $e) {
            $payload = [
                'error' => 'true',
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * @param $iconId
     * @param $valueId
     * @return array|bool
     */
    private function setIcon($iconId, $valueId)
    {
        try {
            $icon = (new Media_Model_Library_Image())
                ->find($iconId);

            if (!$icon->getId()) {
                throw new Exception(__('An error occurred while saving. The selected icon is not valid.'));
            }

            $app = $this->getApplication();

            $specialIcon = false;
            if (in_array($valueId, ['customer_account', 'more_items'])) {
                $specialIcon = true;
            }

            if (!$specialIcon) {
                $optionValue = new Application_Model_Option_Value();
                $optionValue->find($valueId);
                // Tout va bien, on met à jour l'icône pour cette option_value
                $optionValue
                    ->setIconId($icon->getId())
                    ->setIcon(null)/** This is not used! */
                    ->save();
            } else {
                switch ($valueId) {
                    case 'customer_account':
                        $app
                            ->setAccountIconId($iconId)
                            ->save();
                        break;
                    case 'more_items':
                        $app
                            ->setMoreIconId($iconId)
                            ->save();
                        break;
                }
            }

            $iconUrl = $icon->getRelativePath();
            $iconUrlColorized = $icon->getCanBeColorized() ?
                Core_Controller_Default_Abstract::sGetColorizedImage($iconId, '#0099C7') : $iconUrl;
            $iconUrlColorizedApp = $icon->getCanBeColorized() ?
                Core_Controller_Default_Abstract::sGetColorizedImage($iconId, $app->getBlock('tabbar')->getImageColor()) : $iconUrl;

            return [
                'value_id' => $valueId,
                'icon_id' => (integer) $iconId,
                'icon_url' => $iconUrl,
                'icon_url_colorized' => $iconUrlColorized,
                'icon_url_colorized_app' => $iconUrlColorizedApp,
            ];
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     *
     */
    public function deleteiconAction()
    {

        if ($datas = $this->getRequest()->getPost()) {

            try {
                $icon = new Media_Model_Library_Image();
                $icon->find($datas['icon_id']);
                if ($icon->getAppId()) {
                    $icon->delete();
                } else {
                    throw new Exception(__("You may not delete a library icon"));
                }

                $html = [
                    'success' => 1,
                ];
            } catch (\Exception $e) {
                $html = [
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                ];
            }

            $this->_sendHtml($html);

        }

    }

    /**
     *
     */
    public function seticonpositionsAction()
    {

        try {
            $request = $this->getRequest();
            $datas = $request->getPost();
            $application = $this->getApplication();

            if (empty($datas)) {
                throw new Exception(__("An error occurred while sorting your pages. Please try again later."));
            }

            // Récupère les positions
            $positions = $request->getParam("option_value");
            if (empty($positions)) {
                throw new Exception(__("An error occurred while sorting your pages. Please try again later."));
            }

            // Supprime les positions en trop, au cas où...
            $optionValues = $application->getPages();
            $optionValue_ids = [];
            foreach ($optionValues as $optionValue) {
                if ($optionValue->getFolderCategoryId()) {
                    continue;
                }
                $optionValue_ids[] = $optionValue->getId();
            }

            // Met à jour les positions des option_values
            $application->updateOptionValuesPosition($positions);

            // Renvoie OK
            $payload = [
                "success" => true,
                "message" => p__("application", "New position saved."),
            ];

        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function settabbarnameAction()
    {

        if ($datas = $this->getRequest()->getPost()) {

            try {

                // Test les données
                if (empty($datas['option_value_id']) OR empty($datas['tabbar_name'])) {
                    throw new Siberian_Exception(__('An error occurred while saving your page name.'));
                }

                // Charge l'option_value
                $optionValue = new Application_Model_Option_Value();
                $optionValue->setApplication($this->getApplication());
                $optionValue->find($datas['option_value_id']);

                // Test s'il n'y a pas embrouille entre l'id de l'application dans l'option_value et l'id de l'application en session
                if (!$optionValue->getId()) {
                    throw new Siberian_Exception(__('An error occurred while saving your page name.'));
                }


                $option_folder = new Application_Model_Option();
                $option_folder->find(['code' => 'folder']);
                $option_folder_id = $option_folder->getOptionId();

                if ($optionValue->getOptionId() == $option_folder_id) {
                    $folder = new Folder_Model_Folder();
                    $folder->find($datas['option_value_id'], 'value_id');
                    $category = new Folder_Model_Category();
                    $category->find($folder->getRootCategoryId(), 'category_id');
                    $category->setTitle($datas['tabbar_name'])->save();
                }

                /** Privacy policy special case */
                $current_option = new Application_Model_Option();
                $current_option->find($optionValue->getOptionId());
                if ($current_option->getCode() === "privacy_policy") {
                    $this->getApplication()->setPrivacyPolicyTitle($datas['tabbar_name'])->save();
                }

                if (in_array($optionValue->getId(), ['customer_account', 'more_items'])) {
                    $code = $optionValue->getId() == 'customer_account' ? 'tabbar_account_name' : 'tabbar_more_name';
                    $this->getApplication()->setData($code, $datas['tabbar_name'])->save();
                } else {
                    $optionValue->setTabbarName($datas['tabbar_name'])
                        ->save();
                }

                // Renvoie OK
                $payload = [
                    "success" => true,
                    "message" => p__("application", "Feature name saved."),
                ];

            } catch (\Exception $e) {
                $payload = [
                    "error" => true,
                    "message" => $e->getMessage(),
                ];
            }

            $this->_sendJson($payload);

        }

    }

    /**
     *
     */
    public function settabbarsubtitleAction()
    {

        if ($datas = $this->getRequest()->getPost()) {

            try {

                // Test les données
                if (empty($datas['option_value_id'])) {
                    throw new Exception(__('An error occurred while saving your page subtitle.'));
                }

                switch ($datas['option_value_id']) {
                    case "customer_account":
                        $this->getApplication()->setAccountSubtitle($datas['tabbar_subtitle'])->save();
                        break;
                    case "more_items":
                        $this->getApplication()->setMoreSubtitle($datas['tabbar_subtitle'])->save();
                        break;
                    default:
                        // Charge l'option_value
                        $optionValue = new Application_Model_Option_Value();
                        $optionValue->setApplication($this->getApplication());
                        $optionValue->find($datas['option_value_id']);

                        // Test s'il n'y a pas embrouille entre l'id de l'application dans l'option_value et l'id de l'application en session
                        if (!$optionValue->getId()) {
                            throw new Exception(__('An error occurred while saving your page subtitle.'));
                        }

                        $optionValue->setTabbarSubtitle($datas['tabbar_subtitle'])
                            ->save();
                }

                // send ok
                $payload = [
                    "success" => true,
                    "message" => p__("application", "Feature subtitle saved."),
                ];

            } catch (\Exception $e) {
                $payload = [
                    "error" => true,
                    "message" => $e->getMessage(),
                ];
            }

            $this->_sendJson($payload);

        }

    }

    /**
     *
     */
    public function uploadiconAction()
    {
        if ($datas = $this->getRequest()->getPost()) {

            $CanBeColorized = $datas['is_colorized'] == 'true' ? 1 : 0;

            # Disable media optimization for colorizable icons
            if ($CanBeColorized) {
                Siberian_Media::disableTemporary();
            }

            try {
                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->savecrop($datas);
                $app_id = $this->getApplication()->getId();
                if (!empty($file)) {

                    if (in_array($datas['option_id'], ["customer_account", "more_items"])) {
                        $library_name = $datas['option_id'];
                        $library = new Media_Model_Library();
                        $library->find($library_name, "name");

                        $library_id = $library->getId();
                        $option_id = null;
                    } else {
                        $optionValue = new Application_Model_Option_Value();
                        $optionValue->find($datas['option_id']);

                        $library_name = $optionValue->getLibrary()->getName();

                        $library_id = $optionValue->getLibrary()->getId();
                        $option_id = $optionValue->getOptionId();
                    }

                    $formated_library_name = Core_Model_Lib_String::format($library_name, true);
                    $base_lib_path = Media_Model_Library_Image::getBaseImagePathTo($formated_library_name, $app_id);

                    $files = Core_Model_Directory::getTmpDirectory(true) . '/' . $file;


                    if (!is_dir($base_lib_path)) {
                        mkdir($base_lib_path, 0777, true);
                    }
                    if (!copy($files, $base_lib_path . '/' . $file)) {
                        throw new exception(__('An error occurred while saving your picture. Please try againg later.'));
                    } else {

                        $icon_lib = new Media_Model_Library_Image();
                        $icon_lib->setLibraryId($library_id)
                            ->setLink('/' . $formated_library_name . '/' . $file)
                            ->setOptionId($option_id)
                            ->setAppId($app_id)
                            ->setCanBeColorized($CanBeColorized)
                            ->setPosition(0)
                            ->save();

                        if (in_array($datas['option_id'], ["customer_account", "more_items"])) {
                        } else {
                            $optionValue
                                ->setIcon('/' . $formated_library_name . '/' . $file)
                                ->setIconId($icon_lib->getImageId())
                                ->save();
                        }

                        $icon_saved = $this->setIcon($icon_lib->getImageId(), $datas['option_id']);

                        // Charge l'option_value
                        $icon_url = $icon_lib->getUrl();
                        if ($CanBeColorized) {
                            $header_color = $this->getApplication()->getBlock('header')->getColor();
                            $icon_url = $this->getUrl('template/block/colorize', ['id' => $icon_lib->getImageId(), 'color' => str_replace('#', '', $header_color)]);
                        }

                        $icon_color = $this->getApplication()->getBlock('header')->getBackgroundColor();

                        $html = [
                            "success" => true,
                            'file' => '/' . $formated_library_name . '/' . $file,
                            'icon_id' => $icon_lib->getImageId(),
                            'colorizable' => a,
                            'icon_url' => $icon_url,
                            'colored_icon_url' => $this->getUrl('template/block/colorize', ['id' => $icon_lib->getImageId(), 'color' => str_replace('#', '', $icon_color)]),
                            'colored_header_icon_url' => $icon_saved['colored_header_icon_url'],
                            'message' => __("Success."),
                        ];
                    }
                }

            } catch (\Exception $e) {
                $html = [
                    "error" => true,
                    "message" => $e->getMessage(),
                ];
            }

            $this->_sendJson($html);

        }
    }

    /**
     *
     */
    public function setbackgroundimageAction()
    {
        if ($datas = $this->getRequest()->getPost()) {

            try {

                $optionValue = new Application_Model_Option_Value();
                $optionValue->find($datas['option_id']);
                if (!$optionValue->getId()) throw new Exception(__("An error occurred while saving your picture. Please try againg later."));

                // Récupère l'option
                $option = new Application_Model_Option();
                $option->find($optionValue->getOptionId());

                $save_path = '/feature/' . $option->getId() . '/background/';
                $relative_path = Application_Model_Application::getImagePath() . $save_path;
                $folder = Application_Model_Application::getBaseImagePath() . $save_path;

                $datas['dest_folder'] = $folder;

                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->savecrop($datas);

                $optionValue->setBackgroundImage($save_path . $file)->save();

                $datas = [
                    'success' => 1,
                    'file' => $relative_path . $file,
                ];
            } catch (\Exception $e) {
                $datas = [
                    'error' => 1,
                    'message' => $e->getMessage()
                ];
            }

            $this->_sendHtml($datas);
        }
    }

    /**
     *
     */
    public function deletebackgroundimageAction()
    {
        if ($datas = $this->getRequest()->getParams()) {
            try {
                if (empty($datas['value_id'])) throw new Exception(__('An error occurred while deleting your picture'));

                $optionValue = new Application_Model_Option_Value();
                $optionValue->find($datas['value_id']);
                if (!$optionValue->getId()) throw new Exception(__('An error occurred while deleting your picture'));
                $optionValue->setBackgroundImage(null)->save();

                $datas = [
                    'success' => 1,
                    'background_image_url' => $optionValue->reload()->getBackgroundImageUrl()
                ];
            } catch (\Exception $e) {
                $datas = [
                    'error' => 1,
                    'message' => $e->getMessage()
                ];
            }

            $this->_sendHtml($datas);
        }
    }

    /**
     *
     */
    public function setlayoutAction()
    {

        if ($data = $this->getRequest()->getPost()) {

            try {

                if (empty($data["layout_id"]) OR !$this->getCurrentOptionValue() OR $this->getCurrentOptionValue()->getAppId() != $this->getApplication()->getId()) {
                    throw new Exception(__(""));
                }

                $layouts = $this->getCurrentOptionValue()->getLayouts();
                $layout_exists = false;
                foreach ($layouts as $layout) {
                    if ($layout->getCode() == $data["layout_id"]) {
                        $layout_exists = true;
                        break;
                    }
                }

                $this->getCurrentOptionValue()
                    ->setLayoutId($data["layout_id"])
                    ->save();

                $data = [
                    "success" => 1
                ];

            } catch (\Exception $e) {
                $data = [
                    "error" => 1,
                    "message" => $e->getMessage()
                ];
            }

            $this->_sendHtml($data);

        }

    }

    /**
     *
     */
    public function importAction()
    {
        try {

            $data = [
                "success" => 1,
                "message" => __("Import success."),
            ];

            if (empty($_FILES) || empty($_FILES['files']['name'])) {
                throw new Exception("#486-01: No file sent.");
            } else {

                $tmp = Core_Model_Directory::getTmpDirectory(true);
                $tmp_path = $tmp . "/" . $_FILES['files']['name'][0];
                if (!rename($_FILES['files']['tmp_name'][0], $tmp_path)) {
                    throw new Exception("#486-02: Unable to write file.");
                } else {
                    /** Detect if it's a simple feature or a complete template Application */
                    $filetype = pathinfo($tmp_path, PATHINFO_EXTENSION);
                    switch ($filetype) {
                        case "yml":
                            $this->importFeature($tmp_path);

                            $data["message"] = __("Feature successfuly imported.");
                            break;
                        case"zip":
                            if (!$this->getRequest()->getParam("confirm", false)) {
                                $data = [
                                    "confirm" => 1,
                                    "message" => __("Your are about to replace the current application template, colors & features.\nAre you sure ?"),
                                ];
                            } else {
                                $this->importApplication($tmp_path);

                                $data["message"] = __("Application template successfully imported.");
                            }
                            break;
                    }
                }
            }

        } catch (\Exception $e) {
            $data = [
                "error" => 1,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendHtml($data);
    }

    /**
     * @param $path
     * @throws \Exception
     */
    private function importApplication($path)
    {
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
    private function importFeature($path)
    {
        $optionValue_model = new Application_Model_Option_Value();
        $optionValue = $optionValue_model->readOption($path);

        $application = $this->getApplication();
        $existing_options = $application->getOptions();

        if ($optionValue->getCode()) {
            $option_model = new Application_Model_Option();
            $option = $option_model->find($optionValue->getCode(), "code");

            foreach ($existing_options as $existing_option) {
                if (($existing_option->getCode() == $option->getCode()) && $existing_option->getOnlyOnce()) {
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
    public function exportmodalAction()
    {
        $layout = $this->getLayout();
        $layout->setBaseRender('modal', 'html/modal.phtml', 'core_view_default')
            ->setTitle(__('Export / Import'))
            ->setBorderColor("border-blue");
        $layout->addPartial('modal_content', 'admin_view_default', 'application/customization/features/export.phtml');
        $html = ['modal_html' => $layout->render()];

        $this->_sendHtml($html);
    }

    /**
     * Export the application to YAML
     */
    public function exportAction()
    {
        $application = $this->getApplication();
        $options = $application->getOptions();
        $request = $this->getRequest();

        $application_form_export = new Application_Form_Export();
        $application_form_export->addOptions($application);
        $application_form_export->addTemplate();

        # Export as Template
        $is_template = $request->getParam("is_template");
        if ($is_template) {
            $application_form_export->isTemplate();
        }
        $template_name = $request->getParam("template_name", __("MyTemplate"));
        $template_version = $request->getParam("template_version", "1.0");
        $template_description = $request->getParam("template_description", __("My custom template"));

        if ($application_form_export->isValid($request->getParams())) {
            # Folder
            $folder_name = "export-app-" . $application->getId() . "-" . date("Y-m-d_h-i-s") . "-" . uniqid();
            $tmp = Core_Model_Directory::getBasePathTo("var/tmp/");
            $tmp_directory = $tmp . "/" . $folder_name;
            $options_directory = $tmp_directory . "/options";
            mkdir($options_directory, 0777, true);

            $selected_options = $request->getParam("options");
            foreach ($options as $option) {
                if (isset($selected_options[$option->getId()]) && $selected_options[$option->getId()]) {
                    if (Siberian_Exporter::isRegistered($option->getCode())) {
                        $exporter_class = Siberian_Exporter::getClass($option->getCode());
                        if (class_exists($exporter_class) && method_exists($exporter_class, "exportAction")) {
                            $tmp_class = new $exporter_class();
                            $export_type = $selected_options[$option->getId()];
                            $dataset = $tmp_class->exportAction($option, $export_type);
                            File::putContents("{$options_directory}/{$option->getPosition()}-{$option->getCode()}.yml", $dataset);
                        }
                    }
                }
            }

            /** Application */
            $application_dataset = $application->toYml();
            File::putContents("{$tmp_directory}/application.yml", $application_dataset);

            /** package.json */
            $package = [
                "name" => ($is_template) ? $template_name : $folder_name,
                "decription" => ($is_template) ? $template_description : "User exported application template.",
                "version" => ($is_template) ? $template_version : "1.0",
                "flavor" => Siberian_Exporter::FLAVOR,
                "type" => "template",
                "dependencies" => [
                    "system" => [
                        "type" => "SAE",
                        "version" => Siberian_Exporter::MIN_VERSION,
                    ],
                ],
            ];

            File::putContents("{$tmp_directory}/package.json", Siberian_Json::encode($package));

            $zip = Core_Model_Directory::zip($tmp_directory, $tmp . "/" . $folder_name . ".zip");
            $base = Core_Model_Directory::getBasePathTo("");
            $url = $this->getUrl() . str_replace($base, "", $zip);

            if (file_exists($zip)) {
                $data = [
                    "success" => 1,
                    "message" => __("Downloading your package."),
                    "type" => "download",
                    "url" => $url
                ];
            } else {
                $data = [
                    "error" => 1,
                    "message" => __("#498-01: An error occured while exporting your application.")
                ];
            }

        } else {
            $data = [
                "error" => 1,
                "message" => $application_form_export->getTextErrors(),
                "errors" => $application_form_export->getTextErrors(true)
            ];
        }

        $this->_sendHtml($data);
    }

}
