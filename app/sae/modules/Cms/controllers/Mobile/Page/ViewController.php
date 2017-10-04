<?php

class Cms_Mobile_Page_ViewController extends Application_Controller_Mobile_Default
{
    public function findAction() {
        if($value_id = $this->getRequest()->getParam('value_id')
           && $page_id = $this->getRequest()->getParam('page_id')) {

            try {

                $option_value = $this->getCurrentOptionValue();

                $page = new Cms_Model_Application_Page();
                $page->find($page_id);

                $blocks = $page->getBlocks();
                $json = array();

                $request = $this->getRequest();

                foreach($blocks as $block) {
                    $json[] = $block->_toJson($request->getBaseUrl());
                }

                $data = array(
                    "blocks"                    => $json,
                    "page_title"                => $page->getTitle() ? $page->getTitle() : $option_value->getTabbarName(),
                    "picture"                   => $page->getPictureUrl(),
                    "social_sharing_active"     => (boolean) $option_value->getSocialSharingIsActive()
                );

            }
            catch(Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }

        } else {
            $data = array('error' => 1, 'message' => 'An error occurred during process. Please try again later.');
        }

        $this->_sendJson($data);
    }

    public function findallAction() {

        if($value_id = $this->getRequest()->getParam('value_id')) {

            try {

                $option = $this->getCurrentOptionValue();

                $page_id = $this->getRequest()->getParam('page_id');
                $page = new Cms_Model_Application_Page();

                if ($page_id) {
                    $page->find($page_id);
                } else if ($option->getCode() == "places" AND !$page_id) {
                    throw new Exception(__("An error occurred during process. Please try again later."));
                } else {
                    $page->find($option->getId(), 'value_id');
                }

                $blocks = $page->getBlocks();
                $data = array("blocks" => array());

                foreach ($blocks as $block) {
                    $data["blocks"][] = $block->_toJson($this->getRequest()->getBaseUrl());
                }

                if($option->getCode() == "places") {
                    $data["page"] = array(
                        "title"         => $page->getTitle(),
                        "subtitle"      => $page->getContent(),
                        "picture"       => $page->getPictureUrl() ? $this->getRequest()->getBaseUrl().$page->getPictureUrl() : null,
                        "show_image"    => $page->getMetadataValue('show_image'),
                        "show_titles"   => $page->getMetadataValue('show_titles')
                    );
                }

                $data["page_title"]             = $page->getTitle() ? $page->getTitle() : $option->getTabbarName();
                $data["social_sharing_active"]  = (boolean) $option->getSocialSharingIsActive();

            } catch(Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendJson($data);
        }

    }

    public function findblockAction() {

        if($value_id = $this->getRequest()->getParam('value_id') AND
            $block_id = $this->getRequest()->getParam('block_id')) {

            try {

                $page_id = $this->getRequest()->getParam("page_id");
                $option = $this->getCurrentOptionValue();

                $page_id = $this->getRequest()->getParam('page_id');
                $page = new Cms_Model_Application_Page();

                if ($page_id) {
                    $page->find($page_id);
                } else if ($option->getCode() == "places" AND !$page_id) {
                    throw new Siberian_Exception(__("An error occurred during process. Please try again later."));
                } else {
                    $page->find($option->getId(), 'value_id');
                }

                $blocks = $page->getBlocks();
                $data = array("block" => array());

                foreach ($blocks as $block) {
                    if($block->getBlockId() == $block_id) {
                        $data["block"] = $block->_toJson($this->getRequest()->getBaseUrl());
                    }
                }

                if($page->getPictureUrl()) {
                    $data["block"]["picture_url"] = $this->getRequest()->getBaseUrl().$page->getPictureUrl();
                }

                $data["page_title"] = $page->getTitle() ? $page->getTitle() : $option->getTabbarName();

            } catch(Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendJson($data);
        }

    }

}
