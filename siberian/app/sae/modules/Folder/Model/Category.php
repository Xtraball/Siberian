<?php
class Folder_Model_Category extends Core_Model_Default {

    const DISPLAYED_PER_PAGE = 50;

    protected $_root_category_id;
    protected $_children;
    protected $_pages;
    protected $_products;
    protected $_specific_import_data = array(
        "mcommerce_id"
    );
    protected $_mandatory_columns = array(
        "title"
    );

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Folder_Model_Db_Table_Category';
        return $this;
    }

    public function getRootCategoryId() {

        if(!$this->_root_category_id) {
            if($this->getParentId()) {
                $this->_root_category_id = $this->getTable()->findRootCategoryId($this->getParentId());
            }
            else {
                $this->_root_category_id = $this->getId();
            }
        }

        return $this->_root_category_id;

    }

    public function getChildren($offset = null) {

        if(!$this->_children) {

            $params = array();

            if(is_numeric($offset)) {
                $params["limit"] = Folder_Model_Category::DISPLAYED_PER_PAGE;
                $params["offset"] = $offset;
            }

            $this->_children = $this->findAll(array('parent_id' => $this->getId()), 'pos ASC', $params);

        }

        return $this->_children;

    }

    public function getPages() {
        if(!$this->_pages) {
            $page = new Application_Model_Option_Value();
            $this->_pages = $page->findAll(array('app_id' => $this->getApplication()->getId(), 'folder_category_id' => $this->getId(), "is_active" => 1, "is_visible" => 1), "folder_category_position ASC");
        }

        return $this->_pages;
    }
    public function getProducts($offset = null) {
        if(!$this->_products) {
            $product = new Catalog_Model_Product();
            $this->_products = $product->findByCategory($this->getId(), true, $offset);
        }

        return $this->_products;
    }

    public function getPictureUrl() {
        $path_picture = Application_Model_Application::getImagePath().$this->getPicture();
        $base_path_picture = Application_Model_Application::getBaseImagePath().$this->getPicture();
        if($this->getPicture() AND file_exists($base_path_picture)) {
            return $path_picture;
        }
        return '';
    }

    public function getNextCategoryPosition($parent_id) {
        $lastPosition = $this->getTable()->getLastCategoryPosition($parent_id);
        if(!$lastPosition) $lastPosition = 0;

        return ++$lastPosition;
    }

    public function delete() {

        $category_option = new Application_Model_Option_Value();
        $option_values = $category_option->findAll(array('folder_category_id' => $this->getId()));
        if($option_values->count()) {
            foreach($option_values as $option_value) {
                $option_value->setFolderId(null)
                    ->setFolderCategoryId(null)
                    ->setFolderCategoryPosition(null)
                    ->save();
            }
        }

        foreach($this->getChildren() as $child) {
            $child->delete();
        }

        return parent::delete();

    }

    public function finalizeImport($got_heading, $data = null, $line, $full_data = null) {
        $finalize_errors = array();
        $got_parent_id = false;

        if($got_heading) {
            foreach (array_keys($data) as $heading) {
                if($heading == "parent_id") {
                    $got_parent_id = true;
                }
            }

            if($got_parent_id AND !empty($data["parent_id"])) {
                $ind_parent_id = $data["parent_id"]-1;
                if($full_data[$ind_parent_id]) {
                    if($parent_id = $full_data[$ind_parent_id]["new_object_id"]) {
                        $this->setParentId($parent_id);
                    } else {
                        $finalize_errors[] = $this->_("The categories for line %s couldn't be created : no parent category found.", $line);
                    }
                } else {
                    $finalize_errors[] = $this->_("The categories for line %s couldn't be created : no parent category found.", $line);
                }
            } else {
                if($data["mcommerce_id"]) {
                    $mcommerce = new Mcommerce_Model_Mcommerce();
                    $mcommerce = $mcommerce->find($data["mcommerce_id"]);
                    if($mcommerce->getId()) {
                        $this->setParentId($mcommerce->getRootCategory()->getId());
                    } else {
                        $finalize_errors[] = $this->_("The categories for line %s couldn't be created : no root category found.", $line);
                    }
                } else {
                    $finalize_errors[] = $this->_("The categories for line %s couldn't be created : no root category found.", $line);
                }
            }
        }

        if($finalize_errors) {
            return $finalize_errors;
        }

        try {
            $this->save();
            return true;
        } catch(Exception $e) {
            $finalize_errors[] = $this->_("An error occurred while importing line %s : impossible to save the object. Please check your file format.", $line);
            return $finalize_errors;
        }

    }

    public function getExportData($parent = null) {
        $col_to_export = array("category_id", "parent_id", "title");
        $result = array();
        $heading = array();
        $line_data = array();

        $root_category = $parent->getRootCategory();

        if($root_category->getId()) {

            foreach ($root_category->getOrigData() as $key => $data) {
                if(in_array($key, $col_to_export)) {
                    $line_data[] = $data;
                }
            }
            $result[] = $col_to_export;
            $result[] = $line_data;

            $line_data = array();
            foreach($root_category->getChildren() as $child) {
                foreach($child->getData() as $key => $child_data) {
                    if(in_array($key, $col_to_export)) {
                        $line_data[] = $child_data;
                    }
                }
                $result[] = $line_data;
                $line_data = array();
            }

            return $result;

        } else {
            return array();
        }
    }

}
