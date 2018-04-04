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
            $wordpress = new Wordpress2_Model_Wordpress();
            $wordpress = $wordpress
                ->find($params['wordpress2_id']);
            $wordpress->setData($params);
            $wordpress->save();

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
            $wordpressQuery = (new Wordpress2_Model_Query())
                ->find($params['wordpress2_id']);
            $wordpressQuery->setData($params);

            $query = Siberian_Json::encode(
                [
                    'categories' => $params['categories']
                ]
            );

            $wordpressQuery->setQuery($query);

            $wordpressQuery->save();

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

            $selectedCategories = Siberian_Json::decode($wordpressQuery->getQuery())['categories'];

            $form = new Wordpress2_Form_Query();
            $form->populate($wordpressQuery->getData());
            $form->setValueId($this->getCurrentOptionValue()->getId());
            $form
                ->loadCategories($categories, $selectedCategories)
                ->createSubmit();

            $payload = [
                "success" => true,
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

    /**
     *
     */
    public function testAction ()
    {
        echo '<pre>';
        try {
            $wordpressApi = (new Wordpress2_Model_WordpressApi())
                ->init('https://korben.info/');

            $categories = $wordpressApi->getCategories();

            $categoryParentId = [];
            foreach ($categories as $category) {
                $parent = $category['parent'];

                if (!array_key_exists($parent, $categoryParentId)) {
                    $categoryParentId[$parent] = [];
                }
                $categoryParentId[$parent][] = $category;
            }

            $inputHtml = '
<label style="width: 100%;">
    <input type="checkbox" 
           name="categories[]" 
           value="#VALUE#" 
           color="color-blue" 
           class="sb-form-checkbox color-blue" />
    <span class="sb-checkbox-label">#LABEL#</span>
</label>';

            // Sub function to recursively compute child categories!
            function displayRecursive ($parent, $categoryParentId, $inputHtml) {
                if (array_key_exists($parent, $categoryParentId)) {
                    $currentCategories = $categoryParentId[$parent];

                    $html = '';
                    foreach ($currentCategories as $currentCategory) {
                        $currentParent = $currentCategory['id'];

                        $inputMarkup = str_replace(
                            [
                                '#VALUE#',
                                '#LABEL#'
                            ],
                            [
                                $currentParent,
                                sprintf("%s (%s)", $currentCategory['name'], $currentCategory['slug'])
                            ],
                            $inputHtml);

                        $html .= '<li>' . $inputMarkup;

                        $subs = displayRecursive($currentParent, $categoryParentId, $inputHtml);
                        if (!empty($subs)) {
                            $subs = '<ul>' . $subs . '</ul>';
                        }
                        $html .= $subs . '</li>';
                    }

                    return $html;
                }
                return '';
            }

            echo '<ul>' . displayRecursive(0, $categoryParentId, $inputHtml) . '</ul>';

        } catch (Exception $e) {
            print_r($e->getMessage());
        }
        die();
    }
}
