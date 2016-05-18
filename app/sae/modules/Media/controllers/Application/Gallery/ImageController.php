<?php

class Media_Application_Gallery_ImageController extends Application_Controller_Default {

    public function editpostAction() {

        if($datas = $this->getRequest()->getPost()) {

            $html = '';

            try {

                // Test s'il y a un value_id
                if(empty($datas['value_id'])) throw new Exception($this->_("An error occurred while saving your images gallery. Please try again later."));

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);

                $isNew = true;
                $save = true;
                $image = new Media_Model_Gallery_Image();

                if(!empty($datas['id'])) {
                    $image->find($datas['id']);
                    $isNew = false;
                    $id = $datas['id'];
                }
                else {
                    $id = 'new';
                    $datas['value_id'] = $option_value->getId();
                }

                if($image->getId() AND $image->getValueId() != $option_value->getId()) {
                    throw new Exception("An error occurred while saving your images gallery. Please try again later.");
                }

                if (!empty($datas['param_instagram'])) {
                    $instagram = new Media_Model_Gallery_Image_Instagram();
                    $userId = $instagram->getUserId($datas['param_instagram']);
                    if(!$userId) throw new Exception($this->_("The entered name is not a valid Instagram user."));
                    $datas['type_id'] = 'instagram';
                } elseif (!empty($datas['param'])) {
                    $datas['type_id'] = 'picasa';
                } else {
                    $datas['type_id'] = 'custom';
                }

                $html = array(
                    'success' => 1,
                    'is_new' => (int) $isNew
                );

                if(empty($datas['type_id']) OR $datas['type_id'] == 'picasa') {
                    $image->setTypeId('picasa');
                    $image->getTypeInstance()->setParam($datas['param']);

                    if(empty($datas['album_id'])) {
                        $albums = $image->getTypeInstance()->findAlbums();
                    }
                    if(!empty($albums)) {
                        $html['albums'] = $albums;
                        $save = false;
                    }

                    $datas['type'] = !empty($datas['album_id']) || !empty($albums) ? 'album' : 'search';
                }

                if($save) {
                    $image->setData($datas)->save();
                    $html['id'] = (int) $image->getId();
                    $html['is_new'] = (int) $isNew;
                    $html['success_message'] = $this->_("Images gallery has been saved successfully");
                    $html['message_timeout'] = 2;
                    $html['message_button'] = 0;
                    $html['message_loader'] = 0;

                    if(isset($datas['images']['list_'.$id])) {
                        foreach($datas['images']['list_'.$id] as $key => $info) {

                            $gallery = new Media_Model_Gallery_Image_Custom();
                            if(!empty($info['image_id'])) {
                                $gallery->find($info['image_id']);
                            }

                            if(!empty($info['delete'])) {
                                $gallery->delete();
                                continue;
                            }

                            if(!$gallery->getId()) {
                                $gallery->setGalleryId($image->getId());
                            }

                            if(!empty($info['path'])) {

                                $filename = $info['path'];
                                $img_src = Core_Model_Directory::getTmpDirectory(true).'/'.$filename;
                                if(file_exists($img_src)) {

                                    $relative_path = $option_value->getImagePathTo();
                                    $folder = Application_Model_Application::getBaseImagePath().$relative_path;
                                    $img_dst = $folder.'/'.$filename;

                                    if (!is_dir($folder)) {
                                        mkdir($folder, 0777, true);
                                    }

                                    if(!rename($img_src, $img_dst)) {
                                        throw new Exception("An error occurred while saving your images gallery. Please try again later.");
                                    }

                                    $gallery->setUrl($relative_path.'/'.$filename);

                                }
                            }
                            $gallery->setTitle(!empty($info['title']) ? $info['title'] : null)
                                ->setDescription(!empty($info['description']) ? $info['description'] : null)
                                ->save()
                            ;
                        }

                    }
                }

            }
            catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function deleteAction() {

        if ($datas = $this->getRequest()->getPost()) {

            $html = '';

            try {

                // Test s'il y a un value_id
                if (empty($datas['value_id']) OR empty($datas['id']))
                    throw new Exception($this->_("An error occurred while deleting you images gallery. Please try again later."));

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);

                $image = new Media_Model_Gallery_Image();
                $image->find($datas['id']);
                $image->delete();

                $html = array('success' => 1);
            } catch (Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function listAction() {
        $this->getLayout()->setBaseRender('content', 'media/application/gallery/image/list.phtml', 'admin_view_default');
    }

    public function checkinstagramAction() {

        $datas = $this->_request->getParams();

        $instagram = new Media_Model_Gallery_Image_Instagram();
        $userId = $instagram->getUserId($datas['param_instagram']);

        if ($userId) {
            $html['success'] = 1;
        } else {
            $html = array(
                'message' => $this->_("The entered name is not a valid Instagram user."),
                'message_button' => 1,
                'message_loader' => 1
            );
        }

        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

}
