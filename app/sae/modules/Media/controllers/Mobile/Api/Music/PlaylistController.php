<?php

class Media_Mobile_Api_Music_PlaylistController extends Application_Controller_Mobile_Default {

    public function _toJson($playlist){

        $artworkUrl = null;
        if(is_file(Application_Model_Application::getBaseImagePath().$playlist->getArtworkUrl())) {
            $artworkUrl =$this->getRequest()->getBaseUrl() . Application_Model_Application::getImagePath() . $playlist->getArtworkUrl();
        }

        $json = array(
            "id" => $playlist->getId(),
            "name" => $playlist->getName(),
            "artworkUrl" => $artworkUrl,
            "totalDuration" => $playlist->getTotalDuration(),
            "totalTracks" => $playlist->getTotalTracks()
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

}