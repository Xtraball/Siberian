<?php

class Comment_ApplicationController extends Application_Controller_Default {

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
        "updatepost" => array(
            "tags" => array(
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
            ),
        ),
        "delete" => array(
            "tags" => array(
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
            ),
        ),
        "hide" => array(
            "tags" => array(
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
            ),
        ),
        "show" => array(
            "tags" => array(
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
            ),
        ),
        "saveradius" => array(
            "tags" => array(
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
            ),
        ),
    );

    public function editpostAction() {
        $html = '';
        if ($data = $this->getRequest()->getPost()) {
            try {
                if (!empty($data['text'])) {

                    $comment = new Comment_Model_Comment();
                    $image = '';
                    if (empty($data['image'])) {
                        $data['image'] = null;
                    } else if (file_exists(Core_Model_Directory::getTmpDirectory(true) . "/" . $data['image'])) {
                        $img_src = Core_Model_Directory::getTmpDirectory(true) . "/" . $data['image'];
                        $info = pathinfo($img_src);
                        $filename = $info['basename'];
                        $relativePath = $this->getCurrentOptionValue()->getImagePathTo();
                        $img_dst = Application_Model_Application::getBaseImagePath() . $relativePath;
                        if (!is_dir($img_dst))
                            mkdir($img_dst, 0777, true);
                        $img_dst .= '/' . $filename;
                        rename($img_src, $img_dst);
                        if (!file_exists($img_dst))
                            throw new Exception(__('An error occurred while saving your picture. Please try againg later.'));
                        $data['image'] = $relativePath . '/' . $filename;
                        $image = Application_Model_Application::getImagePath() . '/' . $data['image'];
                    }

                    $comment->setData($data)
                            ->save()
                    ;

                    $html = array(
                        'success' => '1',
                        'success_message' => __('Information successfully saved'),
                        'image' => $image,
                        'message_timeout' => 2,
                        'message_button' => 0,
                        'message_loader' => 0
                    );
                }
            } catch (Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }

            $this->_sendJson($html);
        }
    }

    public function updatepostAction() {
        $html = '';
        if ($data = $this->getRequest()->getPost()) {
            try {
                if (!empty($data['text']) && !empty($data['id'])) {

                    $comment = new Comment_Model_Comment();
                    $comment = $comment->find($data['id']);
                    unset($data['id']);
                    $image = '';
                    if (empty($data['image'])) {
                        $data['image'] = null;
                    } else if (file_exists(Core_Model_Directory::getTmpDirectory(true) . "/" . $data['image'])) {
                        $img_src = Core_Model_Directory::getTmpDirectory(true) . "/" . $data['image'];
                        $info = pathinfo($img_src);
                        $filename = $info['basename'];
                        $relativePath = $this->getCurrentOptionValue()->getImagePathTo();
                        $img_dst = Application_Model_Application::getBaseImagePath() . $relativePath;
                        if (!is_dir($img_dst))
                            mkdir($img_dst, 0777, true);
                        $img_dst .= '/' . $filename;
                        rename($img_src, $img_dst);
                        if (!file_exists($img_dst))
                            throw new Exception(__('An error occurred while saving your picture. Please try againg later.'));
                        $data['image'] = $relativePath . '/' . $filename;
                        $image = Application_Model_Application::getImagePath() . '/' . $data['image'];
                    }

                    $comment->setData($data)
                            ->save()
                    ;

                    $url = array('comment/admin/edit');

                    $html = array(
                        'success' => '1',
                        'success_message' => __('Information successfully saved'),
                        'image' => $image,
                        'message_timeout' => 2,
                        'message_button' => 0,
                        'message_loader' => 0
                    );
                }
            } catch (Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function deleteAction() {
        $html = '';
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $comment = new Comment_Model_Comment();
                $comment->find($id)->delete();
                $html = array(
                    'success' => '1',
                    'success_message' => __('Information successfully deleted'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );
            } catch (Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }
            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function hideAction() {
        $html = '';
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $comment = new Comment_Model_Comment();
                $comment->find($id)->setisVisible(0)->save();
                $html = array(
                    'success' => '1',
                    'success_message' => __('Information successfully hidden'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );
            } catch (Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }
            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function showAction() {
        $html = '';
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $comment = new Comment_Model_Comment();
                $comment->find($id)->setisVisible(1)->save();
                $html = array(
                    'success' => '1',
                    'success_message' => __('Information successfully shown'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );
            } catch (Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }
            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function validatecropAction() {
        if ($data = $this->getRequest()->getPost()) {
            try {
                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->savecrop($data);
                $data = array(
                    'success' => 1,
                    'file' => $file,
                    'message_success' => __("Image successfully saved"),
                    'message_button' => 0,
                    'message_timeout' => 2,
                );
            } catch (Exception $e) {
                $data = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }
            $this->getLayout()->setHtml(Zend_Json::encode($data));
        }
    }

    public function saveradiusAction() {
        $html = '';
        if ($data = $this->getRequest()->getPost()) {
            try {
                if (empty($data['radius']))
                    throw new Exception(__('Radius must be provided.'));

                if (!is_numeric($data['radius']))
                    throw new Exception(__('Radius must be a valid numeric value.'));

                // Test s'il y a un value_id
                if (empty($data['value_id']))
                    throw new Exception(__('An error occurred while saving. Please try again later.'));

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($data['value_id']);

                // Test s'il y a embrouille entre la value_id en cours de modification et l'application en session
                if (!$option_value->getId() OR $option_value->getAppId() != $this->getApplication()->getId()) {
                    throw new Exception(__('An error occurred while saving. Please try again later.'));
                }

                $radius = new Comment_Model_Radius();
                $radius->find($data['value_id'], 'value_id');

                if (!$radius->getId()) {
                    $radius->setValueId($data['value_id']);
                }

                $radius->addData($data)
                        ->save()
                ;

                $html = array(
                    'success' => '1',
                    'success_message' => __('Information successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );
            } catch (Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

}
