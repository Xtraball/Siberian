<?php

class Catalog_Model_Product extends Core_Model_Default {

    protected $_is_cacheable = true;

    const DISPLAYED_PER_PAGE = 10;

    protected $_instanceSingleton;
    protected $_outlets;
    protected $_category;
    protected $_category_ids;
    protected $_groups;
    protected $_specific_import_data = array(
        "mcommerce_id"
    );

    protected $_mandatory_columns = array(
        "tax_id",
        "name",
        "description",
        "price"
    );

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Catalog_Model_Db_Table_Product';
    }

    /**
     * @return array
     */
    public function getInappStates($value_id) {
        $products = $this->findByValueId($value_id);

        $state_products = array();
        foreach($products as $product) {
            $state_products[] = array(
                "label" => $product->getName(),
                "state" => "set-meal-view",
                "offline" => true,
                "params" => array(
                    "value_id" => $value_id,
                    "set_meal_id" => $product->getId(),
                ),
            );
        }

        $in_app_states = array(
            array(
                "state" => "set-meal-list",
                "offline" => true,
                "params" => array(
                    "value_id" => $value_id,
                ),
            ),
            $state_products,
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

            if($option_value->getCode() == "set_meal") {
                $menus = $this->findAll(array('value_id' => $option_value->getId(), 'type' => 'menu'));

                for($i = 0; $i <= floor($menus->count()/Catalog_Model_Product::DISPLAYED_PER_PAGE); $i++) {
                    $paths[] = $option_value->getPath("catalog/mobile_setmeal_list/findall", array("value_id" => $option_value->getId(), "offset" => $i*Catalog_Model_Product::DISPLAYED_PER_PAGE));
                }

                foreach($menus as $menu) {
                    $paths[] = $option_value->getPath("catalog/mobile_setmeal_view/find", array(
                        "set_meal_id" => $menu->getId(),
                        "value_id" => $option_value->getId()
                    ));
                }
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

    /**
     * @param $option_value
     * @return array
     */
    public function getAssetsPaths($option_value) {
        $paths = array();

        $value_id = $option_value->getId();
        $cache_id = "assets_paths_valueid_{$value_id}";
        if(!$result = $this->cache->load($cache_id)) {

            if($option_value->getCode() == "set_meal") {
                $menus = $this->findAll(array('value_id' => $option_value->getId(), 'type' => 'menu'));

                foreach($menus as $menu) {
                    //if($menu->getThumbnailUrl())
                        //$paths[] = $menu->getThumbnailUrl();

                    //if($menu->getPictureUrl())
                        //$paths[] = $menu->getPictureUrl();
                }
            }

            $this->cache->save($paths, $cache_id, array(
                "assets_paths",
                "assets_paths_valueid_{$value_id}"
            ));
        } else {
            $paths = $result;
        }

        return $paths;
    }

    public function findByCategory($category_id, $use_folder = false, $offset = null) {
        return $this->getTable()->findByCategory($category_id, $use_folder, $offset);
    }

    public function findByValueId($value_id, $pos_id = null, $only_active = false, $with_menus = false) {
        return $this->getTable()->findByValueId($value_id, $pos_id, $only_active, $with_menus);
    }

    public function findByPosId($product_id) {
        $this->uns();
        $row = $this->getTable()->findByPosId($product_id);
        if($row) {
            $this->setData($row->getData());
            $this->setId($row->getId());
        }

        return $this;
    }

    public function findMenus($value_id) {
        return $this->getTable()->findMenus($value_id);
    }

    public function findLastPosition($value_id) {
        return $this->getTable()->findLastPosition($value_id) + 1;
    }

    public function updatePosition($rows) {
    	$this->getTable()->updatePosition($rows);
    	return $this;
    }

    public function getCategory() {

        if(is_null($this->_category)) {
            $this->_category = new Catalog_Model_Category();
            $this->_category->find($this->getCategoryId());
        }

        return $this->_category;
    }

    public function setCategory($category) {
        $this->_category = $category;
        return $this;
    }

    public function getCategoryIds() {
        if(!$this->_category_ids) {
            $this->_category_ids = array();
            if($this->getId()) {
                $this->_category_ids = $this->getTable()->findCategoryIds($this->getId());
            }
        }

        return $this->_category_ids;
    }

    public function getGroups() {

        if(!$this->_groups) {
            $group = new Catalog_Model_Product_Group_Value();
            $this->_groups = $group->findAll(array('product_id' => $this->getId(), 'as_checkbox' => false));
        }

        return $this->_groups;

    }

    public function getChoices() {

        if(!$this->_choices) {
            $group = new Catalog_Model_Product_Group_Value();
            $this->_choices = $group->findAll(array('product_id' => $this->getId(), 'as_checkbox' => true));
        }

        return $this->_choices;

    }

    public function getType() {
        if(is_null($this->_instanceSingleton)) {
            if(!is_null($this->getData('type'))) {
            $class = 'Catalog_Model_Product_';
            $class .= implode('_', array_map('ucwords', explode('_', $this->getData('type'))));
                $this->_instanceSingleton = new $class();
                $this->_instanceSingleton->setProduct($this);
            }
        }

        return $this->_instanceSingleton;
    }

    public function getMinPrice() {

        if(!$this->getData('min_price')) {
            $min_price = null;
            if($this->getData('type') == 'format') {
                $formats = $this->getType()->getOptions();
                foreach($formats as $format) {
                    if(is_null($min_price)) $min_price = $format->getPrice();
                    else $min_price = min($min_price, $format->getPrice());
                }
                if(is_null($min_price)) $min_price = 0;
            }
            else {
                $min_price = $this->getPrice();
            }

            $groups = $this->getGroups();
            foreach($groups as $group) {

                if(!$group->isRequired()) continue;
                $min_option_price = null;
                foreach($group->getOptions() as $option) {
                    if($option->getPrice()) {
                        if(!$min_option_price) $min_option_price = $option->getPrice();
                        else $min_option_price = min($min_option_price, $option->getPrice());
                    }
                }
                if($min_option_price) $min_price += $min_option_price;
            }

            $this->setMinPrice($min_price);
        }

        return $this->getData('min_price');
    }

    public function checkType() {
        $options = $this->getData('option');
        if(!empty($options)) {
            $this->setType('format');
            $this->getType()->setOptions($options);
        }
    }

    public function getDescription() {
        return stripslashes($this->getData('description'));
    }

    public function getPictureUrl() {
        if($this->getData('picture')) {
            $image_path = Application_Model_Application::getImagePath().$this->getData('picture');
            $base_image_path = Application_Model_Application::getBaseImagePath().$this->getData('picture');
            if(file_exists($base_image_path)) {
                return $image_path;
            }
        }
        return null;
    }

    //If !$all, return only first image
    public function getLibraryPictures($all = true, $base_path = null) {
        if($this->getLibraryId()) {
            $library_image = new Media_Model_Library_Image();
            $images = $library_image->findAll(array("library_id" => $this->getLibraryId()));
            $image_list = array();
            foreach($images as $image) {
                $image_path = Application_Model_Application::getImagePath().$image->getLink();
                $image_list[] = array(
                    "id" => $image->getId(),
                    "url" => $base_path.$image_path
                );
            }
            if(count($image_list) > 0 AND !$all) {
                $image_list = $image_list[0];
            }
            return $image_list;
        }
        return null;
    }

    public function getThumbnailUrl() {
        if($picture = $this->getPictureUrl()) {

            $newIcon = new Core_Model_Lib_Image();
            $newIcon->setId(sha1($picture."_thumbnail"))
                ->setPath(Core_Model_Directory::getBasePathTo($picture))
                ->setWidth(100)
                ->setHeight(100)
                ->crop()
            ;

            return $newIcon->getUrl();
        }
        return null;
    }

    public function save() {
        $this->checkType();

        if(!$this->getIsDeleted()) {
            if (!$this->getPosition()) $this->setPosition($this->findLastPosition($this->getValueId()));

            if (!$this->getData('type')) $this->setData('type', 'simple');
            if ($this->getData('type') == 'simple') {
                $price = Core_Model_Language::normalizePrice($this->getData('price'));
                $this->setData('price', $price);
            }

            //MCommerce multi pictures
            $this->addPictures();
            $this->deletePictures();

            parent::save();

            if ($this->getNewCategoryIds()) {
                $this->getTable()->saveCategoryIds($this->getId(), $this->getNewCategoryIds());
            }
            $this->getType()->setProduct($this)->save();

        } else {
            parent::save();
        }

        return $this;
    }

    public function addPictures() {
        if($picture_list = $this->getPictureList()) {

            if(!($library_id = $this->getLibraryId())) {
                $library = new Media_Model_Library();
                $library->setName("product_".uniqid())->save();
                $library_id = $library->getId();
                $this->setLibraryId($library_id);
            }

            foreach($picture_list as $picture) {

                if($picture != "") {
                    $image = new Media_Model_Library_Image();
                    $img_data = array(
                        "link" => $picture,
                        "can_be_colorized" => 0,
                        "library_id" => $library_id
                    );
                    $image->setData($img_data)->save();
                }

            }
        }
    }

    public function deletePictures() {
        if($picture_list = $this->getRemovePicture()) {
            foreach($picture_list as $picture) {
                if($picture != "") {
                    $image = new Media_Model_Library_Image();
                    $image->find($picture);
                    if($image->getId()) {
                        unlink(Application_Model_Application::getBaseImagePath().$image->getLink());
                        $image->delete();
                    }
                }

            }
        }
    }

    public function deleteAllFormats() {
        if($this->getId()) {
            $this->getTable()->deleteAllFormats($this->getId());
        }
    }

    public function getAppIdByProduct() {
        return $this->getTable()->getAppIdByProduct();
    }

    public function createDummyContents($option_value, $design, $category) {

        $option = new Application_Model_Option();
        $option->find($option_value->getOptionId());

        $dummy_content_xml = $this->_getDummyXml($design, $category);

        if($option->getCode() == "set_meal") {

            foreach ($dummy_content_xml->set_meal->children() as $content) {
                $this->unsData();

                $this->addData((array) $content)
                    ->setValueId($option_value->getId())
                    ->save()
                ;
            }
        }
    }

    public function copyTo($option) {

        $this->copyPictureTo($option);
        $this->setId(null)
            ->setValueId($option->getId())
            ->save()
        ;

        return $this;
    }

    public function copyPictureTo($option) {

        if($image_url = $this->getPictureUrl()) {

            $file = pathinfo($image_url);
            $filename = $file['basename'];

            $relativePath = $option->getRelativePath();
            $folder = Core_Model_Directory::getBasePathTo(Application_Model_Application::PATH_IMAGE.'/'.$relativePath);

            if(!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }

            $img_src = Core_Model_Directory::getBasePathTo($image_url);
            $img_dst = $folder.'/'.$filename;

            if(copy($img_src, $img_dst)) {
                $this->setPicture($relativePath.'/'.$filename);
            }
        }

    }

    public function finalizeImport($got_heading, $data = null, $line, $full_data = null) {
        //When importing a product :
        //- create the product's library
        //- create all foreign key/records if heading are specified
        //     could be categories ids, formats, taxes, options

        $finalize_errors = array();

        //Library
        $library = new Media_Model_Library();
        $library->setName('product_'.$this->getId())->save();
        $this->setLibraryId($library->getId())->save();

        $options = array();
        if($got_heading) {
            $nb_format = 0;
            foreach (array_keys($data) as $heading) {
                //Categories
                if ($heading == "categories_ids") {
                    if($data["categories_ids"]) {
                        $tab_categories = explode(";", $data["categories_ids"]);
                        if (count($tab_categories) > 0) {
                            $categ = new Folder_Model_Category();
                            $error_categ = false;
                            foreach($tab_categories as $category) {
                                $categ->find($category);
                                if(!$categ->getId()) {
                                    $error_categ = true;
                                }
                            }

                            if(!$error_categ) {
                                $this->setNewCategoryIds($tab_categories);
                            } else {
                                $finalize_errors[] = $this->_("The categories for line %s couldn't be read.", $line);
                            }
                        } else {
                            $finalize_errors[] = $this->_("The categories for line %s couldn't be read.", $line);
                        }
                    }
                }

                //Formats
                //has to be formatted: format_name;format_price
                if (strripos($heading,"format_") !== FALSE) {
                    if($data[$heading]) {
                        $tab_format = explode(";", $data[$heading]);
                        if (count($tab_format) == 2 && $nb_format < 5) {
                            $option_data = array(
                                "title" => $tab_format[0],
                                "price" => $tab_format[1],
                                "option_id" => null
                            );
                            $options[] = $option_data;
                        } else {
                            $finalize_errors[] = $this->_("The formats for line %s couldn't be read.", $line);
                        }
                        $nb_format++;
                    }
                }

                //Options
                //has to be formatted: option_id;option_price
                if (strripos($heading,"option_") !== FALSE) {
                    if($data[$heading]) {
                        $tab_option = explode(";", $data[$heading]);
                        if (count($tab_option) == 2) {
                            $groups = array();
                            $option_id = $tab_option[0];
                            $option_price = $tab_option[1];
                            $option_object = new Catalog_Model_Product_Group_Option();
                            $option_object->find($option_id);
                            if ($option_object->getId()) {
                                $group_id = $option_object->getGroupId();
                                $groups = array(
                                    "group_id" => $group_id,
                                    "product_id" => $this->getId(),
                                    "new_option_value" => array()
                                );
                                $groups["new_option_value"][$option_id] = array(
                                    "option_id" => $option_id,
                                    "price" => $option_price
                                );

                                $group = new Catalog_Model_Product_Group_Value();
                                try {
                                    $group->addData($groups)->save();
                                } catch (Exception $e) {
                                    $finalize_errors[] = $this->_("The options for line %s couldn't be saved.", $line);
                                }
                            }
                        } else {
                            $finalize_errors[] = $this->_("The options for line %s couldn't be read.", $line);
                        }
                    }
                }
            }
        }

        if($nb_format>0) {
            $this->_instanceSingleton = null;
            $this->setData("option", $options);
        } else {
            $this->setType("simple");
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



}