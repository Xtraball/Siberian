<?php

class Catalog_Model_Category extends Core_Model_Default
{

    public $_is_cacheable = true;

    protected $_products;
    protected $_active_products;
    protected $_parent;
    protected $_children;
    protected $_active_children;

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Catalog_Model_Db_Table_Category';
    }

    public function findByValueId($value_id, $pos_id = null, $only_active = false, $only_first_level = false) {
        return $this->getTable()->findByValueId($value_id, $pos_id, $only_active, $only_first_level);
    }

    public function findLastPosition($value_id, $parent_id = null) {
        return $this->getTable()->findLastPosition($value_id, $parent_id) + 1;
    }

    public function updatePosition($rows) {
    	$this->getTable()->updatePosition($rows);
    	return $this;
    }


    /**
     * @return array
     */
    public function getInappStates($value_id) {
        $product_model = new Catalog_Model_Product();
        $products = $product_model->findByValueId($value_id);

        $state_products = array();
        foreach($products as $product) {

            $state_products[] = array(
                "label" => $product->getName(),
                "state" => "catalog-product-view",
                "offline" => true,
                "params" => array(
                    "value_id" => $value_id,
                    "product_id" => $product->getId(),
                ),
            );
        }

        $in_app_states = array(
            array(
                "state" => "catalog-category-list",
                "offline" => true,
                "params" => array(
                    "value_id" => $value_id,
                ),
                "childrens" => $state_products,
            ),
        );

        return $in_app_states;
    }

    /**
     * @param $option_value
     * @return array
     */
    public function getFeaturePaths($option_value) {
        if(!$this->isCacheable()) {
            return array();
        }

        $value_id = $option_value->getId();
        $cache_id = "feature_paths_valueid_{$value_id}";
        if(!$result = $this->cache->load($cache_id)) {

            $paths = array();
            $paths[] = $option_value->getPath("findall", array('value_id' => $option_value->getId()), false);

            if($uri = $option_value->getMobileViewUri("find")) {

                $products = $this->getProducts();
                foreach ($products as $product) {
                    $params = array(
                        "value_id" => $option_value->getId(),
                        "product_id" => $product->getId()
                    );

                }

                $paths[] = $option_value->getPath($uri, $params, false);
            }

            $this->cache->save($paths, $cache_id, array(
                "feature_paths",
                "feature_paths_valueid_{$value_id}"
            ));
        } else {
            $paths = $result;
        }

        return $paths;
    }

    public function getParent() {

        if(!$this->_parent) {
            $category = new Catalog_Model_Category();
            if($this->getParentId()) {
                $category->find($this->getParentId());
            }
            $this->_parent = $category;
        }

        return $this->_parent;
    }

    public function getChildren() {

        if(!$this->_children) {
            $category = new Catalog_Model_Category();
            $this->_children = $category->findAll(array('parent_id' => $this->getId()), 'position ASC');
        }

        return $this->_children;
    }

    public function getActiveChildren() {

        if(!$this->_active_children) {
            $category = new Catalog_Model_Category();
            $this->_active_children = $category->findAll(array('parent_id' => $this->getId(), 'is_active' => 1));
        }

        return $this->_active_children;
    }

    public function addProduct($product) {

        $id = $product->getId();
        if(!$id) $id = 'new_'.count($this->_products);

        $this->_products[$id] = $product;
        return $this;
    }

    public function setProducts($products) {
        $this->_products = $products;
        return $this;
    }

    public function getProducts() {
        if(is_null($this->_products)) {
            if($this->getId() AND $this->getCategoryId()) {
                $this->loadProducts();
                if(count($this->_products) == 0) $this->_products = array();
            }
        }

        return !is_null($this->_products) ? $this->_products :  array();
    }

    public function getActiveProducts() {
        if(is_null($this->_active_products)) {

            $products = array();
            foreach($this->getProducts() as $product) {
                if($product->getIsActive()) $products[] = $product;
            }
            $this->_active_products = $products;
        }
        return $this->_active_products;
    }

    public function loadProducts() {
        $product = new Catalog_Model_Product();
        $products = $product->findByCategory($this->getId());
        foreach($products as $product) {
            $this->_products[$product->getId()] = $product;
        }
        return $this;
    }

    public function resetProducts() {
        $this->_products = null;
        return $this;
    }

    public function save() {

        if(!$this->getIsDeleted()) {
            if(!$this->getPosition()) {
                $this->setPosition($this->findLastPosition($this->getValueId(), $this->getParentId()));
            }
        }
        else {
            foreach($this->getChildren() as $child) $child->setIsDeleted(1)->save();
        }

        parent::save();

        if(!$this->getData('is_deleted')) {

            if(!empty($this->_products)) {
                foreach($this->_products as $product) {
                    $product->setCategoryId($this->getId())
                        ->setValueId($this->getValueId())
                        ->save()
                    ;
                }
            }
        }
        else if(!$this->getParentId()) {
            foreach($this->getChildren() as $child) {
                foreach($child->getProducts() as $product) $product->delete();
            }
            foreach($this->getProducts() as $product) $product->delete();
        }

    }

    public function copyTo($option, $parent_id = null) {

        if($this->getParentId() AND is_null($parent_id)) return $this;

        $products = $this->getProducts();
        $this->setProducts(array());
        $children = $this->getChildren();

        $this->setId(null)
            ->setValueId($option->getId())
            ->setParentId($parent_id)
            ->save()
        ;

        foreach($products as $product) {

            $options = array();
            if($product->getData('type') == 'format') {
                $options = $product->getType()->getOptions();
            }

            $product->copyPictureTo($option);
            $product->setId(null)->setProductId(null)
                ->setValueId($option->getId())
                ->setCategoryId($this->getId())
                ->save()
            ;

            foreach($options as $format) {
                $format->setId(null)->setProductId($product->getId())->save();
            }

        }

        foreach($children as $child) {
            $child->copyTo($option, $this->getId());
        }

        return $this;

    }

    public function getTemplatePaths($page, $option_layouts, $suffix, $path) {
        $paths = array();
        $baseUrl = $this->getApplication()->getUrl(null, array(), null, $this->getApplication()->getKey());

        $module_name = current(explode("_", $this->getModel()));
        if(!empty($module_name)) {
            $module_name = strtolower($module_name);
            Core_Model_Translator::addModule($module_name);
        }

        $layout = str_replace(array($baseUrl, "/"), array("", "_"), $page->getUrl("template").$suffix);

        $params = array();
        if(in_array($page->getOptionId(), $option_layouts)) {
            $params["value_id"] = $page->getId();
        }

        $layout_id = str_replace($baseUrl, "", $path.$page->getUrl("template", $params));

        $paths[] = array(
            "layout" => $layout,
            "layout_id" => $layout_id
        );

        if($page->getMobileViewUri("template")) {

            $layout = str_replace(array($baseUrl, "/", "view"), array("", "_", "list"), $page->getMobileViewUri("template").$suffix);

            $params = array();
            if(in_array($page->getOptionId(), $option_layouts)) {
                $params["value_id"] = $page->getId();
            }
            $layout_id = str_replace(array($baseUrl, "view"), array("", "list"), $path.$page->getMobileViewUri("template", $params));

            $paths[] = array(
                "layout" => $layout,
                "layout_id" => $layout_id
            );

            $layout = str_replace(array($baseUrl, "/"), array("", "_"), $page->getMobileViewUri("template").$suffix);

            $params = array();
            if(in_array($page->getOptionId(), $option_layouts)) {
                $params["value_id"] = $page->getId();
            }
            $layout_id = str_replace($baseUrl, "", $path.$page->getMobileViewUri("template", $params));

            $paths[] = array(
                "layout" => $layout,
                "layout_id" => $layout_id
            );

        }

        return $paths;
    }

    public function createDummyContents($option_value, $design, $category) {

        $option = new Application_Model_Option();
        $option->find($option_value->getOptionId());

        $dummy_content_xml = $this->_getDummyXml($design, $category);

        if($option->getCode() == "catalog") {

            foreach ($dummy_content_xml->catalog->children() as $categories) {

                $this->unsData();

                //check si la category existe sur cette app
                $category_data = array(
                    "name" => $categories->name,
                    "value_id" => $option_value->getId()
                );
                $category_id = $this->find($category_data)
                    ->getCategoryId()
                ;

                if (!$category_id) {

                    $this->setName((string)$categories->name)
                        ->setValueId($option_value->getId())
                        ->save()
                    ;

                    $category_id = $this->getId();
                }

                foreach ($categories->products->children() as $product) {
                    $product_model = new Catalog_Model_Product();

                    if ($product->attributes()->subcategory) {

                        $sub_category_model = new Catalog_Model_Category();
                        //check si la sous category existe sur cette app
                        $subcategory_data = array(
                            "name" => $product->attributes()->subcategory,
                            "value_id" => $option_value->getId()
                        );
                        $sub_category_model->find($subcategory_data);

                        if (!$sub_category_model->getCategoryId()) {
                            $sub_category_model->setName($product->attributes()->subcategory)
                                ->setValueId($option_value->getId())
                                ->setParentId($category_id)
                                ->save();

                            $product_model->setCategoryId($sub_category_model->getId());
                        } else {
                            $sub_category_model->setParentId($category_id)
                                ->save();
                            $product_model->setCategoryId($sub_category_model->getId());
                        }

                    } else {
                        $product_model->setCategoryId($category_id);
                    }

                    foreach ($product->content->children() as $key => $value) {
                        $product_model->addData((string)$key, (string)$value);
                    }

                    if ($product->formats) {
                        $format_option = array();
                        foreach ($product->formats->children() as $format) {

                            foreach ($format as $key => $val) {
                                $format_option[$format->getName()][(string)$key] = (string)$val;
                            }

                        }

                        $product_model->setOption($format_option);
                    }

                    $product_model->setValueId($option_value->getId())
                        ->save()
                    ;

                }

            }

        }
    }
}