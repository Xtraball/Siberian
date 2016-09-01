<?php

class Folder_Mobile_ListController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        if($value_id = $this->getRequest()->getParam('value_id')) {

            try {

                $category_id = $this->getRequest()->getParam('category_id');
                $current_category = new Folder_Model_Category();

                if($category_id) {
                    $current_category->find($category_id, 'category_id');
                }

                $object = $this->getCurrentOptionValue()->getObject();

                if(!$object->getId() OR ($current_category->getId() AND $current_category->getRootCategoryId() != $object->getRootCategoryId())) {
                    throw new Exception($this->_('An error occurred during process. Please try again later.'));
                }

                $color_code = 'background';
                if($this->getApplication()->useIonicDesign()) {
                    $color_code = 'list_item';
                }
                $color = $this->getApplication()->getBlock($color_code)->getImageColor();

                //Here we get the list used for the search in folder feature
                $current_option = $this->getCurrentOptionValue();
                $folder = new Folder_Model_Folder();
                $category = new Folder_Model_Category();
                $folder->find($current_option->getId(), 'value_id');
                $category->find($folder->getRootCategoryId(), 'category_id') ;

                $result = array();
                array_push($result, $category);
                $this->_getAllChildren($category, $result);

                $search_list = array();

                foreach($result as $folder) {
                    $search_list[] = array(
                        "name" => $folder->getTitle(),
                        "father_name" => $folder->getFatherName(),
                        "url" => $this->getPath("folder/mobile_list", array("value_id" => $current_option->getId(), "category_id" => $folder->getId())),
                        "picture" => $current_option->getIconId() ? $this->getRequest()->getBaseUrl().$this->_getColorizedImage($current_option->getIconId(), $color) : null,
                        "type" => "folder"
                    );
                    $category_option = new Application_Model_Option_Value();
                    $category_options = $category_option->findAll(array("app_id" => $this->getApplication()->getId(), 'folder_category_id' => $folder->getCategoryId()), array('folder_category_position ASC'));

                    foreach($category_options as $feature) {
                        $search_list[] = array(
                            "name" => $feature->getTabbarName(),
                            "father_name" => $folder->getTitle(),
                            "url" => $feature->getPath(null, array('value_id' => $feature->getId()), false),
                            'is_link' => !$feature->getIsAjax(),
                            "picture" => $feature->getIconId() ? $this->getRequest()->getBaseUrl().$this->_getColorizedImage($feature->getIconId(), $color) : null,
                            "code" => $feature->getCode(),
                            "type" => "feature"
                        );
                    }
                }

                if(!$current_category->getId()) {
                    $current_category = $object->getRootCategory();
                }

                $data = array("folders" => array());

                $subcategories = $current_category->getChildren();

                foreach($subcategories as $subcategory) {
                    $data["folders"][] = array(
                        "title" => $subcategory->getTitle(),
                        "subtitle" => $subcategory->getSubtitle(),
                        "picture" => $subcategory->getPictureUrl() ? $this->getRequest()->getBaseUrl().$subcategory->getPictureUrl() : null,
                        "url" => $this->getPath("folder/mobile_list", array("value_id" => $value_id, "category_id" => $subcategory->getId()))
                    );
                }

                $pages = $current_category->getPages();

                foreach($pages as $page) {
                    $data["folders"][] = array(
                        "title" => $page->getTabbarName(),
                        "subtitle" => "",
                        "picture" => $page->getIconId() ? $this->getRequest()->getBaseUrl().$this->_getColorizedImage($page->getIconId(), $color) : null,
                        'is_link' => !$page->getIsAjax(),
                        "url" => $page->getPath(null, array('value_id' => $page->getId()), false),
                        "code" => $page->getCode(),
                        "is_locked" => $page->isLocked(),
                    );
                }

                $data["cover"] = array(
                    "title" => $current_category->getTitle(),
                    "subtitle" => $current_category->getSubtitle(),
                    "picture" => $current_category->getPictureUrl() ? $this->getRequest()->getBaseUrl().$current_category->getPictureUrl() : null
                );

                $data["search_list"] = $search_list;

                $data["page_title"] = $current_category->getTitle();

            }
            catch(Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($data);

        }

    }

    private function _getAllChildren($category, &$tab_children) {
        $children = $category->getChildren();
        foreach($children as $child) {
            $child->setFatherName($category->getTitle());
            array_push($tab_children, $child);
            $this->_getAllChildren($child, $tab_children);
        }
    }

}