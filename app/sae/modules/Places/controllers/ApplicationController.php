<?php

class Places_ApplicationController extends Application_Controller_Default {

    public function formAction() {


        $id = $this->getRequest()->getParam("id");

        try {
            $option_value = new Application_Model_Option_Value();
            $option_value->find($this->getRequest()->getParam("option_value_id"));

            $page = new Cms_Model_Application_Page();
            $page->find($id);

            $tag_names = $option_value->getTagNames($page);
            $page->tag_names = $tag_names;

            if(!$page->getId()) {
                $page->setId("new");
                $page->tag_names = array();
            }

            $this->getLayout()->setBaseRender('form', 'cms/application/page/edit.phtml', 'admin_view_default')
                ->setCurrentPage($page)
                ->setOptionValue($this->getCurrentOptionValue())
                ->setCurrentFeature("places")
            ;

            $html = array(
                'form' => $this->getLayout()->render(),
                'success' => 1
            );

        } catch (Exception $e) {
            $html = array(
                'message' => $e->getMessage()
            );
        }

        $this->getLayout()->setHtml(Zend_Json::encode($html));
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
                Cms_Model_Application_Page::setPlaceOrder($data['option_value_id'], $data['places_order']);

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