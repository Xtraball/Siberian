<?php

class Catalog_Application_ProductController extends Application_Controller_Default
{

    /**
     * @var array
     */
    public $cache_triggers = array(
        "editpost" => array(
            "tags" => array(
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
            ),
        ),
        "sortproducts" => array(
            "tags" => array(
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
            ),
        ),
    );

    public function editAction() {
        if($this->getCurrentOptionValue()) {
            $product = new Catalog_Model_Product();
            if($product_id = $this->getRequest()->getParam('id')) {
                $product->find($product_id);
                if($product->getId() AND $product->getValueId() != $this->getCurrentOptionValue()->getId()) {
                    throw new Exception(__('An error occurred while loading your product.'));
                }
            }
            else if($category_id = $this->getRequest()->getParam('category_id')) {
                $category = new Catalog_Model_Category();
                $category->find($category_id);
                if($category->getValueId() != $this->getCurrentOptionValue()->getId()) {
                    $category = null;
                    $category_id = Catalog_Model_Category();
                }
                $product->setCategory($category)->setCategoryId($category_id);
            }
            $this->loadPartials(null, false);
            $this->getLayout()->getPartial('content')->setOptionValue($this->getCurrentOptionValue())->setProduct($product);

            $html = $this->getLayout()->render();
            $this->getLayout()->setHtml($html);
        }
    }

    public function createAction() {
        $this->loadPartials(null, false);
    }

    public function editpostAction() {

        if($data = $this->getRequest()->getPost()) {

            try {

                if(empty($data['value_id'])) throw new Exception(__('An error occurred while saving the product. Please try again later.'));

                $option_value = new Application_Model_Option_Value();
                $option_value->find($data['value_id']);

                $html = array();
                $product = new Catalog_Model_Product();
                if(!empty($data['product_id'])) $product->find($data['product_id']);
                $isNew = (bool) !$product->getId();
                $isDeleted = !empty($data['is_deleted']);

                if($product->getId() AND $product->getValueId() != $option_value->getId()) {
                    throw new Exception(__('An error occurred while saving the product. Please try again later.'));
                }

                if(!$isDeleted) {
                    if(!isset($data['is_active'])) $data['is_active'] = 1;

                    $data['value_id'] = $option_value->getValueId();

                    $parent_id = $data['category_id'];
                    if(!empty($data['subcategory_id'])) $data['category_id'] = $data['subcategory_id'];

                    if(!empty($data['picture'])) {
                        if(!file_exists(Core_Model_Directory::getTmpDirectory(true)."/".$data['picture'])) {
                            unset($data['picture']);
                        } else {

                            $illus_relative_path = $option_value->getImagePathTo();
                            $folder = Application_Model_Application::getBaseImagePath().$illus_relative_path;
                            $file = pathinfo(Core_Model_Directory::getBasePathTo($data['picture']));
                            $filename = $file['basename'];
                            $img_src = Core_Model_Directory::getTmpDirectory(true)."/".$data['picture'];
                            $img_dst = $folder.'/'.$filename;

                            if (!is_dir($folder)) {
                                mkdir($folder, 0777, true);
                            }

                            if(!copy($img_src, $img_dst)) {
                                throw new exception(__('An error occurred while saving your picture. Please try againg later.'));
                            } else {
                                $data['picture'] = $illus_relative_path.'/'.$filename;
                            }
                        }
                    }
                    
                }

                if((!$product->getId() AND empty($data['is_multiple'])) OR ($product->getId() AND $product->getData('type') != 'format' AND isset($data['option']))) unset($data['option']);
                $product->addData($data);
                $product->save();
                $html = array('success' => 1);

                if(!$isDeleted) {

                    $product_id = $product->getId();
                    $product = new Catalog_Model_Product();
                    $product->find($product_id);

                    $html = array(
                        'success' => 1,
                        'product_id' => $product->getId(),
                        'parent_id' => $parent_id,
                        'category_id' => $data['category_id']
                    );

                    $html['product_html'] = $this->getLayout()
                        ->addPartial('row', 'admin_view_default', 'catalog/application/edit/category/product.phtml')
                        ->setProduct($product)
                        ->setOptionValue($option_value)
                        ->toHtml()
                    ;
                }

                /** Update touch date, then never expires (until next touch) */
                $option_value
                    ->touch()
                    ->expires(-1);

            }
            catch(Exception $e) {
                $html['message'] = $e->getMessage();
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));

        }

    }

    public function sortproductsAction() {

        if ($rows = $this->getRequest()->getParam('product')) {

            $html = array();
            try {

                if(!$this->getCurrentOptionValue()) {
                    throw new Exception('Une erreur est survenue lors de la sauvegarde.');
                }

                $product = new Catalog_Model_Product();

                $products = $product->findByValueId($this->getCurrentOptionValue()->getId());
                $product_ids = array();

                foreach ($products as $product) {
                    $product_ids[] = $product->getId();
                }

                foreach ($rows as $key => $row) {
                    if (!in_array($row, $product_ids)) {
                        throw new Exception(__('An error occurred while saving. One of your products could not be identified.'));
                    }
                }
                $product->updatePosition($rows);

                $html = array(
                    'success' => 1
                );

                /** Update touch date, then never expires (until next touch) */
                $this->getCurrentOptionValue()
                    ->touch()
                    ->expires(-1);

            } catch (Exception $e) {
                $html = array('message' => $e->getMessage());
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function validatecropAction() {
        if($datas = $this->getRequest()->getPost()) {
            try {
                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->savecrop($datas);
                $datas = array(
                    'success' => 1,
                    'file' => $file,
                    'message_success' => __('Info successfully saved'),
                    'message_button' => 0,
                    'message_timeout' => 2,
                );
            } catch (Exception $e) {
                $datas = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }
            $this->getLayout()->setHtml(Zend_Json::encode($datas));
         }
    }

}
