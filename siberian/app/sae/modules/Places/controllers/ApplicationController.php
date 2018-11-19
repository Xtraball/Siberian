<?php

/**
 * Class Places_ApplicationController
 */
class Places_ApplicationController extends Application_Controller_Default
{

    /**
     * @var array
     */
    public $cache_triggers = [
        "searchsettings" => [
            "tags" => [
                "homepage_app_#APP_ID#",
            ],
        ],
        "rank" => [
            "tags" => [
                "homepage_app_#APP_ID#",
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
     *
     */
    public function updatePlacesAction ()
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

        } catch (\Exception $e) {
        }
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

            $isNew = false;
            if (!$page->getId()) {
                $page->setId("new");
                $isNew = true;
            }

            $html = $this->getLayout()
                ->addPartial('cms_edit', 'Core_View_Default', 'cms/application/page/edit.phtml')
                ->setOptionValue($optionValue)
                ->setCurrentPage($page)
                ->setCurrentFeature("places")
                ->setIsNew($isNew)
                ->toHtml();

            $data = [
                "success" => true,
                "form" => $html,
            ];

        } catch (Exception $e) {
            $data = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($data);
    }

    public function rankAction()
    {
        $ordering = $this->getRequest()->getParam("ordering");
        $value_id = $this->getRequest()->getParam("option_value_id");
        $html = [];
        try {
            $pages = Cms_Model_Application_Page::findAllByPageId($value_id, array_keys($ordering));
            $table = new Cms_Model_Db_Table_Application_Page_Block_Address();
            $adapter = $table->getAdapter();
            foreach ($pages as $page_row) {
                $blocks = $page_row->getBlocks();
                foreach ($blocks as $block) {
                    if (get_class($block) == "Cms_Model_Application_Block") {
                        $block->setRank($ordering[$page_row->getPageId()])->save();
                        $where = $adapter->quoteInto("address_id = ?", $block->getAddressId());
                        $table->update(["rank" => $ordering[$page_row->getPageId()]], $where);
                        break;
                    }
                }
            }

            $this->getCurrentOptionValue()
                ->touch()
                ->expires(-1);


            $html = [
                'success' => 1,
                'success_message' => __('Order successfully saved saved.'),
                'message_timeout' => 2,
                'message_button' => 0,
                'message_loader' => 0
            ];
        } catch (Exception $e) {
            $html = [
                'message' => __('An error occured.'),
                'message_button' => 1,
                'message_loader' => 1
            ];
        }
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    public function searchsettingsAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $html = [];

            try {
                $settings = new Places_Model_Domain_Settings($data['option_value_id'], $this);

                $settings->setup($data['search']);
                $settings->save();
                Cms_Model_Application_Page::setPlaceOrder($data['option_value_id'],
                    $data['places_order'] === 'distance');
                Cms_Model_Application_Page::setPlaceOrderAlpha($data['option_value_id'],
                    $data['places_order'] === 'alpha');

                $this->getCurrentOptionValue()
                    ->touch()
                    ->expires(-1);

                $html = [
                    'success' => 1,
                    'success_message' => __('Setting successfully saved.'),
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

}