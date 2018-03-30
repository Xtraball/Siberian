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
                //$posts = $wordpressApi->getPosts($category['id'], 1);
                //$category['_posts'] = $posts;
                $parent = $category['parent'];

                if (!array_key_exists($parent, $categoryParentId)) {
                    $categoryParentId[$parent] = [];
                }
                $categoryParentId[$parent][] = $category;
            }

            // Sub function to recursively compute child categories!
            function displayRecursive ($parent, $categoryParentId) {
                if (array_key_exists($parent, $categoryParentId)) {
                    $currentCategories = $categoryParentId[$parent];

                    $html = '';
                    foreach ($currentCategories as $currentCategory) {
                        $currentParent = $currentCategory['id'];
                        $html .= '<li>' . $currentCategory['name'];
                        $subs = displayRecursive($currentParent, $categoryParentId);
                        $posts = '';
                        /**foreach ($currentCategory['_posts'] as $post) {
                            $posts .= '<li>' . $post['title']['rendered'] . '</li>';
                        }*/
                        if (!empty($subs) || !empty($posts)) {
                            $subs = '<ul>' . $posts . $subs . '</ul>';
                        }
                        $html .= $subs . '</li>';
                    }

                    return $html;
                }
                return '';
            }

            echo '<ul>' . displayRecursive(0, $categoryParentId) . '</ul>';

        } catch (Exception $e) {
            print_r($e->getMessage());
        }
        die();
    }
}
