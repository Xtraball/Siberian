<?php

class Catalog_Application_CategoryController extends Application_Controller_Default
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
        "sortcategories" => array(
            "tags" => array(
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
            ),
        ),
    );

    public function editpostAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                if(empty($datas['value_id'])) {
                    throw new Siberian_Exception(__('An error occurred while saving the category. Please try again later.'));
                }

                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);

                $data = array();
                $category = new Catalog_Model_Category();
                if(!empty($datas['category_id'])) {
                    $category->find($datas['category_id']);
                }
                $isNew = (bool) !$category->getId();

                if($category->getId() AND $category->getValueId() != $option_value->getId()) {
                    throw new Siberian_Exception(__('An error occurred while saving the category. Please try again later.'));
                }

                if(!isset($datas['is_active'])) $datas['is_active'] = 1;

                $datas['value_id'] = $option_value->getId();

                if(!empty($datas['is_deleted']) AND $category->getParentId()) {
                    foreach($category->getProducts() as $product) {
                        $product->setCategoryId($category->getParentId())->save();
                    }
                }

                $category->addData($datas);
                $category->save();
                $data = array(
                    'success' => 1,
                    'is_new' => (int) $isNew,
                    'parent_id' => $category->getParentId(),
                    'category_id' => $category->getId(),
                    'is_deleted' => $category->getIsDeleted() ? 1 : 0
                );
                if($isNew) {
                    if($category->getParentId()) {
                        $data['li_html'] = $this->getLayout()
                            ->addPartial('row', 'admin_view_default', 'catalog/application/edit/category/subcategory.phtml')
                            ->setCategory($category->getParent())
                            ->setSubcategory($category)
                            ->setOptionValue($option_value)
                            ->toHtml()
                        ;
                    }
                    else {
                        $data['category_html'] = $this->getLayout()
                            ->addPartial('row', 'admin_view_default', 'catalog/application/edit/category.phtml')
                            ->setCategory($category)
                            ->setOptionValue($option_value)
                            ->toHtml()
                        ;
                    }
                }

                /** Update touch date, then never expires (until next touch) */
                $option_value
                    ->touch()
                    ->expires(-1);

            }
            catch(Exception $e) {
                $data['message'] = $e->getMessage();
            }

            $this->_sendJson($data);

        }

        return $this;

    }

    public function sortcategoriesAction() {

        if ($rows = $this->getRequest()->getParam('category') OR $rows = $this->getRequest()->getParam('row')) {

            $data = array();
            try {

                if(!$this->getCurrentOptionValue()) {
                    throw new Exception(__('#112: An error occurred while saving'));
                }

                $category = new Catalog_Model_Category();

                $categories = $category->findByValueId($this->getCurrentOptionValue()->getId());
                $category_ids = array();

                foreach ($categories as $category) {
                    $category_ids[] = $category->getId();
                }

                foreach ($rows as $row) {
                    if (!in_array($row, $category_ids))
                        throw new Exception(__("An error occurred while saving. One of your categories could not be identified."));
                }

                $category->updatePosition($rows);

                $data = array(
                    'success' => 1,
                );
            } catch (Exception $e) {
                $data = array('message' => $e->getMessage());
            }

            $this->_sendJson($data);
        }
    }
}