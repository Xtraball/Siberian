<?php

use Siberian\File;

/**
 * Class Customer_AccountController
 */
class Customer_AccountController extends Application_Controller_Default
{

    /**
     * @var array
     */
    public $openActions = [
        [
            'module' => 'customer',
            'controller' => 'account',
            'action' => 'mydata',
        ]
    ];

    /**
     *
     */
    public function mydataAction ()
    {
        $request = $this->getRequest();

        $token = $request->getParam('token', false);
        $page = $request->getParam('page', 'profile');
        $download = filter_var($request->getParam('download', false), FILTER_VALIDATE_BOOLEAN);

        $customer = (new Customer_Model_Customer())
            ->find($token, 'gdpr_token');

        if (empty($token)) {
            $token = false;
        }

        if (!$token || !$customer->getId()) {
            $content = $this->getContent($this->getLoginLayout(), [], 'login');
            echo $content;
            die;
        }

        $customerId = $customer->getId();

        // Fetch module exports!
        $application = (new Application_Model_Application())
            ->find($customer->getAppId());

        $baseData = [
            'download' => (boolean) $download,
            'base_url' => $request->getBaseUrl(),
            'application' => $application,
            'customer' => $customer,
        ];

        $queryParams = [
            'token' => $token,
        ];

        $nav = [
            'profile' => [
                'uri' => '?' . http_build_query($queryParams + ['page' => 'profile']),
                'filename' => './profile.html',
                'label' => __('Profile'),
                'baseData' => $baseData,
                'data' => [],
            ],
        ];

        // Addresses
        $addresses = (new Customer_Model_Address())
            ->findAll($customerId, 'customer_id');
        if ($addresses->count() > 0) {
            $nav['addresses'] = [
                'uri' => '?' . http_build_query($queryParams + ['page' => 'addresses']),
                'filename' => './addresses.html',
                'label' => __('Addresses'),
                'baseData' => $baseData,
                'data' => [
                    'addresses' => $addresses,
                ],
            ];
        }

        // Metadata
        $metadata = $customer->getMetadatas();
        if (!empty($metadata)) {
            $nav['metadata'] = [
                'uri' => '?' . http_build_query($queryParams + ['page' => 'metadata']),
                'filename' => './metadata.html',
                'label' => __('Metadata'),
                'baseData' => $baseData,
                'data' => [
                    'metadata' => $metadata,
                ],
            ];
        }

        $features = $application->getOptions();
        $registeredModules = Siberian_Privacy::getRegisteredModules();
        $remains = [];
        foreach ($features as $feature) {
            if (in_array($feature->getCode(), array_keys($registeredModules))) {
                $remains[] = $registeredModules[$feature->getCode()];
            }
        }

        foreach ($remains as $module) {
            $code = $module['code'];
            $nav[$code] = [
                'uri' => '?' . http_build_query($queryParams + ['page' => $code]),
                'filename' => './' . $code . '.html',
                'label' => $module['label'],
                'templatePath' => $module['templatePath'],
                'baseData' => $baseData,
                'data' => [],
            ];
        }

        if (!$download) {
            $content = $this->getContent($this->getBaseLayout($customer, $baseData), $nav, $page);
            echo $content;
            die;
        } else {
            // Create folder tree & files
            $baseTmp = Core_Model_Directory::getTmpDirectory(true);
            $baseTmp = $baseTmp . '/export-' . uniqid();

            mkdir($baseTmp, 0777, true);

            foreach ($nav as &$link) {
                $link['uri'] = $link['filename'];
            }

            foreach ($nav as $activePage => $page) {
                $filename = $baseTmp . '/' . basename($page['uri']);
                $content = $this->getContent($this->getBaseLayout($customer, $baseData), $nav, $activePage);
                File::putContents($filename, $content);
            }

            $baseZip = $baseTmp . '.zip';

            $result = Core_Model_Directory::zip($baseTmp, $baseZip);

            // Clean-up folder!
            Core_Model_Directory::delete($baseTmp);

            $slug = slugify($customer->getFirstname() . ' ' . $customer->getLastname());

            $this->_download($result, 'export-' . $slug . '.zip', 'application/octet-stream');
        }
    }

    /**
     * @param $customer
     * @return Siberian_Layout
     * @throws Zend_Layout_Exception
     */
    public function getBaseLayout ($customer, $baseData = [])
    {
        $layout = new Siberian\Layout();

        $layout->setViewBasePath($layout->getViewBasePath() . 'app/sae/modules/Customer/resources/desktop/flat/template/customer/');
        $layout->setViewScriptPath($layout->getViewBasePath());

        $layout
            ->setBaseRender('gdpr', 'customer/gdpr/base.phtml', 'core_view_default');

        $layout
            ->getBaseRender()
            ->setCustomer($customer)
            ->addData($baseData);

        return $layout;
    }

    /**
     * @param $customer
     * @return Siberian_Layout
     * @throws Zend_Layout_Exception
     */
    public function getLoginLayout ()
    {
        $layout = new Siberian\Layout();

        $layout->setViewBasePath($layout->getViewBasePath() . 'app/sae/modules/Customer/resources/desktop/flat/template/customer/');
        $layout->setViewScriptPath($layout->getViewBasePath());

        $layout
            ->setBaseRender('gdpr', 'customer/gdpr/login.phtml', 'core_view_default');

        $layout
            ->getBaseRender();

        return $layout;
    }

    /**
     * @param $layout
     * @param $nav
     * @param $navActive
     * @param null $templatePath
     * @return mixed
     */
    private function getContent ($layout, $nav, $navActive)
    {
        $templatePath = 'customer/gdpr/' . $navActive . '.phtml';
        if (array_key_exists('templatePath', $nav[$navActive])) {
            $templatePath = $nav[$navActive]['templatePath'];
        }

        $layout->addPartial(
            'content', 'admin_view_default',
            $templatePath
        );

        $layout
            ->getBaseRender()
            ->setNav($nav)
            ->setNavActive($navActive);

        $layout
            ->getPartial('content')
            ->addData($nav[$navActive]['data'])
            ->addData($nav[$navActive]['baseData']);

        return $layout->render();
    }
}
