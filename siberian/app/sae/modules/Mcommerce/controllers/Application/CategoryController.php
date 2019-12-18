<?php

/**
 * Class Mcommerce_Application_CategoryController
 */
class Mcommerce_Application_CategoryController extends Application_Controller_Default
{

    /**
     * @var array
     */
    public $cache_triggers = [
        'update-positions' => [
            'tags' => [
                'feature_paths_valueid_#VALUE_ID#',
                'assets_paths_valueid_#VALUE_ID#',
                'homepage_app_#APP_ID#',
            ],
        ],
        'update-category' => [
            'tags' => [
                'feature_paths_valueid_#VALUE_ID#',
                'assets_paths_valueid_#VALUE_ID#',
                'homepage_app_#APP_ID#',
            ],
        ],
        'create-category' => [
            'tags' => [
                'feature_paths_valueid_#VALUE_ID#',
                'assets_paths_valueid_#VALUE_ID#',
                'homepage_app_#APP_ID#',
            ],
        ],
        'delete-category' => [
            'tags' => [
                'feature_paths_valueid_#VALUE_ID#',
                'assets_paths_valueid_#VALUE_ID#',
                'homepage_app_#APP_ID#',
            ],
        ],
    ];

    /**
     * Creates a new category, then send the placeholder
     */
    public function createCategoryAction()
    {
        $request = $this->getRequest();
        $parentId = $request->getParam('parentId');
        $valueId = $request->getParam('valueId');
        $optionValue = $this->getCurrentOptionValue();

        $position = (new Catalog_Model_Category())
            ->findLastPosition($optionValue->getId(), null);

        $category = new Catalog_Model_Category();
        $category
            ->setName(p__('m_commerce', 'New category'))
            ->setValueId($valueId)
            ->setPosition($position)
            ->setParentId($parentId)
            ->setIsActive(1)
            ->save();

        $placeholder = '
            <li class="category-sortable" 
                parentId="' . $category->getParentId() . '"
                typeName="category"
                rel="' . $category->getId() . '">
                <span class="category-hover">
                    <i class="category-sortable-handle fa fa-arrows"></i>
                    <input class="category-title input-flat" 
                           rel="' . $category->getId() . '"
                           name="categoryName" value="' . $category->getName() . '" />
                    <span class="category-product-count"></span>
                    <i class="category-delete fa fa-remove pull-right" 
                       parentId="' . $category->getParentId() . '"
                       rel="' . $category->getId() . '"></i>
                </span>
            </li>';

        $payload = [
            'success' => true,
            'placeholder' => $placeholder,
            'message' => __('Success.'),
        ];

        $this->_sendJson($payload);
    }

    /**
     * Update category/product positions!
     */
    public function updatePositionsAction()
    {
        $request = $this->getRequest();
        $positions = $request->getParam('positions');

        try {
            $currentCategory = $positions['category'];
            $currentPositions = $positions['positions'];
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
        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Update a category title/name only!
     */
    public function updateCategoryAction()
    {
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
        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Delete a category
     */
    public function deleteCategoryAction()
    {
        $request = $this->getRequest();

        try {
            $categoryId = $request->getParam('categoryId');
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
        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }
}