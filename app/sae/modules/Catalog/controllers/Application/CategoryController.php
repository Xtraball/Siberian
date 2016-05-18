<?php

class Catalog_Application_CategoryController extends Application_Controller_Default
{

    public function editpostAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                if(empty($datas['value_id'])) throw new Exception($this->_('An error occurred while saving the category. Please try again later.'));

                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);

                $html = array();
                $category = new Catalog_Model_Category();
                if(!empty($datas['category_id'])) $category->find($datas['category_id']);
                $isNew = (bool) !$category->getId();

                if($category->getId() AND $category->getValueId() != $option_value->getId()) {
                    throw new Exception($this->_('An error occurred while saving the category. Please try again later.'));
                }

                if(!isset($datas['is_active'])) $datas['is_active'] = 1;

                $datas['value_id'] = $option_value->getId();

                if(!empty($datas['is_deleted']) AND $category->getParentId()) {
                    foreach($category->getProducts() as $product) {
                        $product->setCategoryId($category->getParentId())->save();
                    }
                }

//                $category->setPosIds(!empty($datas['outlets']) ? $datas['outlets'] : array());
                $category->addData($datas);
                $category->save();
                $html = array(
                    'success' => 1,
                    'is_new' => (int) $isNew,
                    'parent_id' => $category->getParentId(),
                    'category_id' => $category->getId(),
                    'is_deleted' => $category->getIsDeleted() ? 1 : 0
//                    'success_message' => 'Votre catégorie a été sauvegardée avec succès',
                );
                if($isNew) {
                    if($category->getParentId()) {
                        $html['li_html'] = $this->getLayout()
                            ->addPartial('row', 'admin_view_default', 'catalog/application/edit/category/subcategory.phtml')
                            ->setCategory($category->getParent())
                            ->setSubcategory($category)
                            ->setOptionValue($option_value)
                            ->toHtml()
                        ;
                    }
                    else {
                        $html['category_html'] = $this->getLayout()
                            ->addPartial('row', 'admin_view_default', 'catalog/application/edit/category.phtml')
                            ->setCategory($category)
                            ->setOptionValue($option_value)
                            ->toHtml()
                        ;
                    }
                }

            }
            catch(Exception $e) {
                $html['message'] = $e->getMessage();
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));

        }

        return $this;

    }

    public function sortcategoriesAction() {

        if ($rows = $this->getRequest()->getParam('category') OR $rows = $this->getRequest()->getParam('row')) {

            $html = array();
            try {

                if(!$this->getCurrentOptionValue()) {
                    throw new Exception($this->_('#112: An error occurred while saving'));
                }

                $category = new Catalog_Model_Category();

                $categories = $category->findByValueId($this->getCurrentOptionValue()->getId());
                $category_ids = array();

                foreach ($categories as $category) {
                    $category_ids[] = $category->getId();
                }

                foreach ($rows as $row) {
                    if (!in_array($row, $category_ids))
                        throw new Exception($this->_("An error occurred while saving. One of your categories could not be identified."));
                }

                $category->updatePosition($rows);

                $html = array(
                    'success' => 1,
//                    'success_message' => 'Sauvegarde effectuée avec succès',
//                    'message_button' => 0,
//                    'message_timeout' => 3,
//                    'message_loader' => 0
                );
            } catch (Exception $e) {
                $html = array('message' => $e->getMessage());
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }
}