<?php

class Media_Mobile_Api_Music_AlbumController extends Application_Controller_Mobile_Default {


    public function _toJson($album) {

        if($album instanceof Media_Model_Gallery_Music_Album) {
            $total_duration = $album->getTotalDuration();
            $total_tracks = $album->getTotalTracks();
            $url = $this->getPath("media/mobile_gallery_music_album/index", array("value_id" => $this->getRequest()->getParam("value_id"), "album_id" => $album->getId()));
            $element = "album";
        } else {
            $total_duration = $album->getFormatedDuration();
            $total_tracks = 1;
            $url = $this->getPath("media/mobile_gallery_music_album/index", array("value_id" => $this->getRequest()->getParam("value_id"), "track_id" => $album->getId()));
            $element = "track";
        }

        $artworkUrl = $album->getArtworkUrl();
        if(stripos($artworkUrl, "http") === false) {
            $artworkUrl = $this->getRequest()->getBaseUrl() . $artworkUrl;
        }

        $json = array(
            "id" => $album->getId(),
            "name" => $album->getName(),
            "artworkUrl" => $artworkUrl,
            "artistName" => $album->getArtistName(),
            "totalDuration" => $total_duration,
            "totalTracks" => $total_tracks,
            "path" => $url,
            "type" => $album->getType(),
            "element" => $element
        );

        return $json;
    }

    public function findAction() {

        if($value_id = $this->getRequest()->getParam('value_id')
           && ($album_id = $this->getRequest()->getParam('album_id') OR $track_id = $this->getRequest()->getParam('track_id'))) {

            try {

                $elements = new Media_Model_Gallery_Music_Elements();
                if($album_id) {

                    $element = $elements->find($album_id, "album_id");

                    $album = new Media_Model_Gallery_Music_Album();
                    $album->find($element->getAlbumId());

                    $data = array("album" => $this->_toJson($album));

                } else if($track_id) {
                    $element = $elements->find($track_id, "track_id");

                    $track = new Media_Model_Gallery_Music_Track();
                    $track->find($element->getTrackId());

                    $data = array("album" => $this->_toJson($track));

                } else {
                    $data = array('error' => 1, 'message' => $this->_("This element is not an album."));
                }
            }
            catch(Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }

        } else {
            $data = array('error' => 1, 'message' => $this->_("An error occurred while loading. Please try again later."));
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

                    $elements = new Media_Model_Gallery_Music_Elements();
                    $elements = $elements->findAll(array('gallery_id' => $playlist->getId()), 'position ASC');

                    foreach($elements as $element) {

                        if($element->getAlbumId()) {

                            $album = new Media_Model_Gallery_Music_Album();
                            $album->find($element->getAlbumId());

                            $json[] = $this->_toJson($album);

                        } else if($element->getTrackId()) {

                            $track = new Media_Model_Gallery_Music_Track();
                            $track->find($element->getTrackId());

                            $json[] = $this->_toJson($track);

                        }
                    }
                }

                $data = array("albums" => $json);


            }
            catch(Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }


        }else{
            $data = array('error' => 1, 'message' => 'An error occurred during process. Please try again later.');
        }
        $this->_sendHtml($data);
    }

    public function findbyplaylistAction() {


        if($value_id = $this->getRequest()->getParam('value_id')
           && $playlist_id = $this->getRequest()->getParam('playlist_id')) {

            try {

                $elements = new Media_Model_Gallery_Music_Elements();
                $elements = $elements->findAll(array('gallery_id' => $playlist_id), 'position ASC');

                $json = array();

                foreach($elements as $element) {


                    if($element->getAlbumId()) {

                        $album = new Media_Model_Gallery_Music_Album();
                        $album->find($element->getAlbumId());

                        $json[] = $this->_toJson($album);
                    } else if($element->getTrackId()) {

                        $track = new Media_Model_Gallery_Music_Track();
                        $track->find($element->getTrackId());

                        $json[] = $this->_toJson($track);

                    }

                }

                $data = array("albums" => $json);

            }
            catch(Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }


        }else{
            $data = array('error' => 1, 'message' => 'An error occurred during process. Please try again later.');
        }
        $this->_sendHtml($data);
    }

    /** API v2 introduced in Siberian 5.0 with Progressive Web Apps. */
    public function findallv2Action() {

        try {
            /** Do your stuff here. */
            if($value_id = $this->getRequest()->getParam("value_id")) {

                $playlists = new Media_Model_Gallery_Music();
                $playlists = $playlists->findAll(array(
                    "value_id" => $value_id
                ), "position ASC");

                $albums_json = array();

                foreach($playlists as $playlist) {

                    $elements = new Media_Model_Gallery_Music_Elements();
                    $elements = $elements->findAll(array(
                        "gallery_id" => $playlist->getId()
                    ), "position ASC");

                    foreach($elements as $element) {

                        if($element->getAlbumId()) {

                            $album = new Media_Model_Gallery_Music_Album();
                            $album->find($element->getAlbumId());

                            $albums_json[] = Media_Model_Gallery_Music_Album::_toJson($value_id, $album);

                        } else if($element->getTrackId()) {

                            $track = new Media_Model_Gallery_Music_Track();
                            $track->find($element->getTrackId());

                            $albums_json[] = Media_Model_Gallery_Music_Track::_toJson($track);

                        }
                    }
                }

                $payload = array(
                    "success" => true,
                    "albums" => $albums_json
                );

            } else {
                $payload = array(
                    "error" => true,
                    "message" => __("Missing value_id.")
                );
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