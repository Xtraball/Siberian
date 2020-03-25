<?php

use Weblink_Model_Weblink_Link as ModelLink;
use Weblink\Form\Link as FormLink;
use Weblink\Form\Delete\Link as FormDeleteLink;

use Siberian\Exception;
use Siberian\Feature;
use Siberian\Json;

/**
 * Class Weblink_ApplicationController
 */
class Weblink_ApplicationController extends Application_Controller_Default
{

    /**
     * @var array
     */
    public $cache_triggers = [
        'edit-settings' => [
            'tags' => [
                'homepage_app_#APP_ID#',
            ],
        ],
    ];

    /**
     *
     */
    public function loadFormAction()
    {
        try {
            $optionValue = $this->getCurrentOptionValue();
            $request = $this->getRequest();
            $linkId = $request->getParam('link_id', null);

            $link = (new ModelLink())->find($linkId);

            if (!$link->getId()) {
                throw new Exception(p__('weblink', 'This link entry do not exists!'));
            }

            $form = new FormLink();
            $data = $link->getData();


            // Transforming options before populate
            $_options = $link->getOptions();
            $options = [
                'global' => [],
                'android' => [],
                'ios' => [],
            ];
            foreach ($_options['global'] as $key => $value) {
                $options['global'][$key] = $value;
            }
            foreach ($_options['android'] as $key => $value) {
                $options['android']["android_{$key}"] = ($value === 'yes');
            }
            foreach ($_options['ios'] as $key => $value) {
                $options['ios']["ios_{$key}"] = ($value === 'yes');
            }

            // Restore v1 value!
            if ($data['version'] === '1') {
                if ($data['external_browser'] === '1') {
                    $options['global']['browser'] = 'external_browser';
                } else {
                    $options['global']['browser'] = 'in_app_browser';
                }
            }

            $data['options'] = $options;

            $form->populate($data);
            $form->setAttrib('id', 'weblink-edit-form-id-' . $link->getId());
            $form->setValueId($optionValue->getId());

            $js = '<script type="text/javascript">' .  $form->jsBindings(false)  . '</script>';

            $payload = [
                'success' => true,
                'form' => $form->render() . $js,
                'message' => p__('weblink', 'Success.'),
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
    public function editPostAction()
    {
        try {
            $optionValue = $this->getCurrentOptionValue();
            $code = $optionValue->getCode();
            $request = $this->getRequest();
            $values = $request->getPost();

            if (!$optionValue->getId()) {
                throw new Exception(p__('weblink',"This feature doesn't exists!"));
            }

            if (empty($values)) {
                throw new Exception(p__('weblink', 'Values are required!'));
            }
            $webLink = $optionValue->getObject();

            $lastPosition = (new ModelLink())->getMaxPosition($webLink->getId());

            $form = new FormLink();
            if ($form->isValid($values)) {

                $link = new ModelLink();
                $link->find($values['link_id']);
                $link->setData($values);

                Feature::formImageForOption($optionValue, $link, $values, 'picto', true);

                if (!$link->getId()) {
                    $link->setPosition($lastPosition + 1);
                }

                // Options
                $options = [
                    'global' => [],
                    'android' => [],
                    'ios' => [],
                ];
                $optionsGlobal = $values['options']['global'];
                foreach ($optionsGlobal as $key => $value) {
                    $options['global'][$key] = $value;
                }
                $optionsAndroid = $values['options']['android'];
                foreach ($optionsAndroid as $key => $value) {
                    $options['android'][str_replace('android_', '', $key)] = ($value) ? 'yes' : 'no';
                }
                $optionsIos = $values['options']['ios'];
                foreach ($optionsIos as $key => $value) {
                    $options['ios'][str_replace('ios_', '', $key)] = ($value) ? 'yes' : 'no';
                }

                $link->setOptions($options);
                $link->setWeblinkId($webLink->getId());

                // Fallback for pre-4.18.10 updates
                switch ($values['browser']) {
                    case 'in_app_browser':
                        $link->setInAppBrowser(1);
                        $link->setCustomTab(0);
                        $link->setExternalBrowser(0);
                        break;
                    case 'custom_tab':
                        $link->setInAppBrowser(0);
                        $link->setCustomTab(1);
                        $link->setExternalBrowser(0);
                        break;
                    case 'external_browser':
                        $link->setInAppBrowser(0);
                        $link->setCustomTab(0);
                        $link->setExternalBrowser(1);
                        break;
                }

                // Set version 2 for options!
                $link->setVersion(2);
                $link->save();

                /** Update touch date, then never expires (until next touch) */
                $optionValue
                    ->touch()
                    ->expires(-1);

                // Clear cache on save!
                $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, [
                    'weblink',
                    'value_id_' . $optionValue->getId(),
                ]);

                $message = p__('weblink','Link saved');
                switch ($code) {
                    case 'magento':
                        $message = p__('weblink','Magento saved');
                        break;
                    case 'prestashop':
                        $message = p__('weblink','Prestashop saved');
                        break;
                    case 'shopify':
                        $message = p__('weblink','Shopify saved');
                        break;
                    case 'volusion':
                        $message = p__('weblink','Volusion saved');
                        break;
                    case 'woocommerce':
                        $message = p__('weblink','Woocommerce saved');
                        break;
                }

                $payload = [
                    'success' => true,
                    'message' => $message,
                ];
            } else {
                $payload = [
                    'error' => true,
                    'message' => $form->getTextErrors(),
                    'errors' => $form->getTextErrors(true)
                ];
            }
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
                'exception' => $e->getTrace(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function deleteLinkAction()
    {
        try {
            $request = $this->getRequest();
            $values = $request->getPost();

            $form = new FormDeleteLink();
            if ($form->isValid($values)) {
                $link = new ModelLink();
                $link->find($values['link_id']);

                $valueId = $link->getValueId();

                $link->delete();

                /** Update touch date, then never expires (until next touch) */
                $this->getCurrentOptionValue()
                    ->touch()
                    ->expires(-1);

                // Clear cache on save!
                $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, [
                    'weblink',
                    'value_id_' . $valueId,
                ]);

                $payload = [
                    'success' => true,
                    'message' => p__('weblink', 'Link deleted.'),
                ];
            } else {
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
    public function editSettingsAction()
    {
        $request = $this->getRequest();
        $params = $request->getPost();

        $form = new Weblink_Form_Settings();
        try {
            if ($form->isValid($params)) {
                // Do whatever you need when form is valid!
                $optionValue = $this->getCurrentOptionValue();

                $filteredValues = $form->getValues();

                $filteredValues['showSearch'] = filter_var($filteredValues['showSearch'], FILTER_VALIDATE_BOOLEAN);
                $filteredValues['cardDesign'] = filter_var($filteredValues['cardDesign'], FILTER_VALIDATE_BOOLEAN);

                $optionValue
                    ->setSettings(Json::encode($filteredValues))
                    ->save();

                $webLink = $optionValue->getObject();
                Feature::formImageForOption(
                    $optionValue,
                    $webLink,
                    $filteredValues,
                    'cover',
                    true
                );
                $webLink->save();

                /** Update touch date, then never expires (until next touch) */
                $this->getCurrentOptionValue()
                    ->touch()
                    ->expires(-1);

                $payload = [
                    'success' => true,
                    'message' => __('Success.'),
                ];
            } else {
                $payload = [
                    'error' => true,
                    'message' => $form->getTextErrors(),
                    'errors' => $form->getTextErrors(true)
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
    public function updatePositionsAction()
    {
        try {
            $request = $this->getRequest();
            $optionValue = $this->getCurrentOptionValue();
            $indexes = $request->getParam('indexes', null);

            if (empty($indexes)) {
                throw new Exception(p__('weblink', 'Nothing to re-order!'));
            }

            foreach ($indexes as $index => $linkId) {
                $link = (new ModelLink())
                    ->find($linkId);

                if (!$link->getId()) {
                    throw new Exception(p__('weblink', 'Something went wrong, the link do not exists!'));
                }

                $link
                    ->setPosition($index + 1)
                    ->save();
            }

            /** Update touch date, then never expires (until next touch) */
            $optionValue
                ->touch()
                ->expires(-1);

            // Clear cache on save!
            $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, [
                'weblink',
                'value_id_' . $optionValue->getId(),
            ]);

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
}