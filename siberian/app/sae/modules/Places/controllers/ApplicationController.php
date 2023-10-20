<?php

/**
 * Class Places_ApplicationController
 *
 * @version 4.15.7
 */
class Places_ApplicationController extends Application_Controller_Default
{

    /**
     * @var array
     */
    public $cache_triggers = [
        'searchsettings' => [
            'tags' => [
                'homepage_app_#APP_ID#',
            ],
        ],
        'rank' => [
            'tags' => [
                'homepage_app_#APP_ID#',
            ],
        ],
        'edit-category' => [
            'tags' => [
                'homepage_app_#APP_ID#',
            ],
        ],
        'edit-settings' => [
            'tags' => [
                'homepage_app_#APP_ID#',
            ],
        ],
    ];

    /**
     *
     */
    public function editAction()
    {
        try {
            $optionValue = $this->getCurrentOptionValue();
            $pages = (new Cms_Model_Application_Page())
                ->findAllOrderedByRank($optionValue->getId());

            $mustUpdate = false;
            foreach ($pages as $page) {
                if ($page->getPlaceVersion() != 2) {
                    $mustUpdate = true;
                    break;
                }
            }

            $this->view->must_update = $mustUpdate;
        } catch (\Exception $e) {
            // Nope!
        }

        parent::editAction();
    }

    /**
     * Remastered edit post, with new models & rules
     */
    public function editpostv2Action() {
        try {
            $values = $this->getRequest()->getParams();
            $option_value = $this->getCurrentOptionValue();

            $form = new Cms_Form_Cms();
            if($form->isValid($values)) {
                # Create the cms/page/blocks
                $page_model = new Cms_Model_Application_Page();
                $page = $page_model->edit_v2($option_value, $values);

                if (!$page || !$page->getId()) {
                    throw new \Siberian\Exception('#578-00: ' . __('An error occurred while saving your page.'));
                }

                /** Update touch date, then never expires (until next touch) */
                $option_value
                    ->touch()
                    ->expires(-1);

                $partial = false;

                $message = __('Success.');
                if (!empty($page->getData('__invalid_blocks'))) {
                    $partial = true;
                    $message = __('Partially saved.') . '<br />' .
                        implode_polyfill('<br />', $page->getData('__invalid_blocks'));
                }

                //
                $isPlaces = $page->getData('__is_places');
                $hasAddress = $page->getData('__has_address');

                if ($isPlaces && !$hasAddress) {
                    throw new \Siberian\Exception('#578-10: ' . __('Places requires at least a valid `address` block.'));
                }

                $payload = [
                    'success' => true,
                    'message' => $message,
                ];

                if ($partial) {
                    $payload = [
                        'warning' => true,
                        'message' => $message,
                    ];
                }
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

    public function deleteAction() {

        if ($data = $this->getRequest()->getPost()) {

            $html = [];

            try {

                // Test s'il y a un value_id
                if (empty($data['option_value_id']) OR empty($data['id'])) {
                    throw new Exception(__('An error occurred while saving. Please try again later.'));
                }

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($data['option_value_id']);

                if(!$option_value->getId()) {
                    throw new Exception(__('An error occurred while saving. Please try again later.'));
                }

                $page = new Cms_Model_Application_Page();
                $page->find($data["id"]);

                if(!$page->getId() OR $page->getValueId() != $option_value->getId() OR $option_value->getAppId() != $this->getApplication()->getId()) {
                    throw new Exception(__('An error occurred while saving your page.'));
                }

                /** Clean up tags */
                if(get_class($page) == 'Cms_Model_Application_Page') {
                    $app_tags = new Application_Model_TagOption();
                    $tags = $app_tags->findAll([
                        "object_id = ?" => $page->getId(),
                        "model = ?" => "Cms_Model_Application_Page",
                    ]);

                    foreach($tags as $tag) {
                        $tag->delete();
                    }
                }

                $page->delete();

                $html = [
                    'success' => 1,
                    'success_message' => __('Place successfully deleted'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                ];
            } catch (Exception $e) {
                $html = [
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                ];
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }

    }

    /**
     *
     */
    public function updatePlacesAction()
    {
        // Upgrade feature if necessary!
        try {
            $optionValue = $this->getCurrentOptionValue();
            $pages = (new Cms_Model_Application_Page())
                ->findAllOrderedByRank($optionValue->getId());

            // Associate tags with pages
            $allTags = [];
            $tagIndex = 1;
            foreach ($pages as $page) {
                if ($page->getPlaceVersion() == 2) {
                    continue;
                }

                $tags = $optionValue->getTagNames($page);
                if (!empty($tags)) {
                    $hasTag = false;
                    foreach ($tags as $tag) {
                        if (!empty($tag)) {
                            $hasTag = true;
                            if (!array_key_exists($tag, $allTags)) {
                                $allTags[$tag] = [
                                    'index' => $tagIndex++,
                                    'pages' => [],
                                ];
                            }
                            $allTags[$tag]['pages'][] = $page->getId();
                        }
                    }
                    if (!$hasTag) {
                        $pagePlace = (new Places_Model_Place())
                            ->find($page->getId());
                        $pagePlace
                            ->setPlaceVersion(2)
                            ->save();
                    }
                } else {
                    $pagePlace = (new Places_Model_Place())
                        ->find($page->getId());
                    $pagePlace
                        ->setPlaceVersion(2)
                        ->save();
                }
            }

            // Create missing tags
            foreach ($allTags as $tagName => $allTag) {
                $lowerCategoryName = strtolower($tagName);
                $category = (new Places_Model_Category())
                    ->find(
                        [
                            'title' => $lowerCategoryName,
                            'value_id' => $optionValue->getId()
                        ]);

                if (!$category->getId()) {
                    $category
                        ->setValueId($optionValue->getId())
                        ->setTitle($lowerCategoryName)
                        ->setPosition($allTag['index'])
                        ->save();
                }

                // Update places
                $pages = $allTag['pages'];
                foreach ($pages as $pageId) {
                    $pagePlace = (new Places_Model_Place())
                        ->find($pageId);
                    if ($pagePlace->getId()) {
                        $pagePlace
                            ->addTag($lowerCategoryName)
                            ->save();

                        $pageCategory = (new Places_Model_PageCategory())
                            ->find(
                                [
                                    'page_id' => $pagePlace->getId(),
                                    'category_id' => $category->getId()
                                ]);

                        if (!$pageCategory->getId()) {
                            $pageCategory
                                ->setPageId($pagePlace->getId())
                                ->setCategoryId($category->getId())
                                ->save();
                        }

                        $pagePlace
                            ->setPlaceVersion(2)
                            ->save();
                    }
                }
            }

            $payload = [
                'success' => true,
                'message' => __('Upgrade done!'),
            ];
        } catch (\Exception $e) {
            $payload = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function skipUpdateAction()
    {
        // Upgrade feature if necessary!
        try {
            $optionValue = $this->getCurrentOptionValue();
            $pages = (new Cms_Model_Application_Page())
                ->findAllOrderedByRank($optionValue->getId());

            // Associate tags with pages
            $allTags = [];
            $tagIndex = 1;
            foreach ($pages as $page) {
                if ($page->getPlaceVersion() == 2) {
                    continue;
                }

                $tags = $optionValue->getTagNames($page);
                if (!empty($tags)) {
                    foreach ($tags as $tag) {
                        if (!empty($tag)) {
                            if (!array_key_exists($tag, $allTags)) {
                                $allTags[$tag] = [
                                    'index' => $tagIndex++,
                                    'pages' => [],
                                ];
                            }
                            $allTags[$tag]['pages'][] = $page->getId();
                        }
                    }
                } else {
                    $pagePlace = (new Places_Model_Place())
                        ->find($page->getId());
                    $pagePlace
                        ->setPlaceVersion(2)
                        ->save();
                }
            }

            // Create missing tags
            foreach ($allTags as $tagName => $allTag) {
                $lowerCategoryName = strtolower($tagName);

                // Update places
                $pages = $allTag['pages'];
                foreach ($pages as $pageId) {
                    $pagePlace = (new Places_Model_Place())
                        ->find($pageId);

                    if ($pagePlace->getId()) {
                        $pagePlace
                            ->addTag($lowerCategoryName)
                            ->save();

                        $pagePlace
                            ->setPlaceVersion(2)
                            ->save();
                    }
                }
            }

            $payload = [
                'success' => true,
                'message' => __('Upgrade done!'),
            ];
        } catch (\Exception $e) {
            $payload = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function updatePinsAction()
    {
        // Upgrade feature if necessary!
        try {
            $optionValue = $this->getCurrentOptionValue();
            $pages = (new Cms_Model_Application_Page())
                ->findAllOrderedByRank($optionValue->getId());

            $request = $this->getRequest();
            $pinValue = $request->getParam('pinValue', false);

            if (!$pinValue) {
                throw new \Siberian\Exception(__('The pin value is required'));
            }

            // Associate tags with pages
            foreach ($pages as $page) {
                $pagePlace = (new Places_Model_Place())
                    ->find($page->getId());
                $pagePlace
                    ->setMapIcon($pinValue)
                    ->save();
            }

            $payload = [
                'success' => true,
                'message' => __('Upgrade done!'),
            ];
        } catch (\Exception $e) {
            $payload = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function updateCategoryPositionsAction()
    {
        try {
            $request = $this->getRequest();
            $indexes = $request->getParam('indexes', null);

            if (empty($indexes)) {
                throw new \Siberian\Exception(__('Nothing to do!'));
            }

            foreach ($indexes as $index => $categoryId) {
                $category = (new Places_Model_Category())
                    ->find($categoryId);

                if (!$category->getId()) {
                    throw new \Siberian\Exception(__('Something went wrong, the category do not exists!'));
                }

                $category
                    ->setPosition($index + 1)
                    ->save();
            }

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

    /**
     *
     */
    public function editCategoryAction()
    {
        $request = $this->getRequest();
        $params = $request->getPost();

        $form = new Places_Form_Category();
        try {
            if ($form->isValid($params)) {
                // Do whatever you need when form is valid!
                $optionValue = $this->getCurrentOptionValue();

                $category = (new Places_Model_Category())
                    ->find($params['category_id']);

                $category->setData($params);

                if (!$category->getId()) {
                    // Set the position + 1
                    $category->initPosition($optionValue->getId());
                }

                Siberian_Feature::formImageForOption(
                    $optionValue,
                    $category,
                    $params,
                    'picture',
                    true
                );

                $category->save();

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
    public function editSettingsAction()
    {
        $request = $this->getRequest();
        $params = $request->getPost();

        $form = new Places_Form_Settings();
        try {
            if ($form->isValid($params)) {
                // Do whatever you need when form is valid!
                $optionValue = $this->getCurrentOptionValue();

                $filteredValues = $form->getValues();

                $filteredValues[] = filter_var($filteredValues["notesAreEnabled"], FILTER_VALIDATE_BOOLEAN);

                $optionValue
                    ->setSettings(\Siberian_Json::encode($filteredValues))
                    ->save();

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
     * Load category form
     */
    public function loadCategoryFormAction()
    {
        try {
            $request = $this->getRequest();
            $categoryId = $request->getParam('category_id');

            $category = (new Places_Model_Category())
                ->find($categoryId);

            if (!$category->getId()) {
                throw new \Siberian\Exception(__("The category you are trying to edit doesn't exists.."));
            }

            $form = new Places_Form_Category();
            $form->populate($category->getData());
            $form->removeNav("nav-categories");
            $form->setAttrib('id', 'form-category-id-' . $categoryId);

            $form->getElement('subtitle')->setAttrib('id', 'subtitle_category_' . $categoryId);

            $submit = $form->addSubmit(__("Save"));
            $submit->addClass("pull-right");

            $payload = [
                'success' => true,
                'form' => $form->render(),
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

    /**
     *
     */
    public function deleteCategoryAction ()
    {
        try {
            $request = $this->getRequest();
            $params = $request->getPost();

            $form = new Wordpress2_Form_Query_Delete();
            if ($form->isValid($params)) {
                $categoryId = $params["category_id"];
                $category = (new Places_Model_Category())
                    ->find($categoryId);

                $category->delete();

                // Delete all links
                $links = (new Places_Model_PageCategory())
                    ->findAll(["category_id" => $categoryId]);

                foreach ($links as $link) {
                    $link->delete();
                }

                $optionValue = $this->getCurrentOptionValue();
                $valueId = $optionValue->getId();

                // Update touch date, then never expires (until next touch)!
                $this->getCurrentOptionValue()
                    ->touch()
                    ->expires(-1);

                // Clear cache on save!
                $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, [
                    'places',
                    'value_id_' . $valueId,
                ]);
            }

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

    /**
     *
     */
    public function loadformAction()
    {
        try {
            $request = $this->getRequest();

            $optionValue = $this->getCurrentOptionValue();
            $placeId = $request->getParam("place_id");

            $page = (new Cms_Model_Application_Page())
                ->find($placeId);

            $settings = $optionValue->getSettings();
            try {
                $settings = \Siberian_Json::decode($settings);
            } catch (\Exception $e) {
                $settings = [
                    "defaultPin" => "image"
                ];
            }

            $isNew = false;
            if (!$page->getId()) {
                $page
                    ->setId("new")
                    ->setMapIcon($settings['defaultPin']);
                $isNew = true;
            }

            $html = $this->getLayout()
                ->addPartial('cms_edit', 'Core_View_Default', 'cms/application/page/edit.phtml')
                ->setOptionValue($optionValue)
                ->setCurrentPage($page)
                ->setCurrentFeature('places')
                ->setIsNew($isNew)
                ->toHtml();

            $data = [
                'success' => true,
                'form' => $html,
            ];

        } catch (\Exception $e) {
            $data = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($data);
    }

}
