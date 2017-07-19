<?php

class Media_Mobile_Api_Music_PlaylistController extends Application_Controller_Mobile_Default {

    public function _toJson($playlist){

        $artworkUrl = null;
        if(is_file(Application_Model_Application::getBaseImagePath().$playlist->getArtworkUrl())) {
            $artworkUrl =$this->getRequest()->getBaseUrl() . Application_Model_Application::getImagePath() . $playlist->getArtworkUrl();
        }

        $json = array(
            "id"                => $playlist->getId(),
            "name"              => $playlist->getName(),
            "artworkUrl"        => $artworkUrl,
            "totalDuration"     => $playlist->getTotalDuration(),
            "totalTracks"       => $playlist->getTotalTracks()
        );

        return $json;
    }

    public function findAction() {

        if($value_id = $this->getRequest()->getParam('value_id')
           && $playlist_id = $this->getRequest()->getParam('playlist_id')) {

            try {

                $playlists = new Media_Model_Gallery_Music();
                $playlist = $playlists->find($playlist_id);

                $data = array("playlist" => $this->_toJson($playlist));

            }
            catch(Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }

        } else {
            $data = array('error' => 1, 'message' => 'An error occurred during process. Please try again later.');
        }

        $this->_sendHtml($data);

    }

    public function findallAction() {

        if($value_id = $this->getRequest()->getParam('value_id')) {

            try {

                $playlists = new Media_Model_Gallery_Music();
                $playlists = $playlists->findAll(array('value_id' => $value_id), 'position ASC');

                $json = array();
                foreach($playlists as $playlist) {
                    $json[] = $this->_toJson($playlist);
                }

                $data = array(
                    "playlists" => $json,
                    "artwork_placeholder" => $this->getRequest()->getBaseUrl().Media_Model_Library_Image::getImagePathTo("/musics/default_album.jpg")
                );


            }
            catch(Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }

        } else {
            $data = array('error' => 1, 'message' => 'An error occurred during process. Please try again later.');
        }

        $this->_sendHtml($data);

    }

    public function getpagetitleAction() {
        $option = $this->getCurrentOptionValue();
        $data['page_title'] = $option->getTabbarName();
        $this->_sendHtml($data);
    }


    /** API v2 introduced in Siberian 5.0 with Progressive Web Apps. */

    public function findallv2Action() {

        $request = $this->getRequest();

        try {
            /** Do your stuff here. */
            if($value_id = $request->getParam("value_id")) {

                $playlist_model = new Media_Model_Gallery_Music();
                $playlists = $playlist_model->findAll(array(
                    "value_id" => $value_id
                ), "position ASC");

                $json = array();
                foreach($playlists as $playlist) {
                    $playlist_json = $this->_toJson($playlist);

                    $album_model = new Media_Model_Gallery_Music_Album();
                    $album = $album_model->find($playlist->getId());

                    $album_tracks = $album->getAllTracks(true);
                    foreach($album_tracks as $track) {
                        $tracks_json[] = Media_Model_Gallery_Music_Track::_toJson($track);
                    }

                    $playlist_json["tracks"] = $tracks_json;

                    $json[] = $playlist_json;
                }

                $artwork_placeholder = Core_Model_Directory::getBasePathTo("/images/library/musics/default_album.jpg");
                $artwork_placeholder_b64 = Siberian_Image::open($artwork_placeholder)->inline();
                $album_cover_b64 = Siberian_Image::open($artwork_placeholder)->cropResize(64)->inline();

                $payload = array(
                    "success"               => true,
                    "playlists"             => $json,
                    "artwork_placeholder"   => $artwork_placeholder_b64,
                    "track_placeholder"     => $album_cover_b64
                );


            } else {
                throw new Siberian_Exception(__("Missing value_id."));
            }

        } catch(Exception $e) {
            $payload = array(
                "error" => true,
                "message" => __("An unknown error occurred, please try again later.")
            );
        }

        $this->_sendJson($payload);

    }

}