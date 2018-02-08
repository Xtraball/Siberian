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
        'createcategory' => [
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

    public function loadproductformAction () {
        $request = $this->getRequest();
        $parentId = $request->getParam('parentId', null);
        $productId = $request->getParam('productId', false);
        $valueId = $request->getParam('valueId');

        $form = new Catalog_Form_Product();
        $product = (new Catalog_Model_Product)
            ->find($productId);
        if ($product->getId()) {
            $formats = (new Catalog_Model_Product_Format_Option())
                ->findByProductId($product->getId());
            $form->populate($product->getData());

            $formatPayload = [];
            foreach ($formats as $format) {
                $formatPayload[] = [
                    'title' => addslashes($format->getTitle()),
                    'price' => $format->getPrice(),
                ];
            }

            $payload = [
                'success' => true,
                'form' => $form->render(),
                'formats' => $formatPayload,
                'message' => __('Success.')
            ];
        } else {
            $form->populate([
                'name' => __('New product'),
                'category_id' => $parentId,
                'value_id' => $valueId,
            ]);

            $payload = [
                'success' => true,
                'form' => $form->render(),
                'message' => __('Success.')
            ];
        }

        $this->_sendJson($payload);
    }

    public function editproductAction() {
        try {
            $request = $this->getRequest();
            $productId = $request->getParam('product_id');
            $optionValue = $this->getCurrentOptionValue();
            $values = $request->getPost();

            $form = new Catalog_Form_Product();
            if ($form->isValid($values)) {

                $product = (new Catalog_Model_Product())
                    ->find($productId);

                // Always delete all formats! then Add them again if needed!
                $product->deleteAllFormats();

                $formats = $values['format'];
                unset($values['format']);

                $product->setData($values);
                $product->save();

                $hasFormat = false;
                if (filter_var($values['enable_format'], FILTER_VALIDATE_BOOLEAN)) {
                    foreach ($formats as $format) {
                        if (!empty($format['title']) && !empty($format['price'])) {
                            $formatOption = new Catalog_Model_Product_Format_Option();
                            $formatOption
                                ->setProductId($product->getId())
                                ->setTitle($format['title'])
                                ->setPrice($format['price'])
                                ->save();
                            $hasFormat = true;
                        }
                    }
                }

                // Set as format type!
                if ($hasFormat) {
                    $product->setType('format');
                }

                if ($values['picture'] === '_delete_') {
                    $product->setData('picture', '');
                } else if (file_exists(Core_Model_Directory::getBasePathTo('images/application' . $values['picture']))) {
                    // Nothing changed, skip!
                } else {
                    $background = Siberian_Feature::moveUploadedFile(
                        $this->getCurrentOptionValue(),
                        Core_Model_Directory::getTmpDirectory() . '/' . $values['picture']);
                    $product->setData('picture', $background);
                }

                $product->save();

                // Update touch date, then never expires (until next touch)!
                $optionValue
                    ->touch()
                    ->expires(-1);

                $productLine = '
            <li class="category-sortable" 
                parentId="' . $product->getCategoryId() . '"
                typeName="product"
                rel="' . $product->getProductId() . '">
                <span class="category-hover">
                    <i class="category-sortable-handle fa fa-arrows"></i>
                    <span class="category-title" 
                           rel="' . $product->getProductId() . '"
                           name="categoryName" value="">' . $product->getName() . '</span>
                    <span class="category-product-count"></span>
                    <i class="category-delete fa fa-remove pull-right" 
                       parentId="' . $product->getCategoryId() . '"
                       typeName="product"
                       rel="' . $product->getProductId() . '"></i>
                    <i class="category-edit-product fa fa-pencil pull-right" 
                       parentId="' . $product->getCategoryId() . '"
                       typeName="product"
                       rel="' . $product->getProductId() . '"></i>
                </span>
            </li>';

                $payload = [
                    'success' => true,
                    'productId' => $product->getId(),
                    'product' => $product->getData(),
                    'categoryId' => $product->getCategoryId(),
                    'productLine' => $productLine,
                    'message' => __('Success.'),
                ];
            } else {
                $payload = [
                    'error' => true,
                    'message' => $form->getTextErrors(),
                    'errors' => $form->getTextErrors(true)
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
     * Creates a new category, then send the placeholder
     */
    public function createcategoryAction() {
        $request = $this->getRequest();
        $valueId = $request->getParam('valueId');
        $optionValue = $this->getCurrentOptionValue();

        $position = (new Catalog_Model_Category())
            ->findLastPosition($optionValue->getId(), null);

        $category = new Catalog_Model_Category();
        $category
            ->setName(__('New category'))
            ->setValueId($valueId)
            ->setPosition($position)
            ->setParentId(null)
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
                    <i class="category-add-product fa fa-cart-plus pull-right" 
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
    public function updatepositionsAction() {
        $request = $this->getRequest();
        $positions = $request->getParam('positions');
        $typeName = $request->getParam('typeName');

        try {
            switch ($typeName) {
                case 'category':
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
                    break;
                case 'product':
                    $currentProduct = $positions['product'];
                    $currentPositions = $positions['positions'];
                    $product = (new Catalog_Model_Product())
                        ->find($currentProduct['productId']);

                    if ($product->getId()) {
                        if ($currentProduct['parentId'] === 'root') {
                            $currentProduct['parentId'] = null;
                        }

                        $product
                            ->setCategoryId($currentProduct['parentId'])
                            ->save();

                        // Update all siblings positions!
                        foreach ($currentPositions as $position => $productId) {
                            (new Catalog_Model_Product())
                                ->find($productId)
                                ->setPosition($position + 1)
                                ->save();
                        }

                        $this->getCurrentOptionValue()
                            ->touch()
                            ->expires(-1);

                        $payload = [
                            'success' => true,
                            'message' => __('Product is up-to-date.')
                        ];
                    } else {
                        $payload = [
                            'error' => true,
                            'message' => __('Product not found!')
                        ];
                    }
                    break;
                default:
                    throw new Siberian_Exception(__('Given `typeName` not allowed!'));
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
     * Update a category title/name only!
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
     * Delete a category or a product
     */
    public function deletecategoryAction() {
        $request = $this->getRequest();
        $typeName = $request->getParam('typeName');

        try {
            switch ($typeName) {
                case 'category':
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
                    break;
                case 'product':
                    $productId = $request->getParam('productId');
                    $product = (new Catalog_Model_Product())
                        ->find($productId);

                    if ($product->getId()) {
                        $product
                            ->setIsDeleted(1)
                            ->save();

                        $this->getCurrentOptionValue()
                            ->touch()
                            ->expires(-1);

                        $payload = [
                            'success' => true,
                            'message' => __('Product has been deleted.')
                        ];
                    } else {
                        $payload = [
                            'error' => true,
                            'message' => __('Product not found!')
                        ];
                    }
                    break;
                default:
                    throw new Siberian_Exception(__('Given `typeName` not allowed!'));
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