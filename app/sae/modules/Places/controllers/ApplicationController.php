<?php

class Places_ApplicationController extends Application_Controller_Default {

    public function loadformAction() {

        $place_id = $this->getRequest()->getParam("place_id");
        $option_value = $this->getCurrentOptionValue();

        try {

            $page = new Cms_Model_Application_Page();
            $page->find($place_id);

            $tag_names = $option_value->getTagNames($page);
            $page->tag_names = $tag_names;

            $is_new = false;
            if(!$page->getId()) {
                $page->setId("new");
                $page->tag_names = array();
                $is_new = true;
            }

            $html = $this->getLayout()
                ->addPartial('cms_edit', 'Core_View_Default', 'cms/application/page/edit.phtml')
                ->setOptionValue($option_value)
                ->setCurrentPage($page)
                ->setCurrentFeature("places")
                ->setIsNew($is_new)
                ->toHtml();

            $data = array(
                "success" => true,
                "form" => $html,
            );

        } catch (Exception $e) {
            $data = array(
                "error" => true,
                "message" => $e->getMessage()
            );
        }

        $this->_sendJson($data);
    }

    public function rankAction() {
        $ordering = $this->getRequest()->getParam("ordering");
        $value_id = $this->getRequest()->getParam("option_value_id");
        $html = array();
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
                        $table->update(array("rank" => $ordering[$page_row->getPageId()]), $where);
                        break;
                    }
                }
            }
            $html = array(
                'success' => 1,
                'success_message' => $this->_('Order successfully saved saved.'),
                'message_timeout' => 2,
                'message_button' => 0,
                'message_loader' => 0
            );
        } catch (Exception $e) {
            $html = array(
                'message' => $this->_('An error occured.'),
                'message_button' => 1,
                'message_loader' => 1
            );
        }
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    public function searchSettingsAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $html = array();

            try {
                $settings = new Places_Model_Domain_Settings($data['option_value_id'], $this);

                $settings->setup($data['search']);
                $settings->save();
                Cms_Model_Application_Page::setPlaceOrder($data['option_value_id'], $data['places_order'] == 'distance');
                Cms_Model_Application_Page::setPlaceOrderAlpha($data['option_value_id'], $data['places_order'] == 'alpha');

                $html = array(
                    'success' => 1,
                    'success_message' => $this->_('Setting successfully saved.'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );
            } catch (Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));

        }

    }

}