<?php

use Vnn\WpApiClient\Auth\WpBasicAuth;
use Vnn\WpApiClient\Http\GuzzleAdapter;
use Vnn\WpApiClient\WpClient;

/**
 * Class Wordpress2_ApplicationController
 */
class Wordpress2_ApplicationController extends Application_Controller_Default
{
    /**
     * Edit the default wordpress settings, url, login, password
     */
    public function editwordpressAction()
    {
        $request = $this->getRequest();
        $params = $request->getPost();

        $form = new Wordpress2_Form_Wordpress();
        if ($form->isValid($params)) {
            // Do whatever you need when form is valid!
            $optionValue = $this->getCurrentOptionValue();
            $valueId = $optionValue->getId();
            $wordpress = (new Wordpress2_Model_Wordpress())
                ->find($params['wordpress2_id']);
            $wordpress->setData($params);

            Siberian_Feature::formImageForOption(
                $optionValue,
                $wordpress,
                $params,
                'picture',
                true
            );

            $wordpress->save();

            /** Update touch date, then never expires (until next touch) */
            $this->getCurrentOptionValue()
                ->touch()
                ->expires(-1);

            // Clear cache on save!
            $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, [
                'wordpress2',
                'value_id_' . $valueId,
            ]);

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

        $this->_sendJson($payload);
    }

    /**
     * Edit the default wordpress settings, url, login, password
     */
    public function editsettingsAction()
    {
        $request = $this->getRequest();
        $params = $request->getPost();

        $form = new Wordpress2_Form_Settings();
        if ($form->isValid($params)) {
            // Do whatever you need when form is valid!
            $optionValue = $this->getCurrentOptionValue();
            $valueId = $optionValue->getId();
            $wordpress = (new Wordpress2_Model_Wordpress())
                ->find($valueId, 'value_id');
            $wordpress->setData($params);
            $wordpress->save();

            /** Update touch date, then never expires (until next touch) */
            $this->getCurrentOptionValue()
                ->touch()
                ->expires(-1);

            // Clear cache on save!
            $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, [
                'wordpress2',
                'value_id_' . $valueId,
            ]);

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

        $this->_sendJson($payload);
    }

    /**
     * Edit the default wordpress settings, url, login, password
     */
    public function editqueryAction()
    {
        $request = $this->getRequest();
        $params = $request->getPost();

        $form = new Wordpress2_Form_Query();
        if ($form->isValid($params)) {
            // Do whatever you need when form is valid!
            $optionValue = $this->getCurrentOptionValue();
            $valueId = $optionValue->getId();
            $wordpressQuery = (new Wordpress2_Model_Query())
                ->find($params['wordpress2_id']);
            $wordpressQuery->setData($params);

            $query = Siberian_Json::encode(
                [
                    'categories' => $params['categories'],
                    'pages' => $params['pages']
                ]
            );
            $wordpressQuery->setQuery($query);

            Siberian_Feature::formImageForOption(
                $optionValue,
                $wordpressQuery,
                $params,
                'picture',
                true
            );

            Siberian_Feature::formImageForOption(
                $optionValue,
                $wordpressQuery,
                $params,
                'thumbnail',
                true
            );

            $wordpressQuery->save();

            /** Update touch date, then never expires (until next touch) */
            $this->getCurrentOptionValue()
                ->touch()
                ->expires(-1);

            // Clear cache on save!
            $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, [
                'wordpress2',
                'value_id_' . $valueId,
            ]);

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

        $this->_sendJson($payload);
    }

    /**
     * partial form loader
     */
    public function loadqueryformAction() {
        $queryId = $this->getRequest()->getParam('query_id');
        $valueId = $this->getCurrentOptionValue()->getId();

        $wordpressQuery = (new Wordpress2_Model_Query())
            ->find($queryId);
        if ($wordpressQuery->getId()) {

            $wordpress = (new Wordpress2_Model_Wordpress())
                ->find($valueId, 'value_id');

            $wordpressApi = new Wordpress2_Model_WordpressApi();
            $wordpressApi->init(
                $wordpress->getData('url'),
                $wordpress->getData('login'),
                $wordpress->getData('password')
            );

            $categories = $wordpressApi->getCategories();
            $pages = $wordpressApi->getAllPages();

            $selectedCategories = Siberian_Json::decode($wordpressQuery->getQuery())['categories'];
            $selectedPages = Siberian_Json::decode($wordpressQuery->getQuery())['pages'];

            $form = new Wordpress2_Form_Query();
            $form->populate($wordpressQuery->getData());
            $form->setValueId($this->getCurrentOptionValue()->getId());

            if ($wordpress->getData('group_queries') !== '1') {
                $form->addSortFields();
            }

            $form
                ->loadCategories($categories, $selectedCategories)
                ->loadPages($pages, $selectedPages)
                ->createSubmit();

            $payload = [
                "success" => true,
                'pages' => $pages,
                'selectedPages' => $selectedPages,
                "form" => $form->render(),
                "message" => __("Success."),
            ];
        } else {
            // Do whatever you need when form is not valid!
            $payload = [
                "error" => true,
                "message" => __("The category you are trying to edit doesn't exists."),
            ];
        }

        $this->_sendJson($payload);
    }
}
