<?php

class Customer_AccountController extends Application_Controller_Default
{
    /**
     *
     */
    public function mydataAction ()
    {
        $request = $this->getRequest();

        $customerId = $request->getParam('customer_id', false);
        $page = $request->getParam('page', 'profile');
        $download = filter_var($request->getParam('download', false), FILTER_VALIDATE_BOOLEAN);

        $customer = (new Customer_Model_Customer())
            ->find($customerId);

        if (!$customer->getId()) {
            throw new Siberian_Exception(__('No customer.'));
        }
        $customerId = $customer->getId();

        // Fetch module exports!
        $application = (new Application_Model_Application())
            ->find($customer->getAppId());

        $baseData = [
            'download' => $download,
            'base_url' => $request->getBaseUrl(),
            'application' => $application,
            'customer' => $customer,
        ];

        $nav = [
            'profile' => [
                'uri' => '?customer_id=' . $customerId . '&page=profile',
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
                'uri' => '?customer_id=' . $customerId . '&page=addresses',
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
                'uri' => '?customer_id=' . $customerId . '&page=metadata',
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
                'uri' => '?customer_id=' . $customerId . '&page=' . $code,
                'label' => $module['label'],
                'templatePath' => $module['templatePath'],
                'baseData' => $baseData,
                'data' => [],
            ];
        }

        if (!$download) {
            $content = $this->getContent($this->getBaseLayout($customer), $nav, $page);
            echo $content;
            die;
        } else {
            // Create folder tree & files
            $baseTmp = Core_Model_Directory::getTmpDirectory(true);
            $baseTmp = $baseTmp . '/export-' . uniqid();

            mkdir($baseTmp, 0777, true);

            foreach ($nav as &$link) {
                $link['uri'] = preg_replace('#^\?customer_id=[0-9]+\&page=#', '', $link['uri']);
                $link['uri'] = './' . $link['uri'] . '.html';
            }

            foreach ($nav as $activePage => $page) {
                $filename = $baseTmp . '/' . basename($page['uri']);
                $content = $this->getContent($this->getBaseLayout($customer), $nav, $activePage);
                file_put_contents($filename, $content);
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
    public function getBaseLayout ($customer)
    {
        $layout = new Siberian_Layout();

        $layout->setViewBasePath($layout->getViewBasePath() . 'app/sae/modules/Customer/resources/desktop/flat/template/customer/');
        $layout->setViewScriptPath($layout->getViewBasePath());

        $layout
            ->setBaseRender('gdpr', 'customer/gdpr/base.phtml', 'core_view_default');

        $layout
            ->getBaseRender()
            ->setCustomer($customer);

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
