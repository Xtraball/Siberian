<?php

class Media_Model_Gallery_Image_Instagram extends Media_Model_Gallery_Image_Abstract {

    protected $_endpointUrl = "https://api.instagram.com/v1/users/self/media/recent?client_id=%client_id%&access_token=%token%";

    protected $_userId;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Media_Model_Db_Table_Gallery_Image_Instagram';

        $this->constructUrl();

        return $this;
    }

    public function getUserId() {

        // Requête
        $request = file_get_contents($this->_endpointUrl);
        $userInfos = json_decode($request);

        // Retour l'user id s'il existe
        if (isset($userInfos->data[0]->user->id)) {
            $this->_setUserId($userInfos->data[0]->user->id);
            return $userInfos->data[0]->user->id;
        } else {
            return false;
        }
    }

    public function getImages($offset, $limit = self::DISPLAYED_PER_PAGE) {

        if($offset == 1) $offset = null;

        if (!$this->_images) {

            $this->_images = array();

            $url = $this->_endpointUrl;

            if ($offset) {
                $url .= '&max_id=' . $offset;
            }

            $requestMedia = @file_get_contents($url);
            if(!$requestMedia) return array();
            $userMedias = json_decode($requestMedia);

            foreach ($userMedias->data as $media) {

                $this->_images[] = new Core_Model_Default(array(
                    'offset' => $media->id,
                    'description' => $media->caption->text,
                    'title' => null,
                    'author' => $this->getParamInstagram(),
                    'thumbnail' => $media->images->thumbnail->url,
                    'image' => $media->images->standard_resolution->url
                ));
            }
        }
        return $this->_images;
    }

    public function _setUserId($userId) {
        $this->_userId = $userId;
    }

    public function _getUserId() {
        return $this->_userId;
    }

    public function constructUrl() {

        $client_id = $this->getApplication()->getInstagramClientId();
        $token = $this->getApplication()->getInstagramToken();

        $this->_endpointUrl = str_replace('%token%', $token, $this->_endpointUrl);
        $this->_endpointUrl = str_replace('%client_id%', $client_id, $this->_endpointUrl);

        return $this;
    }
}

