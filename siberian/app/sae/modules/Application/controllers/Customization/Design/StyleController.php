<?php

use Siberian\Json;
use Siberian\Feature;
use Siberian\Exception;

/**
 * Class Application_Customization_Design_StyleController
 */
class Application_Customization_Design_StyleController extends Application_Controller_Default
{

    /**
     * @var array
     */
    public $cache_triggers = [
        "formoptions" => [
            "tags" => ["app_#APP_ID#"],
        ],
        "changelayout" => [
            "tags" => ["app_#APP_ID#"],
        ],
        "changelayoutvisibility" => [
            "tags" => ["app_#APP_ID#"],
        ],
        "changeiosstatusbarvisibility" => [
            "tags" => ["app_#APP_ID#"],
        ],
        "changeandroidstatusbarvisibility" => [
            "tags" => ["app_#APP_ID#"],
        ],
        "savehomepage" => [
            "tags" => ["app_#APP_ID#"],
        ],
        "changehomepageslidervisibility" => [
            "tags" => ["app_#APP_ID#"],
        ],
        "changehomepageslidersize" => [
            "tags" => ["app_#APP_ID#"],
        ],
        "changehomepagesliderloopsystem" => [
            "tags" => ["app_#APP_ID#"],
        ],
        "setimagessliderduration" => [
            "tags" => ["app_#APP_ID#"],
        ],
        "savesliderimages" => [
            "tags" => ["app_#APP_ID#"],
        ],
        "reset-font" => [
            "tags" => [
                "css_app_#APP_ID#",
                "app_#APP_ID#"
            ],
        ],
        "save-font" => [
            "tags" => [
                "css_app_#APP_ID#",
                "app_#APP_ID#"
            ],
        ],
        "save-currency" => [
            "tags" => ["app_#APP_ID#"],
        ],
        "save-locale" => [
            "tags" => ["app_#APP_ID#"],
        ],
        "homepageslider" => [
            "tags" => ["homepage_app_#APP_ID#"],
        ],
    ];

    public function editAction()
    {
        $this->loadPartials();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $html = ['html' => $this->getLayout()->getPartial('content_editor')->toHtml()];
            $this->_sendJson($html);
        }
    }

    public function formoptionsAction()
    {
        if ($datas = $this->getRequest()->getPost()) {

            $application = $this->getApplication();
            $layout_id = $application->getLayoutId();
            $layout_model = new Application_Model_Layout_Homepage();
            $layout = $layout_model->find($layout_id);
            $layout_code = $layout->getCode();

            if ($options = Feature::getLayoutOptionsCallbacks($layout_code)) {
                $options = Feature::getLayoutOptionsCallbacks($layout_code);
                $form_class = $options["form"];
                $form = new $form_class($layout);
            } else {
                $form = new Siberian_Form_Options($layout);
            }

            if ($form->isValid($datas)) {

                if (isset($datas["homepage_slider_is_visible"])) {
                    $application->setHomepageSliderIsVisible($datas["homepage_slider_is_visible"]);
                }

                if (isset($datas["layout_visibility"]) &&
                    ($layout->getVisibility() == Application_Model_Layout_Homepage::VISIBILITY_ALWAYS) &&
                    $datas["layout_visibility"] == "1") {
                    $application->setLayoutVisibility(Application_Model_Layout_Homepage::VISIBILITY_ALWAYS);
                } else {
                    $application->setLayoutVisibility(Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE);
                }

                /** If for layout 9 ... */
                if ($layout->getVisibility() == Application_Model_Layout_Homepage::VISIBILITY_TOGGLE) {
                    $application->setLayoutVisibility(Application_Model_Layout_Homepage::VISIBILITY_TOGGLE);
                }

                if (!isset($datas["homepageoptions"])) {

                    // Data processor
                    $layoutOptions = Feature::processDataForLayout($layout_code, $datas, $application);

                    $jsonString = Json::encode($layoutOptions, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    $application->setLayoutOptions($jsonString);
                }

                $application->save();

                $html = [
                    "success" => 1,
                    "message" => __("Options saved"),
                ];
            } else {
                $html = [
                    "error" => 1,
                    "message" => $form->getTextErrors(),
                    "errors" => $form->getTextErrors(true)
                ];
            }

            $this->_sendJson($html);
        }
    }

    public function homepagesliderAction()
    {
        if ($datas = $this->getRequest()->getPost()) {

            $application = $this->getApplication();
            $form = new Application_Form_HomepageSlider();

            if ($form->isValid($datas)) {

                $application->setData($form->getValues());
                $application->save();

                $html = [
                    "success" => 1,
                    "message" => __("Options saved"),
                ];
            } else {
                $html = [
                    "error" => 1,
                    "message" => $form->getTextErrors(),
                    "errors" => $form->getTextErrors(true)
                ];
            }

            $this->_sendJson($html);
        }
    }

    public function behaviorAction()
    {
        if ($datas = $this->getRequest()->getPost()) {

            $application = $this->getApplication();
            $form = new Application_Form_Behavior();

            if ($form->isValid($datas)) {

                $application->setData($form->getValues());
                $application->save();

                $html = [
                    "success" => 1,
                    "message" => __("Options saved"),
                ];
            } else {
                $html = [
                    "error" => 1,
                    "message" => $form->getTextErrors(),
                    "errors" => $form->getTextErrors(true)
                ];
            }


            $this->_sendHtml($html);
        }
    }

    /**
     * Modal dialog for import/export
     */
    public function layoutsAction()
    {
        $layout = $this->getLayout();
        $layout->setBaseRender('modal', 'html/modal.phtml', 'core_view_default')
            ->setTitle(__("Choose your layout"))
            ->setBorderColor("border-red");
        $layout->addPartial('modal_content', 'admin_view_default', 'application/customization/design/style/layouts.phtml');
        $html = ['modal_html' => $layout->render()];

        $this->_sendHtml($html);
    }

    public function changelayoutAction()
    {

        if ($datas = $this->getRequest()->getPost()) {

            try {
                $html = [];

                if (empty($datas['layout_id'])) {
                    throw new Siberian_Exception(__('An error occurred while changing your layout.'));
                }

                $layout = new Application_Model_Layout_Homepage();
                $layout->find($datas['layout_id']);

                if (!$layout->getId()) {
                    throw new Siberian_Exception(__('An error occurred while changing your layout.'));
                }

                $html = ['success' => 1];

                if ($layout->getId() != $this->getApplication()->getLayoutId()) {

                    $visibility = $layout->getVisibility();

                    switch ($layout->getVisibility()) {
                        case Application_Model_Layout_Homepage::VISIBILITY_ALWAYS:
                        case Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE:
                            $visibility = Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE;
                            break;
                        case Application_Model_Layout_Homepage::VISIBILITY_TOGGLE:
                            $visibility = Application_Model_Layout_Homepage::VISIBILITY_TOGGLE;
                            break;
                    }

                    $app = $this->getApplication();

                    if ($layout->getUseHomepageSlider() == 0) {
                        $app->setHomepageSliderIsVisible(0);
                    }

                    $app
                        ->setLayoutId($datas['layout_id'])
                        ->setLayoutVisibility($visibility)
                        ->setLayoutOptions($layout->getOptions())
                        ->save();

                    $html['success'] = 1;
                    $html['reload'] = 1;
                    $html["display_layout_options"] = ($layout->getVisibility() == Application_Model_Layout_Homepage::VISIBILITY_ALWAYS);
                    $html["layout_id"] = $layout->getId();
                    $html["layout_visibility"] = $this->getApplication()->getLayoutVisibility();
                }

            } catch (Exception $e) {
                $html = [
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1,
                ];
            }

            $this->_sendHtml($html);

        }

    }

    public function changelayoutvisibilityAction()
    {

        if ($datas = $this->getRequest()->getPost()) {

            try {
                if (empty($datas['layout_id'])) throw new Exception(__('An error occurred while changing your layout.'));

                $layout = new Application_Model_Layout_Homepage();
                $layout->find($datas['layout_id']);

                if (!$layout->getId()) throw new Exception(__('An error occurred while changing your layout.'));

                $html = [];

                if ($layout->getId() == $this->getApplication()->getLayoutId()) {

                    $html["success"] = 1;

                    $visibility = $layout->getVisibility();

                    if ($layout->getVisibility() == Application_Model_Layout_Homepage::VISIBILITY_ALWAYS) {
                        $visibility = !empty($datas["layout_is_visible_in_all_the_pages"]) ?
                            Application_Model_Layout_Homepage::VISIBILITY_ALWAYS :
                            Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE;
                    }

                    $this->getApplication()
                        ->setLayoutId($datas['layout_id'])
                        ->setLayoutVisibility($visibility)
                        ->save();
                    $html['reload'] = 1;
                    $html["display_layout_options"] = ($layout->getVisibility() == Application_Model_Layout_Homepage::VISIBILITY_ALWAYS);
                    $html["layout_id"] = $layout->getId();
                    $html["layout_visibility"] = $this->getApplication()->getLayoutVisibility();
                }

            } catch (Exception $e) {
                $html = [
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1,
                ];
            }

            $this->_sendHtml($html);

        }

    }

    /**
     * @deprecated only for Siberian Design
     */
    public function changeiosstatusbarvisibilityAction()
    {

        try {
            $html = [];

            $is_hidden = $this->getRequest()->getPost('ios_status_bar_is_hidden') ? 1 : 0;

            $this->getApplication()
                ->setIosStatusBarIsHidden($is_hidden)
                ->save();

            $html["success"] = 1;
            $html['reload'] = 1;

        } catch (Exception $e) {
            $html = [
                'message' => __('An error occurred while hidding the iOS Status Bar.'),
                'message_button' => 1,
                'message_loader' => 1,
            ];
        }

        $this->_sendHtml($html);

    }

    /**
     * @deprecated only for Siberian Design
     */
    public function changeandroidstatusbarvisibilityAction()
    {

        try {
            $html = [];

            $is_hidden = $this->getRequest()->getPost('android_status_bar_is_hidden') ? 1 : 0;

            $this->getApplication()
                ->setAndroidStatusBarIsHidden($is_hidden)
                ->save();

            $html["success"] = 1;
            $html['reload'] = 1;

        } catch (Exception $e) {
            $html = [
                'message' => __('An error occurred while hidding the Android Status Bar.'),
                'message_button' => 1,
                'message_loader' => 1,
            ];
        }

        $this->_sendHtml($html);

    }

    /**
     * @deprecated only for Siberian Design
     */
    public function mutualizebackgroundimagesAction()
    {

        try {
            $this->getApplication()
                ->setUseHomepageBackgroundImageInSubpages((int)$this->getRequest()->getPost('use_homepage_background_image_in_subpages', 0))
                ->save();

            $html = ['success' => '1'];

        } catch (Exception $e) {
            $html = ['message' => $e->getMessage()];
        }

        $this->_sendHtml($html);
    }

    public function savehomepageAction()
    {

        if ($datas = $this->getRequest()->getPost()) {

            try {

                $application = $this->getApplication();
                $filetype = $this->getRequest()->getParam('filetype');
                $relative_path = '/' . $this->getApplication()->getId() . '/homepage_image/' . $filetype . '/';
                $folder = Application_Model_Application::getBaseImagePath() . $relative_path;
                $datas['dest_folder'] = $folder;
                $datas['ext'] = 'jpg';

                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->savecrop($datas);

                switch ($filetype) {
                    case "standard":
                        $application->setBackgroundImage($relative_path . $file);
                        break;
                    case "hd":
                        $application->setBackgroundImageHd($relative_path . $file);
                        break;
                    case "tablet":
                        $application->setBackgroundImageTablet($relative_path . $file);
                        break;
                    case "landscape_standard":
                        $application->setBackgroundImageLandscape($relative_path . $file);
                        $application->setUseLandscape(true);
                        break;
                    case "landscape_hd":
                        $application->setBackgroundImageLandscapeHd($relative_path . $file);
                        $application->setUseLandscape(true);
                        break;
                    case "landscape_tablet":
                        $application->setBackgroundImageLandscapeTablet($relative_path . $file);
                        $application->setUseLandscape(true);
                        break;
                    case 'unified':
                        $application->setBackgroundImageUnified($relative_path . $file);
                        break;
                }

                $application->save();

                $url = $application->getHomepageBackgroundImageUrl($filetype);

                $html = [
                    'success' => 1,
                    'file' => $url
                ];
            } catch (Exception $e) {
                $html = [
                    'error' => 1,
                    'message' => $e->getMessage()
                ];
            }

            $this->_sendJson($html);
        }
    }

    public function deletehomepageAction()
    {
        $filetype = $this->_request->getparam('filetype');
        try {
            if ($filetype == 'bg') {
                $this->getApplication()->setHomepageBackgroundImageRetinaLink(null);
                $this->getApplication()->setHomepageBackgroundImageLink(null);
                $this->getApplication()->setHomepageBackgroundImageId(null);
            } else if ($filetype == 'icon') {
                $this->getApplication()->setHomepageLogoLink(null);
            }
            $this->getApplication()->save();
            $html = [
                'success' => '1'
            ];
        } catch (Exception $e) {
            $html = [
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($html);
    }

    /**
     * HOMEPAGE SLIDER
     */
    public function changehomepageslidervisibilityAction()
    {

        if ($datas = $this->getRequest()->getPost()) {
            try {
                $this->getApplication()
                    ->setHomepageSliderIsVisible($datas['slider_is_visible'])
                    ->save();

                $html = [
                    "success" => 1,
                    "reload" => 1
                ];
            } catch (Exception $e) {
                $html = [
                    'message' => $e->getMessage(),
                ];
            }

            $this->_sendHtml($html);
        }
    }

    /**
     *
     */
    public function changehomepageslidersizeAction()
    {

        if ($datas = $this->getRequest()->getPost()) {
            try {
                $this->getApplication()
                    ->setHomepageSliderSize($datas['slider_size'])
                    ->save();

                $html = [
                    "success" => 1,
                    "reload" => 1
                ];
            } catch (Exception $e) {
                $html = [
                    'message' => $e->getMessage(),
                ];
            }

            $this->_sendHtml($html);
        }
    }

    /**
     *
     */
    public function changehomepagesliderloopsystemAction()
    {

        if ($datas = $this->getRequest()->getPost()) {
            try {
                $this->getApplication()
                    ->setHomepageSliderLoopAtBeginning($datas['slider_loop_at_beginning'])
                    ->save();

                $html = [
                    "success" => 1,
                    "reload" => 1
                ];
            } catch (Exception $e) {
                $html = [
                    'message' => $e->getMessage(),
                ];
            }

            $this->_sendHtml($html);
        }
    }

    /**
     *
     */
    public function setimagessliderdurationAction()
    {

        if ($datas = $this->getRequest()->getPost()) {

            try {
                if (isset($datas['slider_image_duration'])) {
                    if (is_numeric($datas['slider_image_duration'])) {
                        $application = $this->getApplication();
                        $application->setHomepageSliderDuration($datas['slider_image_duration'])->save();
                    } else {
                        throw new Exception(__('Please enter a number for the duration.'));
                    }
                }

                $html = [
                    'success' => 1,
                    'reload' => 1
                ];
            } catch (Exception $e) {
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
    public function savesliderimagesAction()
    {

        if ($datas = $this->getRequest()->getPost()) {

            try {
                $url = "";
                $image_id = null;

                $application = $this->getApplication();

                $relative_path = '/' . $application->getId() . '/slider_images/';
                $folder = Application_Model_Application::getBaseImagePath() . $relative_path;
                $datas['dest_folder'] = $folder;

                $uploader = new Core_Model_Lib_Uploader();
                if ($file = $uploader->savecrop($datas)) {
                    $url = Application_Model_Application::getImagePath() . $relative_path . $file;

                    $library = new Media_Model_Library();
                    $library->find($application->getHomepageSliderLibraryId());

                    if (!$library->getId()) {
                        $library->setName('homepage_slider_' . $application->getId())->save();
                        $application->setHomepageSliderLibraryId($library->getId())->save();
                    }

                    $image = new Media_Model_Library_Image();
                    $image->setLibraryId($library->getId())
                        ->setLink($url)
                        ->setAppId($application->getId())
                        ->save();

                    $image_id = $image->getId();
                }

                $html = [
                    'success' => 1,
                    'file' => [
                        "id" => $image_id,
                        "url" => $url
                    ]
                ];

            } catch (Exception $e) {
                $html = [
                    'error' => 1,
                    'message' => $e->getMessage()
                ];
            }

            $this->_sendHtml($html);
        }
    }

    /**
     *
     */
    public function setsliderimagepositionsAction()
    {

        try {

            $image_positions = $this->getRequest()->getParam('slider_image');
            if (empty($image_positions)) throw new Exception(__('An error occurred while sorting your slider images. Please try again later.'));

            $image = new Media_Model_Library_Image();
            $image->updatePositions($image_positions);

            $html = [
                'success' => 1,
                'reload' => 1
            ];

        } catch (Exception $e) {
            $html = [
                'message' => $e->getMessage(),
                'message_button' => 1,
                'message_loader' => 1
            ];
        }

        $this->_sendJson($html);
    }

    /**
     *
     */
    public function deletesliderimageAction()
    {

        try {

            $image_id = $this->_request->getparam('image_id');

            $library_image = new Media_Model_Library_Image();
            $library_image->find($image_id);

            $file = path($library_image->getLink());

            $library_image->delete();

            if (file_exists($file)) {
                if (unlink($file)) {
                    $html = [
                        'success' => 1,
                        'reload' => 1
                    ];
                } else {
                    throw new Exception(__("An error occurred while deleting your picture"));
                }
            }

        } catch (\Exception $e) {
            $html = ['message' => $e->getMessage()];
        }

        $this->_sendJson($html);
    }

    /**
     *
     */
    public function resetFontAction()
    {
        try {
            $application = $this->getApplication();
            $application
                ->setFontFamily(null)
                ->save();

            Template_Model_Design::generateCss($application, false, false, true);

            $payload = [
                'success' => true,
                'message' => p__('application', 'Font reset!'),
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
    public function saveFontAction()
    {
        try {
            $request = $this->getRequest();
            $fontFamily = $request->getParam("font", null);
            $fontFamily = preg_replace("#https://fonts.googleapis.com/css\?family=#i", "", $fontFamily);

            if (empty($fontFamily)) {
                throw new \Siberian\Exception(__("Missing font family!"));
            }

            $fontFamily = trim($fontFamily);

            // Testing google font!
            \Siberian_Request::get("https://fonts.googleapis.com/css?family=" . str_replace(" ", "+", $fontFamily));
            if (\Siberian_Request::$statusCode === 400) {
                throw new \Siberian\Exception(__("This Google Font do not exists!"));
            }

            $application = $this->getApplication();
            $application
                ->setFontFamily(str_replace(" ", "+", $fontFamily))
                ->save();

            $result = Template_Model_Design::generateCss($application, false, false, true);

            if ($result === false) {
                $payload = [
                    'success' => false,
                    'message' => __("Your SCSS seems invalid!"),
                ];
            } else {
                $payload = [
                    'success' => true,
                    'message' => __("Font saved!"),
                ];
            }
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
    public function saveLocaleAction()
    {
        try {
            $request = $this->getRequest();
            $datas = $request->getPost();

            if (empty($datas["locale"])) {
                throw new Exception(p__("application", "Invalid locale."));
            }

            $this
                ->getApplication()
                ->setLocale($datas["locale"])
                ->save();

            $payload = [
                "success" => true,
                "message" => p__("application", "Locale saved.")
            ];
        } catch (Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function saveCurrencyAction()
    {
            try {
            $request = $this->getRequest();
            $datas = $request->getPost();

            if (empty($datas["currency"])) {
                throw new Exception(p__("application", "Invalid currency."));
            }

            $this
                ->getApplication()
                ->setCurrency($datas["currency"])
                ->save();

            $payload = [
                "success" => true,
                "message" => p__("application", "Currency saved.")
            ];
            } catch (Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
            }

        $this->_sendJson($payload);
    }

    /**
     * Upgrade app to unified homepage/splashscreen
     */
    public function upgradeunifiedAction ()
    {
        try {
            $this
                ->getApplication()
                ->setSplashVersion(2)
                ->save();

            $payload = [
                'success' => true,
                'message' => __('Success'),
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
     * @throws \Siberian\Exception
     */
    public function downloadhomepageAction ()
    {
        // Create temp folder
        $application = $this->getApplication();
        $appId = $application->getId();

        $zipFile = path('/var/tmp/homepages-' . $appId . '.zip');
        $tmp = path('/var/tmp/homepages-' . $appId);
        mkdir($tmp, 0777, true);

        $allImages = [];

        $allImages['homepage'] = path('/images/application' . $application->getData('background_image'));
        $allImages['homepage_hd'] = path('/images/application' . $application->getData('background_image_hd'));
        $allImages['homepage_tablet'] = path('/images/application' . $application->getData('background_image_tablet'));
        $allImages['homepage_landscape'] = path('/images/application' . $application->getData('background_image_landscape'));
        $allImages['homepage_landscape_hd'] = path('/images/application' . $application->getData('background_image_landscape_hd'));
        $allImages['homepage_landscape_tablet'] = path('/images/application' . $application->getData('background_image_landscape_tablet'));
        $allImages['icon'] = path('/images/application' . $application->getData('icon'));
        $allImages['android_push_icon'] = path('/images/application' . $application->getData('android_push_icon'));
        $allImages['splashscreen'] = path('/images/application' . $application->getData('startup_image'));
        $allImages['splashscreen_retina'] = path('/images/application' . $application->getData('startup_image_retina'));
        $allImages['splashscreen_iphone6'] = path('/images/application' . $application->getData('startup_image_iphone_6'));
        $allImages['splashscreen_iphone6_plus'] = path('/images/application' . $application->getData('startup_image_iphone_6_plus'));
        $allImages['splashscreen_ipad_retina'] = path('/images/application' . $application->getData('startup_image_ipad_retina'));
        $allImages['splashscreen_iphone_x'] = path('/images/application' . $application->getData('startup_image_iphone_x'));

        foreach ($allImages as $filename => $image) {
            if (is_file($image)) {
                $ext = pathinfo(basename($image), PATHINFO_EXTENSION);
                copy($image, $tmp . '/' . $filename . '.' . $ext);
            }
        }

        // Zip!
        Core_Model_Directory::zip($tmp, $zipFile);

        // Then clear!
        Core_Model_Directory::delete($tmp);

        $this->_download($zipFile, 'homepages-' . $appId . '.zip');
    }

}
