<?php

use Siberian\AdNetwork;

/**
 * Class Application_Customization_Publication_InfosController
 */
class Application_Customization_Publication_InfosController extends Application_Controller_Default
{

    /**
     * @var array
     */
    public $cache_triggers = [
        'save' => [
            'tags' => ['app_#APP_ID#'],
        ],
        'switchtoionic' => [
            'tags' => ['app_#APP_ID#'],
        ],
        'save-admob' => [
            'tags' => ['app_#APP_ID#'],
        ],
    ];

    /**
     *
     */
    public function indexAction()
    {
        $this->loadPartials();

        if ($this->getRequest()->isXmlHttpRequest()) {
            $html = ['html' => $this->getLayout()->getPartial('content_editor')->toHtml()];
            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    /**
     *
     */
    public function saveGeneralInformationAction()
    {
        try {
            $request = $this->getRequest();
            $params = $request->getParams();
            $application = $this->getApplication();

            if (!$application->getId()) {
                throw new \Siberian\Exception(__('This application does not exists.'));
            }

            $form = new Application_Form_GeneralInformation();
            if ($form->isValid($request->getParams())) {
                $application = $this->getApplication();

                $application
                    ->setName($params['name'])
                    ->setDescription($params['description'])
                    ->setKeywords($params['keywords'])
                    ->setMainCategoryId((integer) $params['main_category_id'])
                    ->setSecondaryCategoryId((integer) $params['secondary_category_id'])
                    ->save();

                $payload = [
                    'success' => true,
                    'message' => __('Success.'),
                ];
            } else {
                /** Do whatever you need when form is not valid */
                $payload = [
                    'error' => true,
                    'message' => $form->getTextErrors(),
                    'errors' => $form->getTextErrors(true),
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
    public function saveGeneralInformationSourcesAction()
    {
        try {
            $request = $this->getRequest();
            $params = $request->getParams();
            $application = $this->getApplication();

            if (!$application->getId()) {
                throw new \Siberian\Exception(__('This application does not exists.'));
            }

            $form = new Application_Form_GeneralInformationSources();
            if ($form->isValid($request->getParams())) {
                $application = $this->getApplication();

                $application
                    ->setName($params['name']);

                // Saving only if present!
                if (isset($params['bundle_id'])) {
                    $application
                        ->setBundleId($params['bundle_id']);
                }
                if (isset($params['package_name'])) {
                    $application
                        ->setPackageName($params['package_name']);
                }

                $application
                    ->save();

                $payload = [
                    'success' => true,
                    'message' => __('Success.'),
                ];
            } else {
                /** Do whatever you need when form is not valid */
                $payload = [
                    'error' => true,
                    'message' => $form->getTextErrors(),
                    'errors' => $form->getTextErrors(true),
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

    public function saveAndroidVersionAction()
    {
        try {
            $request = $this->getRequest();
            $params = $request->getParams();
            $application = $this->getApplication();

            if (!$application->getId()) {
                throw new \Siberian\Exception(__('This application does not exists.'));
            }

            $application = $this->getApplication();

            $androidDevice = $application->getAndroidDevice();

            $currentVersion = Application_Model_Device_Abstract::validatedVersion($androidDevice);
            $newVersion = Application_Model_Device_Abstract::validatedVersion($androidDevice, $params['version'], 1);

            // Ask user to confirm intent!
            if ($newVersion < $currentVersion) {
                throw new \Siberian\Exception(p__('application',
                    'The new version must be greater than the current one, please ask your administrator to change it if there was an error.'), 100);
            }

            $androidDevice
                ->setVersion($params['version'])
                ->save();

            $payload = [
                'success' => true,
                'message' => __('Success.'),
            ];

        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function saveAndroidAction()
    {
        try {
            $request = $this->getRequest();
            $params = $request->getParams();
            $application = $this->getApplication();

            if (!$application->getId()) {
                throw new \Siberian\Exception(__('This application does not exists.'));
            }

            $form = new Application_Form_Android();
            if ($form->isValid($request->getParams())) {
                $application = $this->getApplication();

                $application
                    ->setDisableBatteryOptimization(filter_var($params['disable_battery_optimization'], FILTER_VALIDATE_BOOLEAN))
                    ->setDisableLocation(filter_var($params['disable_location'], FILTER_VALIDATE_BOOLEAN))
                    ->save();

                $payload = [
                    'success' => true,
                    'message' => __('Success.'),
                ];
            } else {
                /** Do whatever you need when form is not valid */
                $payload = [
                    'error' => true,
                    'message' => $form->getTextErrors(),
                    'errors' => $form->getTextErrors(true),
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

    public function saveAdmobAction()
    {
        try {
            $request = $this->getRequest();
            $params = $request->getParams();
            $application = $this->getApplication();

            if (!$application->getId()) {
                throw new \Siberian\Exception(__('This application does not exists.'));
            }

            $form = new Application_Form_Admob();
            if ($form->isValid($request->getParams())) {
                $application = $this->getApplication();

                $application
                    ->setUseAds(filter_var($params['use_ads'], FILTER_VALIDATE_BOOLEAN))
                    ->setTestAds(filter_var($params['test_ads'], FILTER_VALIDATE_BOOLEAN))
                    ->save();

                if (AdNetwork::$mediationEnabled && canAccess('editor_publication_admob_mediation')) {
                    $application
                        ->setMediationFacebook(filter_var($params['mediation_facebook'], FILTER_VALIDATE_BOOLEAN))
                        ->setMediationStartapp(filter_var($params['mediation_startapp'], FILTER_VALIDATE_BOOLEAN))
                        ->save();
                }

                $androidDevice = $application->getAndroidDevice();
                $androidDevice
                    ->setAdmobAppId(trim($params['android_admob_app_id']))
                    ->setAdmobId(trim($params['android_admob_id']))
                    ->setAdmobInterstitialId(trim($params['android_admob_interstitial_id']))
                    ->setAdmobType(trim($params['android_admob_type']))
                    ->save();

                $iosDevice = $application->getIosDevice();
                $iosDevice
                    ->setAdmobAppId(trim($params['ios_admob_app_id']))
                    ->setAdmobId(trim($params['ios_admob_id']))
                    ->setAdmobInterstitialId(trim($params['ios_admob_interstitial_id']))
                    ->setAdmobType(trim($params['ios_admob_type']))
                    ->save();

                $payload = [
                    'success' => true,
                    'message' => __('Success.'),
                ];
            } else {
                /** Do whatever you need when form is not valid */
                $payload = [
                    'error' => true,
                    'message' => $form->getTextErrors(),
                    'errors' => $form->getTextErrors(true),
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

    public function saveAppleGoogleAction()
    {
        try {
            $request = $this->getRequest();
            $params = $request->getParams();
            $application = $this->getApplication();

            if (!$application->getId()) {
                throw new \Siberian\Exception(__('This application does not exists.'));
            }

            $form = new Application_Form_AppleGoogle();
            if ($form->isValid($request->getParams())) {
                $application = $this->getApplication();

                $androidDevice = $application->getAndroidDevice();
                $androidDevice
                    ->setDeveloperAccountUsername($params['android_email'])
                    ->setDeveloperAccountPassword($params['android_password'])
                    ->setUseOurDeveloperAccount($params['has_android_account'] === '2')
                    ->save();

                $iosDevice = $application->getIosDevice();
                $iosDevice
                    ->setDeveloperAccountUsername($params['apple_email'])
                    ->setDeveloperAccountPassword($params['apple_password'])
                    ->setUseOurDeveloperAccount($params['has_apple_account'] === '2')
                    ->save();

                $payload = [
                    'success' => true,
                    'message' => __('Success.'),
                ];
            } else {
                /** Do whatever you need when form is not valid */
                $payload = [
                    'error' => true,
                    'message' => $form->getTextErrors(),
                    'errors' => $form->getTextErrors(true),
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
     * @throws Exception
     * @throws Zend_Exception
     * @throws \Siberian\Exception
     */
    public function downloadsourceAction()
    {

        if (__getConfig('is_demo')) {
            // Demo version
            throw new \Siberian\Exception("This is a demo version, the source code can't be downloaded");
        } else {
            if ($data = $this->getRequest()->getParams()) {

                $application = $this->getApplication();

                if (!$application->subscriptionIsActive()) {
                    throw new \Siberian\Exception("You have to purchase the application before downloading the mobile source code.");
                }

                if ($design_code = $this->getRequest()->getParam("design_code")) {
                    $application->setDesignCode($design_code);
                }

                $mainDomain = __get('main_domain');
                if (empty($mainDomain)) {
                    throw new \Siberian\Exception('#908-02: ' .
                        __('Main domain is required, you can set it in <b>Settings > General</b>'));
                }

                $request = $this->getRequest();

                $type = $request->getParam('type');
                $device = ($request->getParam('device_id') == 1) ? 'ios' : 'android';
                $noads = ($request->getParam('no_ads') == 1) ? 'noads' : '';
                $pDesign = $request->getParam('design_code');
                $design_code = (!empty($pDesign)) ? $pDesign : 'ionic';
                $isApkService = $request->getParam('apk', false) === 'apk';

                # ACL Apk user
                if ($type === 'apk' && !$this->getAdmin()->canGenerateApk()) {
                    throw new \Siberian\Exception('You are not allowed to generate APK.');
                }

                if ($type === 'apk' && !$isApkService) {
                    $queue = new Application_Model_ApkQueue();

                    $queue->setAppId($application->getId());
                    $queue->setName($application->getName());
                    $application->getDevice(2)->setStatusId(3)->save();
                } else {
                    $queue = new Application_Model_SourceQueue();

                    $queue->setAppId($application->getId());
                    $queue->setName($application->getName());
                    $queue->setType($device . $noads);
                    $queue->setDesignCode($design_code);
                }

                // New case for source to apk generator!
                if ($device === 'android' &&
                    $isApkService) {
                    $queue->setIsApkService(1);
                    $queue->setApkStatus('pending');
                }

                $queue->setHost($mainDomain);
                $queue->setUserId($this->getSession()->getAdminId());
                $queue->setUserType("admin");
                $queue->save();

                /** Fallback for SAE, or disabled cron */
                $reload = false;
                if (!Cron_Model_Cron::is_active()) {
                    $cron = new Cron_Model_Cron();
                    $value = ($type === 'apk') ? 'apkgenerator' : 'sources';
                    $task = $cron->find($value, 'command');
                    Siberian_Cache::__clearLocks();
                    $siberian_cron = new Siberian_Cron();
                    $siberian_cron->execute($task);
                    $reload = true;
                }

                $more['apk'] = Application_Model_ApkQueue::getPackages($application->getId());
                $more['zip'] = Application_Model_SourceQueue::getPackages($application->getId());
                $more['queued'] = Application_Model_Queue::getPosition($application->getId());
                $more['apk_service'] = Application_Model_SourceQueue::getApkServiceStatus($application->getId());

                $data = [
                    'success' => 1,
                    'message' => __('Application successfully queued for generation.'),
                    'more' => $more,
                    'reload' => $reload,
                ];

            } else {
                $data = [
                    'error' => 1,
                    'message' => __('Missing parameters for generation.'),
                ];
            }

            $this->_sendJson($data);
        }
    }

    public function cancelqueueAction()
    {
        if ($data = $this->getRequest()->getParams()) {

            $application = $this->getApplication();
            $type = $this->getRequest()->getParam("type");
            $device = ($this->getRequest()->getParam("device_id") == 1) ? "ios" : "android";
            $noads = ($this->getRequest()->getParam("no_ads") == 1) ? "noads" : "";

            Application_Model_Queue::cancel($application->getId(), $type, $device . $noads);

            $more["apk"] = Application_Model_ApkQueue::getPackages($application->getId());
            $more["zip"] = Application_Model_SourceQueue::getPackages($application->getId());
            $more["queued"] = Application_Model_Queue::getPosition($application->getId());

            $data = [
                "success" => 1,
                "message" => __("Generation cancelled."),
                "more" => $more,
            ];

        } else {
            $data = [
                "error" => 1,
                "message" => __("Missing parameters for cancellation."),
            ];
        }

        $this->_sendJson($data);
    }

    public function switchtoionicAction()
    {

        if ($data = $this->getRequest()->isPost()) {

            try {

                $application = $this->getApplication();

                $application->setDesignCode(Application_Model_Application::DESIGN_CODE_IONIC);

                if ($design_id = $application->getDesignId()) {

                    $design = new Template_Model_Design();
                    $design->find($design_id);

                    if ($design->getId()) {
                        $application->setDesign($design);
                        Template_Model_Design::generateCss($application, false, false, true);
                    }

                }

                $application->save();

                $html = ['success' => '1'];

            } catch (Exception $e) {
                $html = [
                    'message' => $e->getMessage()
                ];
            }

            $this->_sendJson($html);

        }

    }

}
