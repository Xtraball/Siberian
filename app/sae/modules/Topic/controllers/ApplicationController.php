<?php

class Topic_ApplicationController extends Application_Controller_Default
{

    public function saveAction() {
        try {
            if($data = $this->getRequest()->getPost()) {

                if(empty($data['name'])) throw new Exception($this->_('Please, fill out all fields'));

                $topic = $this->getCurrentOptionValue()->getObject();
                if(!$topic->getId()) {
                    throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                }

                $topic->setName($datas['name'])
                    ->setDescription($datas['description'])
                    ->save()
                ;

                $html = array(
                    'success' => '1',
                    'create_store' => $mcommerce->getStores()->count() == 0,
                    'success_message' => $this->_('Info successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

            }
            else {
                throw new Exception($this->_('An error occurred while saving. Please try again later.'));
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

    public function editcategoryAction() {

        $category = new Topic_Model_Category();
        if($data = $this->getRequest()->getPost()) {
            if(!empty($data["category_id"])) {
                $category->find($data["category_id"]);
            }
        }

        $html = $this->getLayout()->addPartial('category_form', 'admin_view_default', 'topic/category/edit.phtml')
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

    public function editpostcategoryAction() {
        if($data = $this->getRequest()->getPost()) {

            try {
                $isNew = false;
                if(!$data["category_id"]) {
                    $isNew = true;
                    $topic = new Topic_Model_Topic();
                    $topic->find(array("value_id" => $this->getCurrentOptionValue()->getValueId()));

                    if (!$topic->getId()) {
                        throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                    }

                    $category = new Topic_Model_Category();
                    $position = $category->getMaxPosition($topic->getId());

                    $data["position"] = $position?$position+1:1;
                    $data["topic_id"] = $topic->getId();
                } else {
                    $category = new Topic_Model_Category($data["category_id"]);
                }

                if(!empty($data["file"])) {
                    $picture = $data["file"];
                    $relative_path = '/features/topic/';
                    $folder = Application_Model_Application::getBaseImagePath() . $relative_path;
                    $path = Application_Model_Application::getBaseImagePath() . $relative_path;
                    $file = Core_Model_Directory::getTmpDirectory(true) . '/' . $picture;

                    if (file_exists($file)) {
                        if (!is_dir($path)) mkdir($path, 0777, true);
                        if (!copy($file, $folder . $picture)) {
                            throw new exception($this->_('An error occurred while saving. Please try again later.'));
                        } else {
                            $data['picture'] = $relative_path . $picture;
                        }
                    }
                }

                if($data["remove_picture"]) {
                    $data['picture'] = null;
                }

                $category->addData($data)
                    ->save()
                ;

                $html = array(
                    'is_new' => (int) $isNew,
                    'category_id' => $category->getId(),
                    'category_label' => $category->getName(),
                    'success' => '1',
                    'success_message' => $this->_('Category successfully saved.'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

                if($isNew) {
                    $html['row_html'] = $this->getLayout()->addPartial('child_'.$category->getId(), 'admin_view_default', 'topic/application/edit/list.phtml')
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

    public function editdescriptionAction() {
        if($data = $this->getRequest()->getPost()) {
            try {
                $topic = new Topic_Model_Topic();
                $topic->find(array("value_id" => $this->getCurrentOptionValue()->getValueId()));

                if (!$topic->getId()) {
                    throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                }

                $topic->setData($data)->save();

                $html = array(
                    'success' => '1',
                    'success_message' => $this->_('Description successfully saved.'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

            } catch(Exception $e) {
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
//                Zend_Debug::dump($positions);
//                die(__METHOD__ . " L:" . __LINE__);
                if(empty($positions)) throw new Exception($this->_('An error occurred while saving. Please try again later.'));

                $position = 0;
                foreach($positions as $index => $parent_category) {
                    if($parent_category == "null") {
                        $parent_category = null;
                    }
                    $category = new Topic_Model_Category();
                    $category->find($index, 'category_id');
                    $category
                        ->setParentId($parent_category)
                        ->setPosition($position)
                        ->save();
                    $position++;
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

        if($data = $this->getRequest()->getParams()) {

            try {
                if(empty($data['category_id'])) {
                    throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                }

                $category = new Topic_Model_Category();
                $category->find($data['category_id']);

                if(!$category->getId()) {
                    throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                }

                $category->delete();

                $html = array(
                    'success' => 1
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

    /**
     * @param $option
     * @return string
     * @throws Exception
     */
    public function exportAction() {
        if($this->getCurrentOptionValue()) {
            $topic = new Topic_Model_Topic();
            $result = $topic->exportAction($this->getCurrentOptionValue());

            $this->_download($result, "topic-".date("Y-m-d_h-i-s").".yml", "text/x-yaml");
        }
    }

}