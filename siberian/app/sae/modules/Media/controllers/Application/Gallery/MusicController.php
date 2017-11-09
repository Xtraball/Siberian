<?php

class Media_Application_Gallery_MusicController extends Application_Controller_Default {

    public function formAction() {
        $type = $this->getRequest()->getParam('type');
        $gallery_id = $this->getRequest()->getParam('gallery_id');
        $album_id = null;
        if($this->getRequest()->getParam('album_id')) {
            $album_id = $this->getRequest()->getParam('album_id');
        }
        $html = $this->getLayout()
                ->addPartial('event_custom', 'admin_view_default', 'media/application/gallery/music/edit/'.$type.'/form.phtml')
                ->setOptionValue($this->getCurrentOptionValue())
                ->setGalleryId($gallery_id)
                ->setAlbumId($album_id)
                ->toHtml();

        $this->getLayout()->setHtml($html);
    }

    public function listAction() {
        $this->getLayout()->setBaseRender('content', 'media/application/gallery/music/list.phtml', 'admin_view_default');
    }

    public function editpostAction() {

        if($datas = $this->getRequest()->getPost()) {

            $html = '';

            try {

                // Test s'il y a un value_id
                if(empty($datas['value_id'])) throw new Exception($this->_("An error occurred while saving your playlist. Thanks for trying later."));

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);

                if(is_array($datas['gallery_id'])) {
                    $datas['gallery_id'] = $datas['gallery_id'][0];
                }

                if(isset($datas['artwork_url']) && !empty($datas['artwork_url'])) {
                    $base_img_src = Core_Model_Directory::getTmpDirectory(true).'/';
                    if(file_exists($base_img_src.$datas['artwork_url'])) {
                        $base_img_src = $base_img_src.$datas['artwork_url'];
                        $relativePath = $option_value->getImagePathTo();
                        $img_dst = Application_Model_Application::getBaseImagePath().$relativePath;
                        if(!is_dir($img_dst)) mkdir($img_dst, 0777, true);
                        $img_dst .= '/'.$datas['artwork_url'];
                        @copy($base_img_src, $img_dst);
                        if(!file_exists($img_dst)) throw new Exception($this->_("An error occurred while saving your picture. Please try againg later."));
                        $datas['artwork_url'] = $relativePath.'/'.$datas['artwork_url'];
                    }
                }

                $gallery = new Media_Model_Gallery_Music();
                $gallery->find($datas['gallery_id']);
                $gallery->addData($datas);

                if(isset($datas['delete_image']) && $datas['delete_image'] == 'true') {
                    $gallery->setArtworkUrl(null);
                }

                $gallery->save();

                $content_html = $this->getLayout()
                    ->addPartial('edit_content', 'admin_view_default', 'media/application/gallery/music/edit/list.phtml')
                    ->setOptionValue($option_value)
                    ->setGalleryId($gallery->getId())
                    ->toHtml()
                ;

                $success_message = $this->_("Playlist successfully saved.");
                if($datas['gallery_id'] != '') {
                    $success_message = '';
                }

                $html = array(
                    'success' => 1,
                    'content_html' => $content_html,
                    'gallery_id' => (int) $gallery->getId(),
                    'success_message' => $success_message,
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

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

    public function sortgalleriesAction() {
        if ($galleries = $this->getRequest()->getParam('gallery_id')) {
            $html = array();
            try {

                if(!$this->getCurrentOptionValue()) {
                    throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                }

                foreach($galleries as $index => $id) {
                    $gallery = new Media_Model_Gallery_Music();
                    $gallery->find($id, 'gallery_id');
                    $gallery
                        ->setPosition($index)
                        ->save();
                }

                $html = array(
                    'success' => 1,
                    'success_message' => $this->_("Info successfully saved"),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );
            } catch (Exception $e) {
                $html = array('message' => $e->getMessage());
            }
            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function sortelementsAction() {
        if ($elements = $this->getRequest()->getParam('element_id')) {
            $html = array();
            try {

                if(!$this->getCurrentOptionValue()) {
                    throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                }

                $pos = 0;
                $elements_types = $this->getRequest()->getParam('element_type');
                foreach($elements as $index => $element_id) {
                    $type = $elements_types[$element_id];
                    $music_element = new Media_Model_Gallery_Music_Elements();
                    $success = $music_element->updatePositions($element_id, $type, $pos);
                    $pos++;
                }

                $html = array(
                    'success' => 1,
                    'success_message' => $this->_("Info successfully saved"),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );
            } catch (Exception $e) {
                $html = array('message' => $e->getMessage());
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function sorttracksAction() {
        if ($tracks = $this->getRequest()->getParam('element_id')) {
            $html = array();
            try {

                if(!$this->getCurrentOptionValue()) {
                    throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                }


                foreach($tracks as $index => $track_id) {
                    $track = new Media_Model_Gallery_Music_Track();
                    $track->find($track_id);
                    $track
                        ->setPosition($index)
                        ->save();
                }

                $html = array(
                    'success' => 1,
                    'success_message' => $this->_("Info successfully saved"),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );
            } catch (Exception $e) {
                $html = array('message' => $e->getMessage());
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function cropAction() {

        if($datas = $this->getRequest()->getPost()) {
            try {
                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->savecrop($datas);
                $datas = array(
                    'success' => 1,
                    'file' => $file,
                    'message_success' => 'Enregistrement réussi',
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

    public function deletegalleryAction() {

        if($datas = $this->getRequest()->getPost()) {

            $html = '';

            try {

                // Test s'il y a un value_id
                if(empty($datas['value_id']) OR empty($datas['gallery_id'])) throw new Exception($this->_("An error occurred while deleting your playlist. Thanks for trying later."));

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);

                $datas['gallery_id'] = $datas['gallery_id'][0];

                $gallery = new Media_Model_Gallery_Music();
                $gallery->find($datas['gallery_id'])->delete();

                $html = array('success' => 1);

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

    public function deleteelementAction() {
        if($datas = $this->getRequest()->getPost()) {

            $html = '';

            try {

                // Test s'il y a un value_id
                if(empty($datas['value_id'])) {
                    if($datas['type'] == 'album') {
                        throw new Exception($this->_("An error occurred while deleting your album. Thanks for trying later."));
                    } elseif($datas['type'] == 'track') {
                        throw new Exception($this->_("An error occurred while deleting your track. Thanks for trying later."));
                    }
                }

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);

                if($datas['type'] == 'album') {
                    $album_id = $datas['element_id'][0];
                    $album = new Media_Model_Gallery_Music_Album();
                    $album->find($album_id)->delete();
                } elseif($datas['type'] == 'track') {
                    $track_id = $datas['element_id'][0];
                    $track = new Media_Model_Gallery_Music_Track();
                    $track->find($track_id)->delete();
                }

                $html = array('success' => 1);
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

    public function editalbumAction() {
        $option_value_id = $this->getRequest()->getParam('option_value_id');
        $album_id = $this->getRequest()->getParam('album_id');
        $html = $this->getLayout()
                ->addPartial('event_custom', 'admin_view_default', 'media/application/gallery/music/edit/album.phtml')
                ->setOptionValueId($option_value_id)
                ->setAlbumId($album_id)
                ->toHtml();

        $this->getLayout()->setHtml($html);
    }

    public function searchitunesAction() {
        if($datas = $this->getRequest()->getPost()) {

            $html = '';
            try {
                $itunes_api = new Media_Model_Library_Itunes();

                if($datas["collectionId"] || $datas["artistId"]) {
                    $datas["entity"] = 'album';
                    if($datas["collectionId"]) {
                        //Recherche par album ID
                        $results = $itunes_api->lookup($datas["collectionId"], 'id', array(
                            'entity' => $datas['entity'],
                            'limit' => 200,
                            'media' => 'music',
                            'sort' => 'recent'
                        ))->results;
                    } else if($datas["artistId"]) {
                        $datas['entity'] = 'musicArtist';
                        $artist_id = $datas["artistId"];
                    }
                } else {
                    //Recherche par terme général
                    $results = $itunes_api->search($datas['search_term'], array(
                        'entity' => $datas['entity'],
                        'limit' => 200,
                        'media' => 'music',
                        'sort' => 'recent'
                    ))->results;
                }

                //Si recherche par artiste
                if($datas['entity'] == 'musicArtist') {
                    //Si un seul artiste correspondant, remonte ses albums
                    if(!isset($results) || count($results) == 1) {
                        $datas['entity'] = 'album';
                        if(!isset($artist_id)) $artist_id = $results[0]->artistId;
                        //Recherche par artist ID
                        $results = $itunes_api->lookup($artist_id, 'id', array(
                            'entity' => $datas['entity'],
                            'limit' => 200,
                            'media' => 'music',
                            'sort' => 'recent'
                        ))->results;
                    }
                }
                $html = array(
                    'success' => 1,
                    'results' => $results,
                    'entity' => $datas['entity']
                );
            } catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }
            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }

    }

    public function saveitunesAction() {
        if($datas = $this->getRequest()->getPost()) {
            $html = '';
            $html_content = '';
            try {

                // Test s'il y a un value_id
                if(empty($datas['value_id'])) throw new Exception($this->_("An error occurred while saving. Please try again later."));

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);

                $itunes_api = new Media_Model_Library_Itunes();
                if(isset($datas['albums'])) {
                    foreach($datas['albums'] as $album) {
                        $tracks_results = $itunes_api->lookup($album, 'id', array(
                            'entity' => 'song',
                            'media' => 'music'
                        ))->results;
                        //Le premier élément du tableau est l'album
                        $album = $tracks_results[0];

                        $new_album = new Media_Model_Gallery_Music_Album();
                        $new_album
                            ->setGalleryId($datas['gallery_id'])
                            ->setName($album->collectionName)
                            ->setArtworkUrl($album->artworkUrl100)
                            ->setArtistName($album->artistName)
                            ->setType('itunes')
                            ->save();

                        $music_positions = new Media_Model_Gallery_Music_Elements();
                        $new_element_position = $music_positions->getNextElementsPosition();
                        $music_positions
                                ->setGalleryId($datas['gallery_id'])
                                ->setAlbumId($new_album->getId())
                                ->setPosition($new_element_position)
                                ->save();

                        $trash = array_shift($tracks_results);
                        $pos = 0;
                        foreach($tracks_results as $track) {
                            $new_track = new Media_Model_Gallery_Music_Track();
                            $new_track_position = $new_track->getNextTrackPosition();
                            $new_track
                                ->setAlbumId($new_album->getAlbumId())
                                ->setName($track->trackName)
                                ->setDuration($track->trackTimeMillis)
                                ->setArtworkUrl($track->artworkUrl100)
                                ->setArtistName($track->artistName)
                                ->setAlbumName($track->collectionName)
                                ->setPrice($track->trackPrice)
                                ->setCurrency($track->currency)
                                ->setPurchaseUrl($track->trackViewUrl)
                                ->setStreamUrl($track->previewUrl)
                                ->setPosition($pos)
                                ->setType('itunes')
                                ->save();
                            $pos++;
                        }

                        $html_content .= $this->getLayout()
                            ->addPartial('list_element', 'Core_View_Default', 'media/application/gallery/music/edit/list/li.phtml')
                            ->setOptionValue($option_value)
                            ->setGalleryId($datas['gallery_id'])
                            ->setElement($music_positions)
                            ->setType('album')
                            ->toHtml()
                            ;
                    }
                }
                //Chansons seulement
                if(isset($datas['songs'])) {
                    $return_tracks = array();
                    foreach($datas['songs'] as $track) {
                        $track_results = $itunes_api->lookup($track, 'id', array(
                            'entity' => 'song',
                            'limit' => 1,
                            'media' => 'music'
                        ))->results;

                        $new_track = new Media_Model_Gallery_Music_Track();
                        $new_track
                            ->setGalleryId($datas['gallery_id'])
                            ->setName($track_results[0]->trackName)
                            ->setDuration($track_results[0]->trackTimeMillis)
                            ->setArtworkUrl($track_results[0]->artworkUrl100)
                            ->setArtistName($track_results[0]->artistName)
                            ->setAlbumName($track_results[0]->collectionName)
                            ->setPrice($track_results[0]->trackPrice)
                            ->setCurrency($track_results[0]->currency)
                            ->setPurchaseUrl($track_results[0]->trackViewUrl)
                            ->setStreamUrl($track_results[0]->previewUrl)
                            ->setType('itunes')
                            ->save();

                        $music_positions = new Media_Model_Gallery_Music_Elements();
                        $new_element_position = $music_positions->getNextElementsPosition();
                        $music_positions
                                ->setGalleryId($datas['gallery_id'])
                                ->setTrackId($new_track->getId())
                                ->setPosition($new_element_position)
                                ->save();

                        $html_content .= $this->getLayout()
                            ->addPartial('list_element', 'Core_View_Default', 'media/application/gallery/music/edit/list/li.phtml')
                            ->setOptionValue($option_value)
                            ->setGalleryId($datas['gallery_id'])
                            ->setElement($music_positions)
                            ->setType('track')
                            ->toHtml()
                            ;
                    }
                }

                $html = array(
                    'success' => 1,
                    'success_message' => $this->_('Info successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0,
                    'content' => $html_content
                );
            } catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }
            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function soundcloudcallbackAction() {
        $this->loadPartials('media_application_gallery_music_soundcloud_callback', false);
    }

    public function savesoundcloudAction() {
        if($datas = $this->getRequest()->getPost()) {
            $html = '';
            $html_content = '';
            try {

                // Test s'il y a un value_id
                if(empty($datas['value_id'])) throw new Exception($this->_("An error occurred while saving. Please try again later."));

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);

                $music = new Media_Model_Gallery_Music();
                $id = $music->getSoundcloudId();
                $secret = $music->getSoundcloudSecret();
                $soundcloud_api = new Media_Model_Library_Soundcloud($id, $secret);
                //Ajout playlist perso
                if(isset($datas['playlists'])) {
                    foreach($datas['playlists'] as $playlist) {
                        $playlist_result = $soundcloud_api->get('playlists/'.$playlist.'.json');
                        $playlist = Zend_Json::decode($playlist_result, Zend_Json::TYPE_OBJECT);

                        $new_album = new Media_Model_Gallery_Music_Album();
                        $new_album
                            ->setGalleryId($datas['gallery_id'])
                            ->setName($playlist->title)
                            ->setArtworkUrl($playlist->artwork_url)
                            ->setArtistName($playlist->user->username)
                            ->setType('soundcloud')
                            ->save();

                        $music_positions = new Media_Model_Gallery_Music_Elements();
                        $new_element_position = $music_positions->getNextElementsPosition();
                        $music_positions
                                ->setGalleryId($datas['gallery_id'])
                                ->setAlbumId($new_album->getId())
                                ->setPosition($new_element_position)
                                ->save();

                        //Chansons sélectionnées dans cette playlist
                        $pos = 0;
                        if(isset($datas['songs'])) {
                            foreach($datas['songs'] as $track) {
                                $track_result = $soundcloud_api->get('tracks/'.$track.'.json');
                                $track = Zend_Json::decode($track_result, Zend_Json::TYPE_OBJECT);
                                if($track->streamable == true) {
                                    $new_track = new Media_Model_Gallery_Music_Track();
                                    $new_track_position = $new_track->getNextTrackPosition();
                                    $new_track
                                        ->setAlbumId($new_album->getAlbumId())
                                        ->setName($track->title)
                                        ->setDuration($track->duration)
                                        ->setArtworkUrl($track->artwork_url)
                                        ->setArtistName($track->user->username)
                                        ->setAlbumName($playlist->title)
                                        ->setStreamUrl($track->stream_url)
                                        ->setType('soundcloud')
                                        ->setPosition($pos)
                                        ->save();
                                    $pos++;
                                }
                            }
                        }

                        $html_content .= $this->getLayout()
                            ->addPartial('list_element', 'Core_View_Default', 'media/application/gallery/music/edit/list/li.phtml')
                            ->setOptionValue($option_value)
                            ->setGalleryId($datas['gallery_id'])
                            ->setElement($music_positions)
                            ->setType('album')
                            ->toHtml()
                            ;
                    }
                }

                //Tout un album
                if(isset($datas['albums'])) {
                    foreach($datas['albums'] as $playlist) {
                        $playlist_result = $soundcloud_api->get('playlists/'.$playlist.'.json');
                        $playlist = Zend_Json::decode($playlist_result, Zend_Json::TYPE_OBJECT);
                        $new_album = new Media_Model_Gallery_Music_Album();
                        $new_album
                            ->setGalleryId($datas['gallery_id'])
                            ->setName($playlist->title)
                            ->setArtworkUrl($playlist->artwork_url)
                            ->setArtistName($playlist->user->username)
                            ->setType('soundcloud')
                            ->save();

                        $music_positions = new Media_Model_Gallery_Music_Elements();
                        $new_element_position = $music_positions->getNextElementsPosition();
                        $music_positions
                                ->setGalleryId($datas['gallery_id'])
                                ->setAlbumId($new_album->getId())
                                ->setPosition($new_element_position)
                                ->save();

                        //Toutes les chansons de cet album
                        $pos = 0;
                        foreach($playlist->tracks as $track) {
                            if($track->streamable == true) {
                                $new_track = new Media_Model_Gallery_Music_Track();
                                $new_track_position = $new_track->getNextTrackPosition();
                                $new_track
                                    ->setAlbumId($new_album->getAlbumId())
                                    ->setName($track->title)
                                    ->setDuration($track->duration)
                                    ->setArtworkUrl($track->artwork_url)
                                    ->setArtistName($track->user->username)
                                    ->setAlbumName($playlist->title)
                                    ->setStreamUrl($track->stream_url)
                                    ->setType('soundcloud')
                                    ->setPosition($pos)
                                    ->save();
                                $pos++;
                            }
                        }

                        $html_content .= $this->getLayout()
                            ->addPartial('list_element', 'Core_View_Default', 'media/application/gallery/music/edit/list/li.phtml')
                            ->setOptionValue($option_value)
                            ->setGalleryId($datas['gallery_id'])
                            ->setElement($music_positions)
                            ->setType('album')
                            ->toHtml()
                            ;

                    }
                }

                //Chansons isolées
                if(!isset($datas['playlists']) && !isset($datas['albums']) && isset($datas['songs'])) {
                    foreach($datas['songs'] as $track) {
                        $track_result = $soundcloud_api->get('tracks/'.$track.'.json');
                        $track = Zend_Json::decode($track_result, Zend_Json::TYPE_OBJECT);
                        if($track->streamable == true) {
                            $new_track = new Media_Model_Gallery_Music_Track();
                            $new_track_position = $new_track->getNextTrackPosition();
                            $new_track
                                ->setGalleryId($datas['gallery_id'])
                                ->setName($track->title)
                                ->setDuration($track->duration)
                                ->setArtworkUrl($track->artwork_url)
                                ->setArtistName($track->user->username)
                                ->setStreamUrl($track->stream_url)
                                ->setType('soundcloud')
                                ->save()
                            ;

                            $music_positions = new Media_Model_Gallery_Music_Elements();
                            $new_element_position = $music_positions->getNextElementsPosition();
                            $music_positions
                                    ->setGalleryId($datas['gallery_id'])
                                    ->setTrackId($new_track->getId())
                                    ->setPosition($new_element_position)
                                    ->save();

                            $html_content .= $this->getLayout()
                            ->addPartial('list_element', 'Core_View_Default', 'media/application/gallery/music/edit/list/li.phtml')
                            ->setOptionValue($option_value)
                            ->setGalleryId($datas['gallery_id'])
                            ->setElement($music_positions)
                            ->setType('track')
                            ->toHtml()
                            ;

                        }
                    }
                }

                $html = array(
                    'success' => 1,
                    'success_message' => $this->_('Info successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0,
                    'content' => $html_content
                );
            } catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }
            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function searchpodcastAction() {
        if($datas = $this->getRequest()->getPost()) {

            $html = '';
            try {

                $podcast = new Media_Model_Gallery_Music_Type_Podcast();
                $data = $podcast->setFeedUrl($datas['podcast_url'])->parse();

                if(empty($data)) {
                    throw new Exception($this->_("Podcast type is invalid or can't be found"));
                } else {
                    $html = array(
                        'success' => 1,
                        'results' => $data
                    );
                }
            } catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }
            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function savepodcastAction() {

        if($datas = $this->getRequest()->getPost()) {

            $html_content = '';
            try {

                // Test s'il y a un value_id
                if(empty($datas['value_id'])) throw new Exception($this->_("An error occurred while saving. Please try again later."));

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);

                $podcast = new Media_Model_Gallery_Music_Type_Podcast();
                $podcast_data = $podcast->setFeedUrl($datas['podcast_url'])->parse();

                $new_album = new Media_Model_Gallery_Music_Album();
                $new_album->setGalleryId($datas['gallery_id'])
                    ->setName($podcast_data["title"])
                    ->setArtworkUrl($podcast_data["image"])
                    ->setArtistName($podcast_data["author"])
                    ->setPodcastUrl($datas["podcast_url"])
                    ->setType("podcast")
                    ->save()
                ;

                $music_positions = new Media_Model_Gallery_Music_Elements();
                $new_element_position = $music_positions->getNextElementsPosition();
                $music_positions
                    ->setGalleryId($datas['gallery_id'])
                    ->setAlbumId($new_album->getId())
                    ->setPosition($new_element_position)
                    ->save()
                ;

                $html_content .= $this->getLayout()
                    ->addPartial('list_element', 'Core_View_Default', 'media/application/gallery/music/edit/list/li.phtml')
                    ->setOptionValue($option_value)
                    ->setGalleryId($datas['gallery_id'])
                    ->setElement($music_positions)
                    ->setType('album')
                    ->toHtml()
                ;

                $html = array(
                    'success' => 1,
                    'success_message' => $this->_('Info successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0,
                    'content' => $html_content
                );

            } catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }
            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function savecustomAction() {
        if($datas = $this->getRequest()->getPost()) {
            $html = '';
            $html_content = '';
            try {

                // Test s'il y a un value_id
                if(empty($datas['value_id']) OR empty($datas['gallery_id'])) throw new Exception($this->_("An error occurred while saving. Please try again later."));

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);

                //Si au moins un titre ou un nom, on crée un album
                $album = new Media_Model_Gallery_Music_Album();
                if($datas['album']['name'] || $datas['album']['artist_name']) {
                    $album->find($datas['album_id']);
                    $album
                        ->setGalleryId($datas['gallery_id'])
                        ->setName($datas['album']['name'])
                        ->setArtistName($datas['album']['artist_name'])
                        ->setArtworkUrl(null)
                        ->setType('custom')
                        ->save()
                    ;

                    if(isset($datas['artwork_url']) && !empty($datas['artwork_url'])) {
                        $base_img_src = Core_Model_Directory::getTmpDirectory(true).'/';
                        if(file_exists($base_img_src.$datas['artwork_url'])) {
                            $base_img_src = $base_img_src.$datas['artwork_url'];
                            $relativePath = $option_value->getImagePathTo("");
                            $img_dst = Application_Model_Application::getBaseImagePath().'/'.$relativePath;
                            if(!is_dir($img_dst)) mkdir($img_dst, 0777, true);
                            $img_dst .= '/'.$datas['artwork_url'];
                            @rename($base_img_src, $img_dst);
                            if(!file_exists($img_dst)) throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                            $artwork_url = '/'.$relativePath.'/'.$datas['artwork_url'];
                            $album->setArtworkUrl($artwork_url)->save();
                        }
                    }

                    if(isset($datas['delete_image']) && $datas['delete_image'] == 'true') {
                        $album->setArtworkUrl(null);
                    }

                    $album->save();

                    $music_positions = new Media_Model_Gallery_Music_Elements();
                    $music_positions->find($datas['album_id'], 'album_id');
                    if(!$music_positions->getAlbumId()) {
                        $new_element_position = $music_positions->getNextElementsPosition();
                        $music_positions
                            ->setGalleryId($datas['gallery_id'])
                            ->setAlbumId($album->getId())
                            ->setPosition($new_element_position)
                            ->save()
                        ;
                    }
                    $has_album = $music_positions;
                }

                //Suppression album donc remise chansons dans les éléments généraux
                if($datas['delete_album'] && $datas['album_id']) {
                    $tracks = new Media_Model_Gallery_Music_Track();
                    $tracks = $tracks->findAll(array('album_id' => $datas['album_id']), 'position ASC');
                    foreach($tracks as $track) {
                        $track
                            ->setAlbumId(null)
                            ->setGalleryId($datas['gallery_id'])
                            ->save()
                        ;

                        $track_position = new Media_Model_Gallery_Music_Elements();
                        $new_element_position = $track_position->getNextElementsPosition();
                        $track_position
                            ->setPosition($new_element_position)
                            ->setGalleryId($datas['gallery_id'])
                            ->setTrackId($track->getId())
                            ->setAlbumId(null)
                            ->save()
                        ;

                        $html_content .= $this->getLayout()
                            ->addPartial('list_element', 'Core_View_Default', 'media/application/gallery/music/edit/list/li.phtml')
                            ->setOptionValue($option_value)
                            ->setGalleryId($datas['gallery_id'])
                            ->setElement($track_position)
                            ->setType('track')
                            ->toHtml()
                        ;
                    }
                    $album->find($datas['album_id'])->delete();
                }

                //Nouvel album
                $pos = 0;
                if(isset($datas['track'])) {
                    foreach($datas['track'] as $id => $track) {
                        $new_track = new Media_Model_Gallery_Music_Track();
                        $new_track->find($id);
                        $new_track
                            ->setName($track['title'])
                            ->setDuration($track['duration'])
                            ->setArtistName($track['artist'])
                            ->setStreamUrl($track['url'])
                            ->setType('custom')
                            ->save()
                        ;

                        if(!$datas['delete_album']) {
                            if($album->getId()) {
                                $new_track
                                    ->setAlbumId($album->getId())
                                    ->setGalleryId(null)
                                    ->setArtworkUrl($album->getArtworkUrl())
                                    ->save()
                                ;

                            } else {
                                $new_track
                                    ->setGalleryId($datas['gallery_id'])
                                    ->setAlbumId(null)
                                    ->save()
                                ;
                            }
                        }

                        if($album->getId()) {
                            $new_track->setPosition($track['position'])->save();
                            $pos++;
                        } else {
                            $music_positions = new Media_Model_Gallery_Music_Elements();
//                            $new_element_position = $music_positions->getNextElementsPosition();
                            $music_positions
                                ->setGalleryId($datas['gallery_id'])
                                ->setTrackId($new_track->getId())
                                ->setPosition($track['position'])
                                ->save()
                            ;
                        }
                        if(!$datas['delete_album'] && !$datas['album_id'] && !$album->getId()) {
                            $html_content .= $this->getLayout()
                                ->addPartial('list_element', 'Core_View_Default', 'media/application/gallery/music/edit/list/li.phtml')
                                ->setOptionValue($option_value)
                                ->setGalleryId($datas['gallery_id'])
                                ->setElement($music_positions)
                                ->setType('track')
                                ->toHtml()
                            ;
                        }
                    }
                }

                if(isset($has_album)) {
                    $html_content .= $this->getLayout()
                        ->addPartial('list_element', 'Core_View_Default', 'media/application/gallery/music/edit/list/li.phtml')
                        ->setOptionValue($option_value)
                        ->setGalleryId($datas['gallery_id'])
                        ->setElement($has_album)
                        ->setType('album')
                        ->toHtml()
                    ;
                }

                $html = array(
                    'success' => 1,
                    'success_message' => $this->_('Info successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0,
                    'content' => $html_content
                );
            } catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }
            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function checkfileAction() {
        if($datas = $this->getRequest()->getParams()) {
            $html = '';
            try {
                $url = $datas['file'];
                $url = str_replace(' ', '%20', str_replace('§', '/', $url));
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_NOBODY, true);
                curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 10);
                $res = curl_exec($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                switch($code) {
                    case 200:
                        $extension = explode('.', $url);
                        $extension = $extension[count($extension)-1];
                        if($extension != 'mp3') throw new Exception($this->_("Incorrect filetype."));
                        $html = array(
                            'success' => 1
                        );
                        break;
                    default:
                        throw new Exception($this->_("No file found."));
                }
            } catch(Exception $e) {
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
