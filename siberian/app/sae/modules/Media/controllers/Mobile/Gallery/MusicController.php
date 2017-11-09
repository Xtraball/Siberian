<?php

class Media_Mobile_Gallery_MusicController extends Application_Controller_Mobile_Default {

    public function listAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {
                if(empty($datas['option_value_id'])) throw new Exception($this->_('An error occurred while loading. Please try again later.'));

                $playlists = new Media_Model_Gallery_Music();
                $playlists = $playlists->findAll(array('value_id' => $datas['option_value_id']), 'position ASC');

                $type = $datas['type'];

                if($type == 'albums' || $type == 'tracks') {
                    $albums = array();
                    $tracks = array();
                    foreach($playlists as $playlist) {
                        $elements = new Media_Model_Gallery_Music_Elements();
                        $elements = $elements->findAll(array('gallery_id' => $playlist->getId()), 'position ASC');
                        foreach($elements as $element) {
                            if($element->getAlbumId()) {
                                $album = new Media_Model_Gallery_Music_Album();
                                $album->find($element->getAlbumId());
                                $albums[] = $album;
                                $album_tracks = $album->getAllTracks(true);
                                foreach($album_tracks as $album_track) {
                                    $tracks[] = $album_track;
                                }
                            }
                            if($element->getTrackId()) {
                                $track = new Media_Model_Gallery_Music_Track();
                                $track->find($element->getTrackId());
                                $tracks[] = $track;
                            }
                        }
                    }
                }

                if($type == 'albums') {
                    $partial_html = $this->getLayout()->addPartial('music_albums', 'core_view_default', 'media/gallery/music/l1/view/list/music/albums.phtml')->setCurrentOption($this->getCurrentOptionValue())->setAlbums($albums)->toHtml();
                    $html = array('html' => $partial_html, 'title' => $this->getCurrentOptionValue()->getTabbarName(), 'id' => 'albums', 'albums' => $albums);
                } else if($type == 'tracks') {
                    $partial_html = $this->getLayout()->addPartial('music_tracks', 'core_view_default', 'media/gallery/music/l1/view/list/music/tracks.phtml')->setCurrentOption($this->getCurrentOptionValue())->setTracks($tracks)->toHtml();
                    $html = array('html' => $partial_html, 'title' => $this->getCurrentOptionValue()->getTabbarName(), 'id' => 'tracks', 'tracks' => $tracks);
                } else {
                    $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
                    $this->getLayout()->getPartial('content')->setPlaylists($playlists);
                    $html = array('html' => $this->getLayout()->render(), 'title' => $this->getCurrentOptionValue()->getTabbarName(), 'id' => 'playlists', 'playlists' => $playlists);
                }

            }
            catch(Exception $e) {
                $html = array('message' => $e->getMessage());
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));

        }

    }

    public function listplaylistAction() {
        if($datas = $this->getRequest()->getPost()) {
            try {

                if(empty($datas['option_value_id'])) throw new Exception($this->_('An error occurred while loading. Please try again later.'));

                $playlist = new Media_Model_Gallery_Music();
                $playlist->find($datas['playlist_id']);
                $this->loadPartials('media_mobile_gallery_music_list_l1_playlists', false);
                $this->getLayout()->getPartial('content')->setPlaylist($playlist);
                $html = array('html' => $this->getLayout()->render(), 'title' => $this->getCurrentOptionValue()->getTabbarName(), 'id' => 'playlist', 'playlist' => $playlist);

            }
            catch(Exception $e) {
                $html = array('message' => $e->getMessage());
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function listalbumAction() {
        if($datas = $this->getRequest()->getPost()) {
            try {

                if(empty($datas['option_value_id'])) throw new Exception($this->_('An error occurred while loading. Please try again later.'));

                $album = new Media_Model_Gallery_Music_Album();
                $album->find($datas['album_id']);
                $album_tracks = $album->getAllTracks(true);

                $this->loadPartials('media_mobile_gallery_music_list_l1_tracks', false);
                $this->getLayout()->getPartial('content')->setTracks($album_tracks);
                $html = array('html' => $this->getLayout()->render(), 'title' => $this->getCurrentOptionValue()->getTabbarName(), 'id' => 'tracks', 'tracks' => $album_tracks);

            }
            catch(Exception $e) {
                $html = array('message' => $e->getMessage());
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function playlistAction() {

        if($playlist_id = $this->getRequest()->getParam('playlist_id')) {
            try {

                $playlist = new Media_Model_Gallery_Music();
                $playlist->find($playlist_id);

                $partial_html = $this->getLayout()->addPartial('music_tracks', 'core_view_default', 'media/gallery/music/l1/view/list/playlist.phtml')->setCurrentOption($this->getCurrentOptionValue())->setPlaylist($playlist)->toHtml();
                $html = array(
                    'html' => $partial_html,
                    'title' => $playlist->getName()
                );

            }
            catch(Exception $e) {
                $html = array('message' => $e->getMessage());
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function albumAction() {

        if($album_id = $this->getRequest()->getParam('album_id')) {
            try {

                $album = new Media_Model_Gallery_Music_Album();
                $album->find($album_id);
                if($album->getType() == 'podcast') {
                    $podcast_xml = simplexml_load_file($album->getPodcastUrl());
                    $podcast = $podcast_xml->channel;
                    $album_tracks = array();
                    foreach($podcast->item as $item) {
                        $track = new Media_Model_Gallery_Music_Track();
                        $track->setName((string) $item->title)
                                ->setDuration((int) $item->enclosure["length"])
                                ->setStreamUrl((string) $item->enclosure["url"]);
                        $album_tracks[] = $track;
                    }
                } else {
                    $album_tracks = $album->getAllTracks(true);
                }

                $partial_html = $this->getLayout()->addPartial('music_tracks', 'core_view_default', 'media/gallery/music/l1/view/list/tracks.phtml')
                    ->setCurrentOption($this->getCurrentOptionValue())
                    ->setTracks($album_tracks)
                    ->setAlbum($album)
                    ->toHtml();

                $html = array(
                    'html' => $partial_html,
                    'title' => $album->getName()
                );
            }
            catch(Exception $e) {
                $html = array('message' => $e->getMessage());
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function playlistalbumsAction() {

        if($playlist_id = $this->getRequest()->getPost('playlist_id')) {
            try {

                $playlist = new Media_Model_Gallery_Music();
                $playlist->find($playlist_id);
                $rough_tracks = $playlist->getAllTracks(false);
                $tracks = array();
                foreach($rough_tracks as $track) {
                    $new_track = array(
                        "track_name" => $track->getName(),
                        "track_artist" => $track->getArtistName(),
                        "track_url" => $track->getStreamUrl(),
                        "track_buy_url" => $track->getPurchaseUrl()
                    );
                    if($track->getType() != 'podcast') {
                        $new_track["track_duration"] = $track->getFormatedDuration();
                    } else {
                        $new_track["track_duration"] = $track->getFormatedDuration($track->getDuration());
                    }
                    $tracks[] = $new_track;
                }
                $html = array(
                    'tracks' => $tracks,
                    'title' => $playlist->getName()
                );

            }
            catch(Exception $e) {
                $html = array('message' => $e->getMessage());
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function playlistallAction() {

        if($type = $this->getRequest()->getPost('type')) {
            try {

                $tracks = array();
                $playlists = new Media_Model_Gallery_Music();
                $playlists = $playlists->findAll(array('value_id' => $this->getRequest()->getPost('option_value_id')), 'position ASC');
                foreach($playlists as $playlist) {
                    if($type == 'tracks' || $type == 'playlists') {
                        $rough_tracks = $playlist->getAllTracks(true);
                    } elseif($type == 'albums') {
                        $rough_tracks = $playlist->getAllTracks(false);
                    }
                    foreach($rough_tracks as $track) {
                        $new_track = array(
                            "track_name" => $track->getName(),
                            "track_artist" => $track->getArtistName(),
                            "track_url" => $track->getStreamUrl(),
                            "track_buy_url" => $track->getPurchaseUrl()
                        );
                        if($track->getType() != 'podcast') {
                            $new_track["track_duration"] = $track->getFormatedDuration();
                        } else {
                            $new_track["track_duration"] = $track->getFormatedDuration($track->getDuration());
                        }
                        $tracks[] = $new_track;
                    }
                }
                $html = array(
                    'tracks' => $tracks
                );
            }
            catch(Exception $e) {
                $html = array('message' => $e->getMessage());
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }
}
