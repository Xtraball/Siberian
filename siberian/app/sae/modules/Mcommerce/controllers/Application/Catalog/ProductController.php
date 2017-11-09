<?php

class Mcommerce_Application_Catalog_ProductController extends Application_Controller_Default_Ajax {

    public function editAction() {

        $product = new Catalog_Model_Product();
        if($id = $this->getRequest()->getParam('product_id')) {
            $product->find($id);
            if($product->getId() AND $this->getCurrentOptionValue()->getId() != $product->getValueId()) {
                throw new Exception($this->_('An error occurred during the process. Please try again later.'));
            }
        }

        $mcommerce = $this->getApplication()->getPage('m_commerce')->getObject();

        $html = $this->getLayout()->addPartial('store_form', 'admin_view_default', 'mcommerce/application/edit/catalog/products/edit.phtml')
            ->setCurrentMcommerce($mcommerce)
            ->setOptionValue($mcommerce->getCatalog())
            ->setCurrentProduct($product)
            ->toHtml();

        $html = array('form_html' => $html);

        $this->_sendHtml($html);

    }

    public function editpostAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {
                $isNew = false;
                $option = $this->getCurrentOptionValue();
                $application = $this->getApplication();
                $product = new Catalog_Model_Product();
                if(!empty($datas['product_id'])) {
                    $product->find($datas['product_id']);
                    if($product->getId() AND $option->getId() != $product->getValueId()) {
                        throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                    }
                }

                if(!$product->getId()) {
                    $datas['value_id'] = $option->getId();
                    $datas['mcommerce_id'] = $this->getApplication()->getPage('m_commerce')->getObject()->getId();
                    $isNew = true;
                }

                if(!empty($datas['picture_list']) AND (count($datas['picture_list'])) > 0) {
                    foreach($datas['picture_list'] as $key => $picture) {
                        if($picture != "") {
                            $relative_path = '/features/mcommerce/product/';
                            $folder = Application_Model_Application::getBaseImagePath() . $relative_path;
                            $path = Application_Model_Application::getBaseImagePath() . $relative_path;
                            $file = Core_Model_Directory::getTmpDirectory(true) . '/' . $picture;

                            if (file_exists($file)) {
                                if (!is_dir($path)) mkdir($path, 0777, true);
                                if (!copy($file, $folder . $picture)) {
                                    throw new exception($this->_('An error occurred while saving. Please try again later.'));
                                } else {
                                    $datas['picture_list'][$key] = $relative_path . $picture;
                                }
                            }
                        }
                    }
                }

                //if((!$product->getId() AND empty($datas['is_multiple'])) OR ($product->getId() AND $product->getData('type') != 'format' AND isset($datas['option']))) unset($datas['option']);
//                if(isset($datas['option'])) unset($datas["price"]);

                if($datas['is_multiple']=="0") {
                    $product->setData('type','simple');
                    unset($datas['option']);

                    if($product->getId() AND $product->getData('type') == 'format') {
                        $product->deleteAllFormats();
                    }
                } else {
                    $product->setPrice(null);
                }

                $product->addData($datas);

                $isDeleted = $product->getIsDeleted();
                $productId = $product->getId();

                //If the product is deleted, we need to delete its library
                if($isDeleted) {
                    $library = new Media_Model_Library();
                    $library->find($product->getLibraryId());
                    if($library->getId()) {
                        $library->delete();
                    }
                }

                $product->save();

                if(!$isDeleted) {
                    $productId = $product->getId();
                }

                if(!empty($datas['group']) AND !$isDeleted) {

                    $group_ids = array();
                    $groups = $product->getGroups();
                    foreach($datas['group'] as $group_datas) {
                        if(!empty($group_datas['group_id'])) $group_ids[] = $group_datas['group_id'];
                    }
                    // Supprime tous les groups qui ont été décochés
                    foreach($groups as $group) {
                        if(!in_array($group->getGroupId(), $group_ids)) $group->delete();
                    }

                    foreach($datas['group'] as $group_datas) {
                        if(empty($group_datas['group_id'])) continue;

                        $group = new Catalog_Model_Product_Group_Value();
                        if(!$isNew) {
                            $group->find(array('product_id' => $product->getId(), 'group_id' => $group_datas['group_id']));
                            // Supprime le groupe dont toutes les options ont été décochées mais pas lui
                            if(empty($group_datas['new_option_value'])) $group_datas['is_deleted'] = 1;
                        }
                        if(!$group->getId()) {
                            $group_datas['product_id'] = $product->getId();
                        }

                        $group->addData($group_datas)->save();
                    }

                    foreach($product->getChoices() as $choice){
                        if(!in_array($choice->getGroupId(), $group_ids)){
                            $choice->delete();
                        }
                    }
                }


                $html = array(
                    'picture' => $datas['picture'],
                    'is_new' => (int) $isNew,
                    'is_deleted' => (int) $isDeleted,
                    'product_id' => $productId,
                    'success' => '1',
                    'success_message' => $this->_('Product successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

                $html['product'] = array(
                    'name' => Core_Model_Lib_String::truncate($product->getName(), 25),
                    'description' => Core_Model_Lib_String::truncate($product->getDescription(), 25),
                    'formatted_price' => $product->getFormattedPrice($application->getCountryCode())
                );

            }
            catch(Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => Zend_Debug::dump($e),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->_sendHtml($html);

        }

    }

    public function duplicateAction() {
        if($id = $this->getRequest()->getParam('product_id')) {
            $product = new Catalog_Model_Product();
            $product->find($id);

            if($product->getId()) {
                $existing_formats = $product->getType()->getOptions();
                $existing_options = $product->getGroups();
                $existing_categories = $product->getCategoryIds();

                //Duplicate product himself
                $product->setName($product->getName()." (Copy)");
                $product->setId(null);
                $product->setData('new_category_ids',$existing_categories);

                //Duplicate formats
                if($product->getData("type") == "format") {
                    $options = array();
                    foreach ($existing_formats as $key => $option) {
                        $options["new_" . $key] = array(
                            "title" => $option->getData("title"),
                            "price" => $option->getData("price"),
                            "option_id" => null,
                            "is_deleted" => ""
                        );
                    }

                    $product->getType()->setOptions($options);

                }

                $product->save();

                //Duplicate options
                foreach($existing_options as $option) {
                    $old_option_id = $option->getValueId();
                    $option->setProductId($product->getProductId())
                        ->setId(null)
                        ->save();

                    $options_values = new Catalog_Model_Product_Group_Option_Value();
                    $options_values = $options_values->findAll(array("group_value_id" => $old_option_id));

                    foreach($options_values as $option_value) {
                        $new_option = new Catalog_Model_Product_Group_Option_Value();
                        $new_option->find(array("group_value_id" => $option->getValueId(), "option_id" => $option_value->getOptionId()));
                        if(!$new_option->getId()) {
                            $option_data = array(
                                "group_value_id" => $option->getValueId(),
                                "option_id" => $option_value->getOptionId(),
                                "price" => $option_value->getPrice()
                            );
                            $new_option->setData($option_data)->save();
                        }
                    }
                }

                //Duplicate pictures
                $library = new Media_Model_Library();
                $library_name = "product_".$product->getProductId();
                $library->setName($library_name)->save();
                foreach($product->getLibraryPictures() as $picture) {
                    $file = Core_Model_Directory::getBasePathTo($picture["url"]);
                    $relative_path = '/features/mcommerce/product/';
                    if (file_exists($file)) {
                        $name = explode('.',end(explode('/',$file)));
                        $folder = $relative_path.end(explode('/',$file));
                        $uniq = uniqid();
                        $new_file = str_replace($name[0],$uniq,$file);
                        $folder = str_replace($name[0],$uniq,$folder);
                        copy($file, $new_file);
                    }
                    $data = array('library_id' => $library->getId(), 'link' => $folder, 'can_be_colorized' => 0);
                    $image = new Media_Model_Library_Image();
                    $image->setData($data)->save();
                }

                $product->setLibraryId($library->getId());
                $product->save();

            }

            $application = $this->getApplication();
            $html = array(
                'product_id' => $product->getProductId(),
                'product' => array(
                    'name' => $product->getName(),
                    'description' => html_entity_decode($product->getDescription()),
                    'formatted_price' => $product->getFormattedPrice($application->getCountryCode())
                ),
                'success' => '1',
                'success_message' => $this->_('Product successfully duplicated'),
                'message_timeout' => 2,
                'message_button' => 0,
                'message_loader' => 0
            );
            $this->_sendHtml($html);
        }
    }

}