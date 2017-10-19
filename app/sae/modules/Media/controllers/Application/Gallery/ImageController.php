<?php

class Media_Application_Gallery_ImageController extends Application_Controller_Default {

    /**
     * @var array
     */
    public $cache_triggers =[
        'editpost' => [
            'tags' => [
                'homepage_app_#APP_ID#',
            ],
        ],
        'editpostv2' => [
            'tags' => [
                'homepage_app_#APP_ID#',
            ],
        ],
        'delete' => [
            'tags' => [
                'homepage_app_#APP_ID#',
            ],
        ]
    ];

    public function editpostv2Action() {
        try {
            $request = $this->getRequest();

            if ($formDatas = $request->getPost()) {
                $currentOption = $this->getCurrentOptionValue();
                if (!$currentOption->getId()) {
                    throw new Siberian_Exception(__('Missing value_id!'));
                }

                $valueId = $currentOption->getId();
                $galleryId = isset($formDatas['id']) ? $formDatas['id'] : null;
                unset($formDatas['id']);
                $app = $this->getApplication();
                $willSave = true;

                $imageGallery = (new Media_Model_Gallery_Image())
                    ->find([
                        'value_id' => $valueId,
                        'gallery_id' => $galleryId,
                    ]);

                // Gallery type!
                if (key_exists('param_instagram', $formDatas) && !empty($formDatas['param_instagram'])) {
                    if (!$app->getInstagramClientId() || !$app->getInstagramToken()) {
                        throw new Siberian_Exception(__("Instagram API settings can't be found."));
                    }

                    $userId = (new Media_Model_Gallery_Image_Instagram())->getUserId();
                    if (!$userId) {
                        throw new Siberian_Exception(__('The entered name is not a valid Instagram user.'));
                    }
                    $formDatas['type_id'] = 'instagram';

                } else if (key_exists('param_flickr', $formDatas) && !empty($formDatas['param_flickr'])) {
                    if(!$app->getFlickrKey() || !$app->getFlickrSecret()) {
                        throw new Siberian_Exception(__("Flickr API settings can't be found."));
                    }

                    $formDatas['type_id'] = 'flickr';
                    $formDatas['type'] = (new Media_Model_Gallery_Image_Flickr())
                        ->guessType($formDatas['param_flickr']);
                    $formDatas['identifier'] = $formDatas['param_flickr'];

                } else if (key_exists('param_facebook', $formDatas) && !empty($formDatas['param_facebook'])) {
                    try {
                        (new Social_Model_Facebook())->getAccessToken();
                    } catch (Exception $e) {
                        throw new Siberian_Exception(__('No valid Facebook API key and secret.'));
                    }

                    $formDatas['type_id'] = 'facebook';

                } else if (key_exists('param', $formDatas) && !empty($formDatas['param'])) {
                    $formDatas['type_id'] = 'picasa';
                } else {
                    $formDatas['type_id'] = 'custom';
                }

                $payload = [
                    'success' => true,
                    'is_new' => !(boolean) $imageGallery->getId()
                ];

                // Don't really understand this section! (About PICASA...)
                if (isset($formDatas['type_id']) && ($formDatas['type_id'] === 'picasa')) {
                    $imageGallery->setTypeId('picasa');
                    $imageGallery->getTypeInstance()->setParam($formDatas['param']);

                    if (empty($formDatas['album_id'])) {
                        $albums = $imageGallery->getTypeInstance()->findAlbums();
                    } else {
                        $payload['albumId'] = $formDatas['album_id'];
                    }

                    $payload['albums'] = [];
                    if (!empty($albums)) {
                        $payload['albums'] = $albums;
                    }

                    $formDatas['type'] = !empty($formDatas['album_id']) || !empty($albums) ?
                        'album' : 'search';
                }

                // OK We will save the informations!
                if ($willSave) {
                    // For newly created galleries
                    $formGalleryId = $imageGallery->getId() ? $galleryId : 'new';

                    $imageGallery
                        ->setData($formDatas)
                        ->setId($galleryId);

                    // Save new name!
                    if (isset($formDatas['name_' . $galleryId])) {
                        $imageGallery
                            ->setName($formDatas['name_' . $galleryId]);
                    }

                    $imageGallery->save();

                    // Facebook case!
                    if ($formDatas['type_id'] === 'facebook') {
                        $facebookGallery = new Media_Model_Gallery_Image_Facebook(
                            [
                                'gallery_id' => $imageGallery->getGalleryId(),
                                'album_id' => $formDatas['param_facebook']
                            ]
                        );
                        if ($formDatas['image_id']){
                            $facebookGallery->setImageId($formDatas['image_id']);
                        }
                        $facebookGallery->save();
                    }

                    if (isset($formDatas['images']['list_' . $formGalleryId])) {
                        foreach ($formDatas['images']['list_' . $formGalleryId] as $key => $info) {

                            $singleImage = (new Media_Model_Gallery_Image_Custom())
                                ->find($key, 'image_id');

                            if (!empty($info['delete'])) {
                                $singleImage->delete();
                                continue;
                            }

                            if (!$singleImage->getId()) {
                                $singleImage->setGalleryId($imageGallery->getGalleryId());
                            }

                            if (!empty($info['path'])) {

                                $filename = $info['path'];
                                $imgSrc = Core_Model_Directory::getTmpDirectory(true).'/'.$filename;
                                if (file_exists($imgSrc)) {

                                    $relativePath = $currentOption->getImagePathTo();
                                    $folder = Application_Model_Application::getBaseImagePath() . $relativePath;
                                    $imgDst = $folder . '/' . $filename;

                                    if (!is_dir($folder)) {
                                        mkdir($folder, 0777, true);
                                    }

                                    if (!rename($imgSrc, $imgDst)) {
                                        throw new Siberian_Exception(
                                            __('An error occurred while saving your images gallery. Please try again later.'));
                                    }

                                    $singleImage->setUrl($relativePath . '/' . $filename);
                                }
                            }

                            $singleImage
                                ->setTitle(!empty($info['title']) ? $info['title'] : null)
                                ->setDescription(!empty($info['description']) ? $info['description'] : null)
                                ->save();
                        }
                    }

                    $payload = array_merge($payload, [
                        'id' => (int) $imageGallery->getGalleryId(),
                        'is_new' => !(boolean) $imageGallery->getId(),
                        'message' => __('Images gallery has been saved successfully'),
                        'success_message' => __('Images gallery has been saved successfully'),
                        'message_timeout' => 2,
                        'message_button' => 0,
                        'message_loader' => 0,
                    ]);

                }

            } else {
                throw new Siberian_Exception(__('Missing form data!'));
            }

        } catch(Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function deleteAction() {

        try {
            $request = $this->getRequest();
            if ($formDatas = $request->getPost()) {

                // Test s'il y a un value_id
                if (empty($formDatas['value_id']) || empty($formDatas['id'])) {
                    throw new Siberian_Exception(
                        __('An error occurred while deleting you images gallery. Please try again later.'));
                }

                $db = Zend_Db_Table::getDefaultAdapter();
                $db->delete(
                    'media_gallery_image',
                    'gallery_id = ' . intval($formDatas['id'])
                );

                $payload = [
                    'success' => true,
                    'message' => __('Gallery has been deleted!')
                ];

            } else {
                throw new Siberian_Exception(__('Missing form data!'));
            }

        } catch(Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * What ?
     */
    public function listAction() {
        $this->getLayout()->setBaseRender(
            'content',
            'media/application/gallery/image/list.phtml',
            'admin_view_default');
    }

    public function checkinstagramAction() {

        $datas = $this->_request->getParams();

        $instagram = new Media_Model_Gallery_Image_Instagram();
        $userId = $instagram->getUserId($datas['param_instagram']);

        if ($userId) {
            $html['success'] = 1;
        } else {
            $html = array(
                'message' => __("The entered name is not a valid Instagram user."),
                'message_button' => 1,
                'message_loader' => 1
            );
        }

        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    public function albumsAction() {
        $html = array();
        try {
            $pageid = $this->getRequest()->getParam('pageid');
            if (!$pageid) {
                throw new Exception("Please specify the page ID");
            }
            $facebook = new Social_Model_Facebook();
            // Verify the page exists
            $facebook->getPage($pageid);
            // Return the albums
            $albums = $facebook->getAlbums($pageid);

            if(!$albums) {
                $html = array(
                    'message' => __("An error occurred while retrieving your page. Please check your page id."),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            } else {
                $html = $albums;
            }
        } catch (Exception $e) {
            $html = array(
                'message' => __($e->getMessage()),
                'message_button' => 1,
                'message_loader' => 1
            );
        }
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }






    /**
     * @deprecated
     */
    public function editpostAction() {

        if($datas = $this->getRequest()->getPost()) {

            $html = '';

            try {

                // Test s'il y a un value_id
                if(empty($datas['value_id'])) {
                    throw new Siberian_Exception(
                        __('An error occurred while saving your images gallery. Please try again later.'));
                }

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
                    if($this->getApplication()->getInstagramClientId() AND
                        $this->getApplication()->getInstagramToken()) {
                        $instagram = new Media_Model_Gallery_Image_Instagram();
                        $userId = $instagram->getUserId();
                        if (!$userId) {
                            throw new Exception(__("The entered name is not a valid Instagram user."));
                        }
                        $datas['type_id'] = 'instagram';
                    } else {
                        throw new Siberian_Exception(__("Instagram API settings can't be found."));
                    }
                } elseif (!empty($datas['param_flickr'])) {
                    if($this->getApplication()->getFlickrKey() AND $this->getApplication()->getFlickrSecret()) {
                        $flickr = new Media_Model_Gallery_Image_Flickr();
                        $datas['type_id'] = 'flickr';
                        $datas['type'] = $flickr->guessType($datas['param_flickr']);
                        $datas['identifier'] = $datas['param_flickr'];
                    } else {
                        throw new Exception(__("Flickr API settings can't be found."));
                    }
                } elseif (!empty($datas['param_facebook'])) {
                    $facebook = new Social_Model_Facebook();
                    $facebook_is_available = $facebook->getAccessToken() != null;
                    if(!$facebook_is_available){
                        throw new Exception(__("No valid Facebook API key and secret."));
                    }
                    $datas['type_id'] = 'facebook';
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
                    $image
                        ->setData($datas)
                        ->save();

                    // save facebook parameters if necessary
                    if ($datas['param_facebook']) {
                        $facebook_gallery = new Media_Model_Gallery_Image_Facebook(
                            array(
                                'gallery_id' => $image->getGalleryId(),
                                'album_id' => $datas['param_facebook']
                            )
                        );
                        if($datas['image_id']){
                            $facebook_gallery->setImageId($datas['image_id']);
                        }
                        $facebook_gallery->save();
                    }

                    $html['id'] = (int) $image->getId();
                    $html['is_new'] = (int) $isNew;
                    $html['success_message'] = __("Images gallery has been saved successfully");
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

                                    if (!rename($img_src, $img_dst)) {
                                        throw new Siberian_Exception(
                                            __('An error occurred while saving your images gallery. Please try again later.'));
                                    }

                                    $gallery->setUrl($relative_path . '/' . $filename);
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

}
