<?php

class Mcommerce_Model_Mcommerce extends Core_Model_Default
{

    protected $_default_store;
    protected $_stores;
    protected $_carts;
    protected $_orders;
    protected $_taxes;
    protected $_catalog;
    protected $_root_category;
    protected $_products;

    protected static $_importable_object = array(
        "Stores" => array(
            "model" => "Mcommerce_Model_Store",
            "table" => "mcommerce_store"
        ),
        "Products" => array(
            "model" => "Catalog_Model_Product",
            "table" => "catalog_product"
        ),
        "Categories" => array(
            "model" => "Catalog_Model_Category",
            "table" => "folder_category"
        )
    );

    public function __construct($params = array())
    {
        parent::__construct($params);
        $this->_db_table = 'Mcommerce_Model_Db_Table_Mcommerce';
        return $this;
    }

    public function deleteFeature()
    {

        $products = $this->getProducts();
        $catalog = $this->getCatalog();

        foreach ($this->getOrders() as $order) {
            $order->delete();
        }

        foreach ($this->getCarts() as $cart) {
            $cart->delete();
        }

        foreach ($this->getTaxes() as $tax) {
            $tax->delete();
        }

        foreach ($this->getStores() as $store) {
            $store->delete();
        }

        $this->delete();

        foreach ($products as $product) {
            $product->delete();
        }

        $catalog->delete();

        return $this;

    }

    public function prepareFeature($option_value)
    {

        parent::prepareFeature($option_value);
        if (!$this->getId()) {
            $catalog_option = new Application_Model_Option();
            $catalog_option->find('catalog', 'code');
            $catalog_option_value = new Application_Model_Option_Value();
            // Ajoute les données
            $catalog_option_value->addData(array(
                'option_id' => $catalog_option->getId(),
                'app_id' => $option_value->getAppId(),
                'position' => 0,
                'is_active' => 1,
                'is_visible' => 0
            ))->save();

            $root_category = new Folder_Model_Category();
            $root_category->setTitle($this->_('Category'))
                ->setPos(1)
                ->save();

            $this->setValueId($option_value->getId())
                ->setCatalogValueId($catalog_option_value->getId())
                ->setRootCategoryId($root_category->getId())
                ->save();

        }

        return $this;
    }

    public function getSettings()
    {

        return array(
            "phone" => array("rule" => $this->getPhone(), "label" => "Phone"),
            "birthday" => array("rule" => $this->getBirthday(), "label" => "Date of birth"),
            "delivery_address" => array("rule" => $this->getDeliveryAddress(), "label" => __("Delivery address")),
            "invoicing_address" => array("rule" => $this->getInvoicingAddress(), "label" => __("Invoicing address"))
        );

    }

    /**
     * Validates customer data in case of legacy application
     *
     * @param array $data
     * @return array
     */
    public function validateLegacyCustomer($controller, $customer) {
        $required_fields = array(
            $controller->_('Firstname') => 'firstname',
            $controller->_('Lastname') => 'lastname',
            $controller->_('Email') => 'email',
            $controller->_('Phone') => 'phone'
        );
        $errors = array();
        foreach ($required_fields as $label => $field) {
            if (empty($customer[$field])) $errors[] = $label;
        }
        return $errors;
    }

    public function validateCustomer($controller, $data)
    {
        $errors = array_merge(
            $this->_validatePhone($controller, $data['metadatas']),
            $this->_validateBirthday($controller, $data['metadatas']),
            $this->_validateInvoicingAddress($controller, $data['metadatas']),
            $this->_validateDeliveryAddress($controller, $data['metadatas']));
        return $errors;
    }

    protected function _validatePhone($controller, $data)
    {
        $not_empty = new Zend_Validate_NotEmpty();
        if ($data['phone'] && $this->getPhone() != "hidden") {
            if (!$not_empty->isValid($data['phone'])) {
                return array($controller->_('Phone'));
            };
        } else if ($this->getPhone() == "mandatory") {
            return array($controller->_('Phone'));
        }
        return array();
    }

    protected function _validateBirthday($controller, $data)
    {
        $data_validator = new Zend_Validate_Date();
        if ($data['birthday'] && $this->getBirthday() != "hidden") {
            $date = new Zend_Date($data['birthday']);
            if (!$data_validator->isValid($date)) {
                return array($controller->_('Birthday'));
            };
        } else if ($this->getBirthday() == "mandatory") {
            return array($controller->_('Birthday'));
        }
        return array();
    }

    protected function _validateDeliveryAddress($controller, $data)
    {
        $mandatory = $this->getDeliveryAddress() == "mandatory";
        if (($mandatory && !$data['delivery_address']) ||
            !$this->_addressComponentsAreValid($data['delivery_address'], $mandatory)
        ) {
            return array($controller->_('Delivery address'));
        }
        return array();
    }

    protected function _validateInvoicingAddress($controller, $data) {
        $mandatory = $this->getInvoicingAddress() == "mandatory";
        if (($mandatory && !$data['invoicing_address']) ||
            !$this->_addressComponentsAreValid($data['invoicing_address'], $mandatory)
        ) {
            return array($controller->_('Invoicing address'));
        }
        return array();
    }

    protected function _addressComponentsAreValid($data, $isMandatory = true) {
        return
            $this->_addressComponentIsValid($data['street'], $isMandatory) &&
            $this->_addressComponentIsValid($data['postcode'], $isMandatory) &&
            $this->_addressComponentIsValid($data['city'], $isMandatory) &&
            $this->_addressComponentIsValid($data['country'], $isMandatory);
    }

    protected function _addressComponentIsValid($data, $isMandatory) {
        $notempty = new Zend_Validate_NotEmpty();

        return ($isMandatory) ? $notempty->isValid($data) : true;
    }

    public function getOptionValue() {
        $value = new Application_Model_Option_Value();
        $value->find($this->getValueId());
        return $value;
    }

    protected function _resetMetada($data) {
        $metadata = new Application_Model_Option_Value_Metadata();
        $metadata->find($data);
        $metadata->delete();
        return $this;
    }

    protected function _setMetada($data) {
        $metadata = new Application_Model_Option_Value_Metadata($data);
        $metadata->save();
        return $this;
    }

    /**
     * Saves the add_tip metadata
     *
     * @param $add_tip
     * @return $this
     */
    public function setAddTip($add_tip) {

        $data = array(
            "value_id" => $this->getValueId(),
            "code" => "add_tip",
            "type" => "boolean"
        );

        $this->_resetMetada($data);

        $data['payload'] = $add_tip;
        $this->_setMetada($data);

        return $this;
    }

    /**
     * Retrieves the add_tip metadatum associated with the corresponding feature
     *
     * @return bool
     */
    public function getAddTip(){
        $metadata = new Application_Model_Option_Value_Metadata();
        $metadata->find(array(
            "value_id" => $this->getValueId(),
            "code" => "add_tip"
        ));
        return $metadata->getPayload();
    }

    public function setGuestMode($guestmode) {

        $data = array(
            "value_id" => $this->getValueId(),
            "code" => "guest_mode",
            "type" => "boolean"
        );

        $this->_resetMetada($data);

        $data['payload'] = $guestmode;
        $this->_setMetada($data);

        return $this;
    }

    public function getGuestMode(){
        $metadata = new Application_Model_Option_Value_Metadata();
        $metadata->find(array(
            "value_id" => $this->getValueId(),
            "code" => "guest_mode"
        ));
        return $metadata->getPayload();
    }

    public function getStores()
    {

        if (!$this->_stores) {
            $store = new Mcommerce_Model_Store();
            $this->_stores = $store->findAll(array('mcommerce_id' => $this->getId(), 'is_visible' => 1));
        }

        return $this->_stores;

    }

    public function getCarts()
    {

        if (!$this->_carts) {
            $cart = new Mcommerce_Model_Cart();
            $this->_carts = $cart->findAll(array('mcommerce_id' => $this->getId()), 'cart_id DESC');
        }

        return $this->_carts;

    }

    public function getOrders()
    {

        if (!$this->_orders) {
            $order = new Mcommerce_Model_Order();
            $this->_orders = $order->findAll(array('mcommerce_id' => $this->getId()), 'order_id DESC');
        }

        return $this->_orders;

    }

    public function getDefaultStore()
    {

        if (!$this->_default_store) {
            $this->_default_store = $this->getStores()->rewind()->current();
            if (!$this->_default_store) $this->_default_store = new Mcommerce_Model_Store();
        }

        return $this->_default_store;

    }

    public function getRootCategory()
    {

        if (!$this->_root_category) {
            $this->_root_category = new Folder_Model_Category();
            $this->_root_category->find($this->getRootCategoryId());
            $this->_root_category->setIsRootCategory(1);
        }

        return $this->_root_category;
    }

    public function getTaxes()
    {

        if (!$this->_taxes) {
            $tax = new Mcommerce_Model_Tax();
            $this->_taxes = $tax->findAll(array('mcommerce_id' => $this->getId()));
        }

        return $this->_taxes;

    }

    public function getCatalog()
    {

        if (!$this->_catalog) {
            $this->_catalog = new Application_Model_Option_Value();
            $this->_catalog->find($this->getCatalogValueId());
//            $this->_catalog = $catalog_value->getObject();
        }

        return $this->_catalog;

    }

    public function getProducts()
    {

        if (!$this->_products) {
            $product = new Catalog_Model_Product();
            $this->_products = $product->findAll(array('mcommerce_id' => $this->getId()));
        }

        return $this->_products;

    }

    public function createDummyContents($option_value, $design, $category)
    {

        $dummy_content_xml = $this->_getDummyXml($design, $category);

        foreach ($dummy_content_xml->children() as $content) {
            //catalog
            $catalog_category = new Application_Model_Option();
            $catalog_category->find("catalog", "code");

            $catalog_category_option_value = new Application_Model_Option_Value();

            $datas = array(
                "app_id" => $this->getApplication()->getId(),
                "option_id" => $catalog_category->getId(),
                "layout_id" => $this->getApplication()->getLayout()->getId(),
                "is_visible" => 0
            );

            $catalog_category_option_value->addData($datas)
                ->save();

            //folder_category
            $folder_category = new Folder_Model_Category();
            $datas_category = array(
                "title" => (string)$content->stores->category->title,
                "subtitle" => (string)$content->stores->category->subtitle ? (string)$content->stores->category->subtitle : NULL,
                "picture" => (string)$content->stores->category->picture ? (string)$content->stores->category->picture : NULL,
                "type_id" => (string)$content->stores->category->type_id
            );

            $folder_category->addData($datas_category)
                ->save();

            //mcommerce
            $this->unsData();

            $this->setValueId($option_value->getId())
                ->setCatalogValueId($catalog_category_option_value->getId())
                ->setRootCategoryId($folder_category->getId())
                ->save();

            if ($content->stores->store) {
                //mcommerce_store
                $store = new Mcommerce_Model_Store();

                $datas_delivery_method = array();
                $i = 0;
                foreach ($content->stores->store_delivery_methods->method_code as $delivery_method_code) {
                    $delivery_method = new Mcommerce_Model_Delivery_Method();
                    $delivery_method->find((string)$delivery_method_code, "code");

                    $datas_delivery_method[$i++]["method_id"] = $delivery_method->getId();
                }

                $datas_payment_method = array();
                $i = 0;
                foreach ($content->stores->store_payment_methods->method_code as $payment_method_code) {
                    $payment_method = new Mcommerce_Model_Payment_Method();
                    $payment_method->find((string)$payment_method_code, "code");

                    $datas_payment_method[$i++]["method_id"] = $payment_method->getId();
                }

                $store->setData((array)$content->stores->store)
                    ->setNewDeliveryMethods($datas_delivery_method)
                    ->setNewPaymentMethods($datas_payment_method)
                    ->setMcommerceId($this->getId())
                    ->save();

            }

            //store printer
            if ($content->stores->store_printer) {
                $printer = new Mcommerce_Model_Store_Printer();
                $printer->addData((array)$content->stores->store_printer)
                    ->setStoreId($store->getId())
                    ->save();
            }

            //category products
            if ($content->stores->category->products) {
                $tax = new Mcommerce_Model_Tax();
                $tax->find($this->getId(), "mcommerce_id");

                foreach ($content->stores->category->products->product as $product) {

                    $product_data = (array)$product->content;
                    if ($product->content->pictures) {
                        $i = 0;
                        foreach ($product->content->pictures->children() as $picture) {
                            $product_data["picture_list"][$i] = (string)$picture;
                            $i++;
                        }
                        unset($product_data["pictures"]);
                    }

                    $product_obj = new Catalog_Model_Product();
                    $product_obj->addData($product_data)
                        ->setValueId($catalog_category_option_value->getId())
                        ->setMcommerceId($this->getId())
                        ->setTaxId($tax->getId())
                        ->save();

                    if ($product->options) {
                        foreach ($product->options->children() as $option) {

                            if ($option->group) {
                                $data_option = array();
                                $i = 0;
                                foreach ($option->group->children() as $option_value) {
                                    $data_option["new_" . $i++]["name"] = (string)$option_value;
                                }

                                $group = new Catalog_Model_Product_Group();
                                $data_group = array(
                                    "title" => (string)$option->title,
                                    "app_id" => $this->getApplication()->getId()
                                );

                                $group->addData($data_group)
                                    ->setNewOption($data_option)
                                    ->save();

                                $data_option_value = array();
                                foreach ($option->group as $option_value) {
                                    $group_option_value = new Catalog_Model_Product_Group_Option();
                                    $group_option_value->find((string)$option_value->name, "name");

                                    $data_option_value[$group_option_value->getId()]["option_id"] = $group_option_value->getId();
                                    $data_option_value[$group_option_value->getId()]["price"] = (string)$option_value->value;
                                }

                                $product_groupe = new Catalog_Model_Product_Group_Value();
                                $product_groupe->setProductId($product_obj->getId())
                                    ->setGroupId($group->getId())
                                    ->setNewOptionValue($data_option_value)
                                    ->save();

                            }

                        }
                    }
                }
            }

            //subcategory folder
            if ($content->stores->category->subcategory) {
                foreach ($content->stores->category->subcategory as $subcategory) {
                    $folder_subcategory = new Folder_Model_Category();
                    $datas_subcategory = array(
                        "title" => (string)(string)$subcategory->title,
                        "subtitle" => (string)$subcategory->subtitle ? (string)$subcategory->subtitle : NULL,
                        "picture" => (string)$subcategory->picture ? (string)$subcategory->picture : NULL,
                        "type_id" => (string)$subcategory->type_id,
                        "parent_id" => $folder_category->getId()
                    );

                    $folder_subcategory->addData($datas_subcategory)
                        ->save();

                    //sub category products
                    if ($subcategory->products) {
                        $tax = new Mcommerce_Model_Tax();
                        $tax->find($this->getId(), "mcommerce_id");

                        foreach ($subcategory->products->product as $subproduct) {

                            $subproduct_data = (array)$subproduct->content;
                            if ($subproduct->content->pictures) {
                                $i = 0;
                                foreach ($subproduct->content->pictures->children() as $picture) {
                                    $subproduct_data["picture_list"][$i] = (string)$picture;
                                    $i++;
                                }
                                unset($subproduct_data["pictures"]);
                            }

                            $product_obj = new Catalog_Model_Product();
                            $product_obj->addData($subproduct_data)
                                ->setValueId($catalog_category_option_value->getId())
                                ->setMcommerceId($this->getId())
                                ->setTaxId($tax->getId())
                                ->setNewCategoryIds(array(0 => $folder_subcategory->getId()))
                                ->save();

                            //subproducts options
                            if ($subproduct->options) {
                                foreach ($subproduct->options->children() as $option) {

                                    if ($option->group) {

                                        $data_option = array();
                                        $i = 0;
                                        foreach ($option->group as $option_value) {
                                            $data_option["new_" . $i++]["name"] = (string)$option_value->name;
                                        }

                                        $group = new Catalog_Model_Product_Group();
                                        $group->setTitle((string)$option->title)
                                            ->setAppId($this->getApplication()->getId())
                                            ->setNewOption($data_option)
                                            ->save();

                                        $data_option_value = array();
                                        foreach ($option->group as $option_value) {
                                            $group_option_value = new Catalog_Model_Product_Group_Option();
                                            $group_option_value->find((string)$option_value->name, "name");

                                            $data_option_value[$group_option_value->getId()]["option_id"] = $group_option_value->getId();
                                            $data_option_value[$group_option_value->getId()]["price"] = (string)$option_value->value;
                                        }

                                        $product_groupe = new Catalog_Model_Product_Group_Value();
                                        $product_groupe->setProductId($product_obj->getId())
                                            ->setGroupId($group->getId())
                                            ->setNewOptionValue($data_option_value)
                                            ->save();

                                    }

                                }
                            }
                        }
                    }
                }
            }
        }

    }

    public function copyTo($option)
    {

        $old_product_ids = array();
        $old_group_ids = array();
        $old_option_ids = array();

        $root_category = $this->getRootCategory();
        $products = $this->getProducts();
        $stores = $this->getStores();
        $taxes = $this->getTaxes();
        $tax_ids = array();
        $this->setId(null)->setValueId($option->getId());

        $this->prepareFeature($option);

        $this->_root_category = null;

        $this->getRootCategory()->setTitle($root_category->getTitle())
            ->setSubtitle($root_category->getSubtitle())
            ->setPicture($root_category->getPicture());

        foreach ($taxes as $tax) {
            $old_tax_id = $tax->getId();
            $tax->setId(null)->setMcommerceId($this->getId())->save();
            $tax_ids[$old_tax_id] = $tax->getId();
        }

        foreach ($stores as $store) {

            $delivery_methods = $store->getDeliveryMethods();
            $payment_methods = $store->getDeliveryMethods();
            $printer = new Mcommerce_Model_Store_Printer();
            $printer->find($store->getId(), 'store_id');
            $data = $store->getData();

            // Delivery methods
            $data['new_delivery_methods'] = array();
            foreach ($delivery_methods as $delivery_method) {
                $tax_id = null;
                if ($delivery_method->getTaxId() AND !empty($tax_ids[$delivery_method->getTaxId()])) {
                    $tax_id = $tax_ids[$delivery_method->getTaxId()];
                }
                $data['new_delivery_methods'][] = array(
                    'method_id' => $delivery_method->getMethodId(),
                    'price' => $delivery_method->getPrice(),
                    'min_amount_for_free_delivery' => $delivery_method->getMinAmountForFreeDelivery(),
                    'tax_id' => $tax_id
                );
            }

            // Payment methods
            $data['new_payment_methods'] = array();
            foreach ($payment_methods as $payment_method) {
                $data['new_payment_methods'][] = array(
                    'method_id' => $payment_method->getMethodId(),
                    'user' => $payment_method->getUser(),
                    'password' => $payment_method->getPassword(),
                    'signature' => $payment_method->getSignature(),
                );
            }

            unset($data['id']);
            unset($data['store_id']);
            $data['mcommerce_id'] = $this->getId();

            $store->setId(null)->setMcommerceId($this->getId())->save();

            // Printer
            if ($printer->getId()) {
                $printer->setId(null)->setStoreId($store->getId())->save();
            }

        }

        $group = new Catalog_Model_Product_Group();
        $groups = $group->findAll(array('app_id' => $option->getOldAppId()));

        // Groups + Options
        foreach ($groups as $group) {

            $group_options = $group->getOptions();
            $old_group_id = $group->getId();
            $group->setId(null)->setAppId($option->getAppId())->save();

            $old_group_ids[$old_group_id] = $group->getId();

            foreach ($group_options as $group_option) {
                $old_option_id = $group_option->getId();
                $group_option->setId(null)
                    ->setGroupId($group->getId())
                    ->save();
                $old_option_ids[$old_option_id] = $group_option->getId();
            }

        }

        // Products
        foreach ($products as $product) {

            if ($product->getLibraryId()) {
                $old_library = new Media_Model_Library();
                $old_library->find($product->getLibraryId());
                $new_library = new Media_Model_Library();
                $new_library->setName("product_" . $product->getId())->save();
                $old_library->copyTo($new_library->getId(), $option);
                $product->setlibraryId($new_library->getId());
            }

            $old_product_id = $product->getId();
            $groups = $product->getGroups();
//            $product->copyTo($option);
            $tax_id = null;
            if ($product->getTaxId() AND !empty($tax_ids[$product->getTaxId()])) {
                $tax_id = $tax_ids[$product->getTaxId()];
            }
            $product->setId(null)->setValueId($this->getCatalogValueId())
                ->setMcommerceId($this->getId())
                ->setTaxId($tax_id)
                ->save();

            $old_product_ids[$old_product_id] = $product->getId();

            foreach ($groups as $group) {

                $options = $group->getOptions();
                $group_id = $group->getGroupId();
                $group->setId(null)->setProductId($product->getId())->setGroupId($old_group_ids[$group_id])->save();

                foreach ($options as $option) {
                    $option_id = $option->getOptionId();
                    $option->setId(null)->setOptionId($old_option_ids[$option_id])->setGroupValueId($group->getId())->save();
                }
            }
        }

        // Categories
        foreach ($root_category->getChildren() as $child) {
            $this->copyCategoryTo($child, $this->getRootCategory()->getId(), $old_product_ids);
        }

        foreach ($this->getProducts() as $product) $product->save();

        return $this;
    }

    public function copyCategoryTo($category, $parent_id = null, $old_product_ids = array())
    {

        $category_products = $category->getProducts();

        $children = $category->getChildren();
        $category->setId(null)->setParentId($parent_id)->save();

        // Category / Products
        foreach ($category_products as $category_product) {

            $product_id = $old_product_ids[$category_product->getId()];
            $product = $this->getProducts()->findById($product_id);
            if ($product->getId()) {
                $new_category_ids = is_array($product->getNewCategoryIds()) ? $product->getNewCategoryIds() : array();
                $new_category_ids[] = $category->getId();
                $product->setNewCategoryIds($new_category_ids);
            }

        }

        foreach ($children as $child) {
            $this->copyCategoryTo($child, $category->getId(), $old_product_ids);
        }
    }

    public static function getImportableObjects()
    {
        return self::$_importable_object;
    }

    public function getFeaturePaths($option_value)
    {
        if (!$this->isCacheable()) return array();

        // Categories paths
        $paths = array();
        $paths[] = $option_value->getPath("findall", array('value_id' => $option_value->getId()), false);

        $categories = $this->getRootCategory()->getChildren();

        foreach ($categories as $category) {
            $params = array(
                'value_id' => $option_value->getId(),
                'category_id' => $category->getId()
            );
            $paths[] = $option_value->getPath("findall", $params, false);

            // Subcategories paths
            $subcategories = $category->getChildren();
            foreach ($subcategories as $subcategory) {
                $params = array(
                    'value_id' => $option_value->getId(),
                    'category_id' => $subcategory->getId()
                );
                $paths[] = $option_value->getPath("findall", $params, false);
            }
        }

        if ($uri = $option_value->getMobileViewUri("find")) {

            $products = $this->getProducts();
            foreach ($products as $product) {
                $params = array(
                    "value_id" => $option_value->getId(),
                    "product_id" => $product->getId()
                );

                $paths[] = $option_value->getPath($uri, $params, false);
            }

        }

        return $paths;
    }

    public function getAppIdByMcommerceId()
    {
        return $this->getTable()->getAppIdByMcommerceId();
    }

    /**
     * Create or update the metadatum having $name and $type
     *
     * @param $name
     * @param $payload
     * @return $this
     */
    public function setMetadatum($name, $type, $payload) {
        $data = array(
            "value_id" => $this->getValueId(),
            "code" => $name,
            "type" => $type
        );

        $this->_resetMetadata($data);

        $data['payload'] = $payload;
        $this->_setMetadata($data);

        return $this;
    }

    /**
     * Clear the old metadatum defined by $data
     *
     * @param $data
     * @return $this
     */
    protected function _resetMetadata($data) {
        $metadata = new Application_Model_Option_Value_Metadata();
        $metadata->find($data);
        $metadata->delete();
        return $this;
    }

    /**
     * Save the new metadatum
     *
     * @param $data
     * @return $this
     */
    protected function _setMetadata($data) {
        $metadata = new Application_Model_Option_Value_Metadata($data);
        $metadata->save();
        return $this;
    }

    /**
     * Retrieves the metadatum having $name
     *
     * @return mixed
     */
    public function getMetadatum($name){
        $metadata = new Application_Model_Option_Value_Metadata();
        $metadata->find(array(
            "value_id" => $this->getValueId(),
            "code" => $name
        ));
        return $metadata->getPayload();
    }

    public function getPromos(){
        $promo = new Mcommerce_Model_Promo();
        $promos = $promo->findAll(array('mcommerce_id' => $this->getId(), 'hidden' => 0));
        return $promos;
    }
}
