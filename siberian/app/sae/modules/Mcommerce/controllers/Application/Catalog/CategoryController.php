<?php

/**
 * Class Mcommerce_Application_Catalog_CategoryController
 */
class Mcommerce_Application_Catalog_CategoryController extends Application_Controller_Default_Ajax
{
    /**
     * @throws Exception
     */
    public function editAction()
    {
        $category = new Folder_Model_Category ();
        if ($id = $this->getRequest()->getParam('category_id')) {
            $category->find($id);
            if ($category->getId() AND $this->getCurrentOptionValue()->getObject()->getRootCategoryId() != $category->getRootCategoryId()) {
                throw new Exception(__('An error occurred during the process. Please try again later.'));
            }
        }

        $html = $this->getLayout()
            ->addPartial('category_form', 'admin_view_default', 'mcommerce/application/edit/catalog/categories/list/edit.phtml')
            ->setOptionValue($this->getCurrentOptionValue())
            ->setParentId($this->getRequest()->getPost('parent_id'))
            ->setCurrentCategory($category)
            ->toHtml();

        $payload = [
            'form_html' => $html,
            'category_id' => $category->getId()
        ];

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function editpostAction()
    {
        if ($datas = $this->getRequest()->getParams()) {
            try {
                $isNew = false;
                $option = $this->getCurrentOptionValue();
                $category = new Folder_Model_Category();
                if (!empty($datas['category_id'])) {
                    $category->find($datas['category_id']);
                    if ($category->getId() AND $option->getObject()->getRootCategoryId() != $category->getRootCategoryId()) {
                        throw new Exception(__('An error occurred while saving. Please try again later.'));
                    }
                }
                if (!$category->getId() AND empty($datas['parent_id'])) {
                    throw new Exception(__('An error occurred while saving. Please try again later.'));
                }

                if (!$category->getId()) {
                    $isNew = true;
                }

                if (!empty($datas['file'])) {
                    $application = $this->getApplication();
                    $formated_name = Core_Model_Lib_String::format($application->getName(), true);
                    $relative_path = '/' . $application->getId() . '-' . $formated_name . '/application/mcommerce/category/';
                    $folder = Application_Model_Application::getBaseImagePath() . $relative_path;
                    $path = Application_Model_Application::getBaseImagePath() . $relative_path;
                    $file = Core_Model_Directory::getTmpDirectory(true).'/'.$datas['file'];
                    if (!is_dir($path)) {
                        mkdir($path, 0777, true);
                    }
                    if (!copy($file, $folder.$datas['file'])) {
                        throw new exception(__('An error occurred while saving. Please try again later.'));
                    } else {
                        $datas['picture'] = $relative_path.$datas['file'];
                    }
                } else if(!empty($datas['remove_picture'])) {
                    $datas['picture'] = null;
                }

                $category->addData($datas)
                    ->save()
                ;

                $html = [
                    'is_new' => (int) $isNew,
                    'is_deleted' => (int) $category->getIsDeleted(),
                    'category_id' => $category->getId(),
                    'category_label' => $category->getTitle(),
                    'success' => '1',
                    'success_message' => __('Category successfully saved.'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                ];

                if($isNew) {
                    $html['row_html'] = $this->getLayout()->addPartial('child_'.$category->getId(), 'admin_view_default', 'mcommerce/application/edit/catalog/categories/list.phtml')
                        ->setCategory($category)
                        ->toHtml()
                    ;
                }

            }
            catch (Exception $e) {
                $html = [
                    'error' => 1,
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                ];
            }

            $this->_sendHtml($html);

        }

    }

    /**
     * Update categories for M-Commerce.
     *
     * the old version, named 'get' was using GET query parameters to update the categories,
     * it was resulting in too long uris.
     */
    public function orderAction() {
        $request = $this->getRequest();

        try {
            $version = $request->getParam('version', 'get');
            $categories = $request->getParam('categories', false);

            if (!is_array($categories)) {
                throw new Siberian_Exception(__('Missing categories.'));
            }

            switch ($version) {
                case 'get': // Legacy old/get method!
                        throw new Siberian_Exception(__('This method is deprecated, please update!'));
                    break;
                case 'post':
                        $position = 0;
                        foreach ($categories as $category) {
                            $tmpCategory = (new Folder_Model_Category())
                                ->find($category['id'], 'category_id');

                            $parentId = is_numeric($category['parentId']) ? $category['parentId'] : null;

                            $tmpCategory
                                ->setParentId($parentId)
                                ->setPos($position)
                                ->save();

                            $position++;
                        }
                    break;
            }

            $payload = [
                'success' => true
            ];

        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    public function deleteAction() {

        if($datas = $this->getRequest()->getParams()) {

            try {
                if(empty($datas['category_id'])) {
                    throw new Exception(__('An error occurred while saving. Please try again later.'));
                }

                $category = new Folder_Model_Category();
                $category->find($datas['category_id']);

                if(!$category->getId() OR $category->getRootCategoryId() != $this->getCurrentOptionValue()->getObject()->getRootCategoryId()) {
                    throw new Exception(__('An error occurred while saving. Please try again later.'));
                }

                $category->delete();

                $html = [
                    'success' => 1,
                    'category_id' => $datas['category_id']
                ];
            }
            catch(Exception $e) {
                $html = [
                    'error' => 1,
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                ];
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
                $html = [];
                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->savecrop($datas);
                $html = [
                    'success' => 1,
                    'file' => $file,
                    'message_success' => 'Enregistrement réussi',
                    'message_button' => 0,
                    'message_timeout' => 2,
                ];
            } catch (Exception $e) {
                $html = [
                    'error' => 1,
                    'message' => $e->getMessage()
                ];
            }

            $this->_sendHtml($html);

         }

    }

}