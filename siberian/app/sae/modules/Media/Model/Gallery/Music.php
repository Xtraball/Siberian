<?php

class Media_Model_Gallery_Music extends Core_Model_Default {

    protected $_key;

    protected $_tracks = array();
    protected $_albums = array();

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Media_Model_Db_Table_Gallery_Music';
        $this->_key = Api_Model_Key::findKeysFor('soundcloud');
        return $this;
    }

    /**
     * @return array
     */
    public function getInappStates($value_id) {

        $in_app_states = array(
            array(
                "state" => "music-playlist-list",
                "offline" => false,
                "params" => array(
                    "value_id" => $value_id,
                ),
            ),
        );

        return $in_app_states;
    }

    public function getAllAlbums() {
        if(!$this->_albums) {
            $elements = new Media_Model_Gallery_Music_Elements();
            $elements = $elements->findAll(array('gallery_id' => $this->getId()), 'position ASC');
            foreach($elements as $element) {
                if($element->getAlbumId()) {
                    $album = new Media_Model_Gallery_Music_Album();
                    $album->find($element->getAlbumId());
                    $this->_albums[] = $album;
                }
            }
        }
        return $this->_albums;
    }

    public function getAllTracks($without_albums) {
        if(!$this->_tracks) {
            $elements = new Media_Model_Gallery_Music_Elements();
            $elements = $elements->findAll(array('gallery_id' => $this->getId()), 'position ASC');
            foreach($elements as $element) {
                if($element->getAlbumId()) {
                    $album = new Media_Model_Gallery_Music_Album();
                    $album->find($element->getAlbumId());
                    if($album->getType() != 'podcast') {
                        $tracks = new Media_Model_Gallery_Music_Track();
                        $tracks = $tracks->findAll(array('album_id' => $element->getAlbumId()), 'position ASC');
                        foreach($tracks as $track) {
                            $this->_tracks[] = $track;
                        }
                    } else {
                        $podcast = new Media_Model_Gallery_Music_Type_Podcast();
                        $data = $podcast->setFeedUrl($album->getPodcastUrl())->parse();
                        foreach($data["items"] as $item) {
                            $track = new Media_Model_Gallery_Music_Track();
                            $track->setName($item["title"])
                                    ->setDuration($item["duration"])
                                    ->setStreamUrl($item["stream_url"])
                                    ->setType('podcast');
                            $this->_tracks[] = $track;
                        }
                    }
                } else {
                    if($without_albums == true) {
                        $track = new Media_Model_Gallery_Music_Track();
                        $track->find($element->getTrackId());
                        $this->_tracks[] = $track;
                    }
                }
            }
        }

        return $this->_tracks;
    }

    public function getMosaicArtworkUrl() {
        $artwork_url = $this->getData('artwork_url');
        $artwork_urls = array();
        if($artwork_url) {
            $artwork_urls[] = Application_Model_Application::getImagePath().$artwork_url;
        } else {
            $elements = new Media_Model_Gallery_Music_Elements();
            $elements = $elements->findAll(array('gallery_id' => $this->getId()), 'position ASC');
            if($elements->count() > 0) {
                $i = 0;
                foreach($elements as $element) :
                    if($element->getAlbumId()) :
                        $objet = new Media_Model_Gallery_Music_Album();
                        $objet->find($element->getAlbumId());
                    endif;
                    if($element->getTrackId()) :
                        $objet = new Media_Model_Gallery_Music_Track();
                        $objet->find($element->getTrackId());
                    endif;
                    if($i < 4 && $objet->getArtworkUrl()) :
                        $artwork_urls[] = $objet->getArtworkUrl();
                        $i++;
                    endif;
                endforeach;
                while(count($artwork_urls) < 4) {
                    $objet = new Media_Model_Gallery_Music_Album();
                    $artwork_urls[] = $objet->getArtworkUrl();
                }
            } else {
                $album = new Media_Model_Gallery_Music_Album();
                $artwork_album = $album->getArtworkUrl();
                for($i = 0; $i < 4; $i++) {
                    $artwork_urls[] = $artwork_album;
                }
            }
        }
        return $artwork_urls;
    }

    public function getTotalTracks() {
        $total_tracks = $this->getAllTracks(true);
        return count($total_tracks);
    }

    public function getTotalDuration() {
        $total_tracks = $this->getAllTracks(true);
        $total_duration = 0;
        foreach($total_tracks as $track) {
            $total_duration += $track->getDuration();
        }

        $total_duration = floor($total_duration / 1000);
        $seconds = $total_duration % 60;
        $total_duration = floor($total_duration / 60);

        $minutes = $total_duration % 60;
        $total_duration = floor($total_duration / 60);

        $hours = $total_duration % 60;
        $total_duration = floor($total_duration / 60);

        $days = $total_duration % 24;
        $total_duration = floor($total_duration / 24);

        $return = '';

        if($days >= 1) {
            $return = $days.' '.$days == 1 ? $days.' '.$this->_('day') : $days.' '.$this->_('days');
        } elseif($hours >= 1) {
            $return = $hours.' '.$hours == 1 ? $hours.' '.$this->_('hour') : $hours.' '.$this->_('hours');
        } elseif($minutes >= 1) {
            $return = $minutes.' '.$minutes == 1 ? $minutes.' '.$this->_('minute') : $minutes.' '.$this->_('minutes');
        } else {
            $return = $seconds.' '.$seconds == 1 ? $seconds.' '.$this->_('second') : $seconds.' '.$this->_('seconds');
        }

        return $return;
    }

    public function getSoundcloudId() {
        return $this->_key->getClientId();
    }

    public function getSoundcloudSecret() {
        return $this->_key->getSecretId();
    }

    public function getNextElementsPosition() {
        $lastPosition = $this->getTable()->getLastElementsPosition();
        if(!$lastPosition) $lastPosition = 0;
        return ++$lastPosition;
    }

    public function createDummyContents($option_value, $design, $category) {

        $dummy_content_xml = $this->_getDummyXml($design, $category);

        if($dummy_content_xml->musics) {

            foreach ($dummy_content_xml->musics->children() as $content) {

                $this->unsData();
                $this->addData((array)$content->content)
                    ->setValueId($option_value->getId())
                    ->save();

                if($content->attributes()->type == "soundcloud" && $this->getSoundcloudId()) {
                    $music = new Media_Model_Gallery_Music();
                    $id = $music->getSoundcloudId();
                    $secret = $music->getSoundcloudSecret();
                    $soundcloud_api = new Media_Model_Library_Soundcloud($id, $secret);

                    foreach($content->songs->children() as $track) {
                        $track_result = $soundcloud_api->get('tracks/'.$track.'.json');
                        $track = Zend_Json::decode($track_result, Zend_Json::TYPE_OBJECT);
                        if($track->streamable == true) {
                            $new_track = new Media_Model_Gallery_Music_Track();
                            $new_track_position = $new_track->getNextTrackPosition();
                            $new_track
                                ->setGalleryId($this->getId())
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
                                ->setGalleryId($this->getId())
                                ->setTrackId($new_track->getId())
                                ->setPosition($new_element_position)
                                ->save();
                        }
                    }

                    //Tout un album
                    foreach($content->albums->children() as $playlist) {
                        $playlist_result = $soundcloud_api->get('playlists/'.$playlist.'.json');
                        $playlist = Zend_Json::decode($playlist_result, Zend_Json::TYPE_OBJECT);
                        $new_album = new Media_Model_Gallery_Music_Album();
                        $new_album
                            ->setGalleryId($this->getId())
                            ->setName($playlist->title)
                            ->setArtworkUrl($playlist->artwork_url)
                            ->setArtistName($playlist->user->username)
                            ->setType('soundcloud')
                            ->save();

                        $music_positions = new Media_Model_Gallery_Music_Elements();
                        $new_element_position = $music_positions->getNextElementsPosition();
                        $music_positions
                            ->setGalleryId($this->getId())
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

                    }
                }

//                $this->unsData();
//                $this->addData((array)$content->content)
//                    ->setValueId($option_value->getId())
//                    ->save();

//                if ($content->attributes()->type == "custom") {
//                    foreach($content->custom as $custom_images) {
//                        $custom = new Media_Model_Gallery_Music_Album();
//                        $custom->setGalleryId($this->getId())
//                            ->addData((array) $custom_images)
//                            ->save();
//                    }
//                }
            }
        }
    }

    public function copyTo($option) {

        // Duplicate the gallery
        $old_gallery_id = $this->getId();
        $this->setId(null)
            ->setValueId($option->getId())
            ->save()
        ;

        // Retrieve the albums
        $album = new Media_Model_Gallery_Music_Album();
        $albums = $album->findAll(array('gallery_id' => $old_gallery_id));

        foreach($albums as $album) {

            // Duplicate the album
            $old_album_id = $album->getId();

            $album->setId(null)
                ->setGalleryId($this->getId())
                ->save()
            ;

            // Retrieve the elements for this gallery of this album
            $element = new Media_Model_Gallery_Music_Elements();
            $elements = $element->findAll(array('gallery_id' => $old_gallery_id, 'album_id' => $old_album_id, new Zend_Db_Expr('track_id IS NULL')));

            foreach($elements as $element) {
                // Duplicate the elements of this gallery & this album
                $element->setId(null)
                    ->setGalleryId($this->getId())
                    ->setAlbumId($album->getId())
                    ->save()
                ;
            }

            // Retrieve the tracks
            $track = new Media_Model_Gallery_Music_Track();
            $tracks = $track->findAll(array('album_id' => $old_album_id));

            foreach($tracks as $track) {

                // Duplicate the track
                $old_track_id = $track->getId();
                $track->setId(null)
                    ->setAlbumId($album->getId())
                    ->setGalleryId($this->getId())
                    ->save()
                ;

                // Retrieve the elements for this gallery of this album of this track
                $element = new Media_Model_Gallery_Music_Elements();
                $elements = $element->findAll(array('gallery_id' => $old_gallery_id, 'album_id' => $old_album_id, 'track_id' => $old_track_id));

                foreach($elements as $element) {
                    // Duplicate the elements for this gallery of this album of this track
                    $element->setId(null)
                        ->setGalleryId($this->getId())
                        ->setAlbumId($album->getId())
                        ->setTrackId($track->getId())
                        ->save()
                    ;
                }

            }

        }

        return $this;

    }

    public function getFeaturePaths($option_value) {
        if(!$this->isCacheable()) return array();

        $action_view = $this->getActionView();

        $paths = array();
        $value_id = $option_value->getId();

        // Playlists paths
        $findall_path = $option_value->getMobileViewUri("findall");
        $paths[] = $this->getPath($findall_path, array('value_id' => $value_id), false);

        // Albums paths
        $paths[] = $this->getPath("media/mobile_api_music_album/findall/", array('value_id' => $value_id), false);

        $playlists = new Media_Model_Gallery_Music();
        $playlists = $playlists->findAll(array('value_id' => $value_id), 'position ASC');

        foreach($playlists as $playlist) {
            // Albums/Playlists paths
            $params = array(
                "value_id" => $value_id,
                "playlist_id" => $playlist->getId()
            );
            $paths[] = $this->getPath("media/mobile_api_music_album/findbyplaylist/", $params, false);

            // Playlists paths
            $playlist_path = $option_value->getMobileViewUri($action_view);
            $params = array(
                "value_id" => $value_id,
                "playlist_id" => $playlist->getId()
            );
            $paths[] = $this->getPath($playlist_path, $params, false);

            $elements = new Media_Model_Gallery_Music_Elements();
            $elements = $elements->findAll(array('gallery_id' => $playlist->getId()), 'position ASC');

            foreach($elements as $element) {

                if($element->getAlbumId()) {

                    $album = new Media_Model_Gallery_Music_Album();
                    $album->find($element->getAlbumId());

                    // Albums paths
                    $params = array(
                        "value_id" => $value_id,
                        "album_id" => $album->getId()
                    );
                    $paths[] = $this->getPath("media/mobile_api_music_album/find/", $params, false);
                    $paths[] = $this->getPath("media/mobile_api_music_track/findbyalbum/", $params, false);

                } else if($element->getTrackId()) {

                    $track = new Media_Model_Gallery_Music_Track();
                    $track->find($element->getTrackId());

                    // Tracks paths
                    $params = array(
                        "value_id" => $value_id,
                        "track_id" => $track->getId()
                    );
                    $paths[] = $this->getPath("media/mobile_api_music_album/find/", $params, false);
                    $paths[] = $this->getPath("media/mobile_api_music_track/findbyalbum/", $params, false);
                }

            }

        }

        return $paths;
    }

    public function getAssetsPaths($option_value) {
        if(!$this->isCacheable()) return array();

        $action_view = $this->getActionView();

        $paths = array();
        $value_id = $option_value->getId();

        $playlists = new Media_Model_Gallery_Music();
        $playlists = $playlists->findAll(array('value_id' => $value_id), 'position ASC');

        foreach($playlists as $playlist) {
            // Artwork URLs paths
            if($artworkUrl = $playlist->getArtworkUrl()) {
                $paths[] = Application_Model_Application::getImagePath() . $artworkUrl;
            }

            $elements = new Media_Model_Gallery_Music_Elements();
            $elements = $elements->findAll(array('gallery_id' => $playlist->getId()), 'position ASC');
            foreach($elements as $element) {

                if($element->getAlbumId()) {

                    $album = new Media_Model_Gallery_Music_Album();
                    $album->find($element->getAlbumId());

                    if(stripos($album->getArtworkUrl(), "http") !== false) {
                        $paths[] = $album->getArtworkUrl();
                    }

                    // Albums paths
                    $params = array(
                        "value_id" => $value_id,
                        "album_id" => $album->getId()
                    );
                } else if($element->getTrackId()) {

                    $track = new Media_Model_Gallery_Music_Track();
                    $track->find($element->getTrackId());

                    if(stripos($track->getArtworkUrl(), "http") !== false) {
                        $paths[] = $track->getArtworkUrl();
                    }

                    // Tracks paths
                    $params = array(
                        "value_id" => $value_id,
                        "track_id" => $track->getId()
                    );
                }
            }
        }

        // Default artwork URL path
        $paths[] = Media_Model_Library_Image::getImagePathTo("/musics/default_album.jpg");

        return $paths;
    }

}
