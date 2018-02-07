<?php

class Catalog_ApplicationController extends Application_Controller_Default {

    /**
     * @var array
     */
    public $cache_triggers = [
        'updatepositions' => [
            'tags' => [
                'feature_paths_valueid_#VALUE_ID#',
                'assets_paths_valueid_#VALUE_ID#',
                'homepage_app_#APP_ID#',
            ],
        ],
        'updatecategory' => [
            'tags' => [
                'feature_paths_valueid_#VALUE_ID#',
                'assets_paths_valueid_#VALUE_ID#',
                'homepage_app_#APP_ID#',
            ],
        ],
        'deletecategory' => [
            'tags' => [
                'feature_paths_valueid_#VALUE_ID#',
                'assets_paths_valueid_#VALUE_ID#',
                'homepage_app_#APP_ID#',
            ],
        ],
    ];

    /**
     *
     */
    public function updatepositionsAction() {
        $request = $this->getRequest();
        $positions = $request->getParam('positions');

        $currentCategory = $positions['category'];
        $currentPositions = $positions['positions'];

        try {
            $category = (new Catalog_Model_Category())
                ->find($currentCategory['categoryId']);

            if ($category->getId()) {
                if ($currentCategory['parentId'] === 'root') {
                    $currentCategory['parentId'] = null;
                }

                $category
                    ->setParentId($currentCategory['parentId'])
                    ->save();

                // Update all siblings positions!
                foreach ($currentPositions as $position => $categoryId) {
                    (new Catalog_Model_Category())
                        ->find($categoryId)
                        ->setPosition($position + 1)
                        ->save();
                }

                $this->getCurrentOptionValue()
                    ->touch()
                    ->expires(-1);

                $payload = [
                    'success' => true,
                    'message' => __('Catalog is up-to-date.')
                ];
            } else {
                $payload = [
                    'error' => true,
                    'message' => __('Catalog not found!')
                ];
            }
        } catch(Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function updatecategoryAction() {
        $request = $this->getRequest();

        $categoryId = $request->getParam('categoryId');
        $title = $request->getParam('title');

        try {
            $category = (new Catalog_Model_Category())
                ->find($categoryId);

            if ($category->getId()) {
                $category
                    ->setName($title)
                    ->save();

                $this->getCurrentOptionValue()
                    ->touch()
                    ->expires(-1);

                $payload = [
                    'success' => true,
                    'message' => __('Category is up-to-date.')
                ];
            } else {
                $payload = [
                    'error' => true,
                    'message' => __('Category not found!')
                ];
            }
        } catch(Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function deletecategoryAction() {
        $request = $this->getRequest();

        $categoryId = $request->getParam('categoryId');

        try {
            $category = (new Catalog_Model_Category())
                ->find($categoryId);

            if ($category->getId()) {
                $category
                    ->setIsDeleted(1)
                    ->save();

                $this->getCurrentOptionValue()
                    ->touch()
                    ->expires(-1);

                $payload = [
                    'success' => true,
                    'message' => __('Category has been deleted.')
                ];
            } else {
                $payload = [
                    'error' => true,
                    'message' => __('Category not found!')
                ];
            }
        } catch(Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }
}