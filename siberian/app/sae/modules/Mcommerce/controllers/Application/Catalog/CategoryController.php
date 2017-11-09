<?php

class Mcommerce_Application_Catalog_CategoryController extends Application_Controller_Default_Ajax {

    public function editAction() {

        $category = new Folder_Model_Category ();
        if($id = $this->getRequest()->getParam('category_id')) {
            $category->find($id);
            if($category->getId() AND $this->getCurrentOptionValue()->getObject()->getRootCategoryId() != $category->getRootCategoryId()) {
                throw new Exception($this->_('An error occurred during the process. Please try again later.'));
            }
        }

        $mcommerce = $this->getApplication()->getPage('m_commerce')->getObject();

        $html = $this->getLayout()->addPartial('category_form', 'admin_view_default', 'mcommerce/application/edit/catalog/categories/list/edit.phtml')
//            ->setCurrentMcommerce($mcommerce)
            ->setOptionValue($this->getCurrentOptionValue())
            ->setParentId($this->getRequest()->getPost('parent_id'))
            ->setCurrentCategory($category)
            ->toHtml()
        ;

        $html = array(
            'form_html' => $html,
            'category_id' => $category->getId()
        );

        $this->_sendHtml($html);
    }

    public function editpostAction() {


        if($datas = $this->getRequest()->getParams()) {

            try {

                $isNew = false;
                $option = $this->getCurrentOptionValue();
                $category = new Folder_Model_Category();
                if(!empty($datas['category_id'])) {
                    $category->find($datas['category_id']);
                    if($category->getId() AND $option->getObject()->getRootCategoryId() != $category->getRootCategoryId()) {
                        throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                    }
                }
                if(!$category->getId() AND empty($datas['parent_id'])) {
                    throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                }

                if(!$category->getId()) {
                    $isNew = true;
                }

                if(!empty($datas['file'])) {
                    $application = $this->getApplication();
                    $formated_name = Core_Model_Lib_String::format($application->getName(), true);
                    $relative_path = '/' . $application->getId() . '-' . $formated_name . '/application/mcommerce/category/';
                    $folder = Application_Model_Application::getBaseImagePath() . $relative_path;
                    $path = Application_Model_Application::getBaseImagePath() . $relative_path;
                    $file = Core_Model_Directory::getTmpDirectory(true).'/'.$datas['file'];
                    if(!is_dir($path)) mkdir($path, 0777, true);
                    if(!copy($file, $folder.$datas['file'])) {
                        throw new exception($this->_('An error occurred while saving. Please try again later.'));
                    } else {
                        $datas['picture'] = $relative_path.$datas['file'];
                    }
                } else if(!empty($datas['remove_picture'])) {
                    $datas['picture'] = null;
                }

                $category->addData($datas)
                    ->save()
                ;

                $html = array(
                    'is_new' => (int) $isNew,
                    'is_deleted' => (int) $category->getIsDeleted(),
                    'category_id' => $category->getId(),
                    'category_label' => $category->getTitle(),
                    'success' => '1',
                    'success_message' => $this->_('Category successfully saved.'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

                if($isNew) {
                    $html['row_html'] = $this->getLayout()->addPartial('child_'.$category->getId(), 'admin_view_default', 'mcommerce/application/edit/catalog/categories/list.phtml')
                        ->setCategory($category)
                        ->toHtml()
                    ;
                }

            }
            catch(Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->_sendHtml($html);

        }

    }

    public function orderAction() {

        if($datas = $this->getRequest()->getParams()) {

            try {

                // Récupère les positions
                $positions = $this->getRequest()->getParam('category');
                // Supprime la root cat en conservant les index
                reset($positions);
                $key = key($positions);
                unset($positions[$key]);

                if(empty($positions)) throw new Exception($this->_('An error occurred while saving. Please try again later.'));

                $position = 0;
                foreach($positions as $index => $parent_category) {
                    $category = new Folder_Model_Category();
                    $category->find($index, 'category_id');
                    $category
                        ->setParentId($parent_category)
                        ->setPos($position)
                        ->save();
                    $position+=1;
                }

                // Renvoie OK
                $html = array('success' => 1);

            }
            catch(Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->_sendHtml($html);

        }

    }

    public function deleteAction() {

        if($datas = $this->getRequest()->getParams()) {

            try {
                if(empty($datas['category_id'])) {
                    throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                }

                $category = new Folder_Model_Category();
                $category->find($datas['category_id']);

                if(!$category->getId() OR $category->getRootCategoryId() != $this->getCurrentOptionValue()->getObject()->getRootCategoryId()) {
                    throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                }

                $category->delete();

                $html = array(
                    'success' => 1,
                    'category_id' => $datas['category_id']
                );
            }
            catch(Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->_sendHtml($html);

        }

    }

    /**
     * @todo have to move in a front controller
     */
    public function cropAction() {

        if($datas = $this->getRequest()->getPost()) {
            try {
                $html = array();
                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->savecrop($datas);
                $html = array(
                    'success' => 1,
                    'file' => $file,
                    'message_success' => 'Enregistrement réussi',
                    'message_button' => 0,
                    'message_timeout' => 2,
                );
            } catch (Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }

            $this->_sendHtml($html);

         }

    }

}