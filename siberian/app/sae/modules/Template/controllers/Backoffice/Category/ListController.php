<?php

class Template_Backoffice_Category_ListController extends Backoffice_Controller_Default
{

    public function loadAction() {

        $html = array(
            "title" => __("Templates"),
            "icon" => "fa-picture-o",
        );

        $this->_sendHtml($html);

    }

    public function findallAction() {

        $category = new Template_Model_Category();
        $categories = $category->findAll();
        $data = array("title" => $this->_("List of your categories"), "columns" => array());
        $tmp = array();
        foreach($categories as $category) {
            $tmp[] = array(
                "category_id" => $category->getId(),
                "name" => __($category->getName())
            );
            if(count($tmp) == 2) {
                $data["columns"][] = $tmp;
                $tmp = array();
            }
        }

        if(!empty($tmp)) $data["columns"][] = $tmp;

        $this->_sendHtml($data);
    }

    public function saveAction() {

        if($categories = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {
                
                foreach($categories as $data) {
                    $category = new Template_Model_Category();
                    $category->find($data["category_id"]);
                    $category->addData($data)->save();
                }

                $data = array(
                    "success" => 1,
                    "message" => $this->_("Info successfully saved")
                );

            } catch(Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendHtml($data);
        }

    }

}
