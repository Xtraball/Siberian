<?php

class Media_Model_Gallery_Image_Instagram extends Media_Model_Gallery_Image_Abstract {

    protected $_endpointUrls = array(
        'userSearch' => 'https://api.instagram.com/v1/users/search?q=%s&client_id=%client_id%&access_token=%token%',
        'mediaSearch' => 'https://api.instagram.com/v1/users/%s/media/recent?client_id=%client_id%&access_token=%token%'
    );
    protected $_userId;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Media_Model_Db_Table_Gallery_Image_Instagram';

        $this->constructUrl();

        return $this;
    }

    public function getUserId($username) {

        // Requête
        if (!empty($username)) {
            $request = file_get_contents(str_replace('%s', $username, $this->_endpointUrls['userSearch']));
            $userInfos = json_decode($request);

            // Retour l'user id s'il existe
            if (isset($userInfos->data[0]->id)) {
                $this->_setUserId($userInfos->data[0]->id);
                return $userInfos->data[0]->id;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getImages($offset) {

        if($offset == 1) $offset = null;

        if (!$this->_images) {

            $this->_images = array();

            $this->_userId = $this->getUserId($this->getParamInstagram());
            $url = str_replace('%s', $this->_userId, $this->_endpointUrls['mediaSearch']);
            if ($offset) {
                $url .= '&max_id=' . $offset;
            }

            $requestMedia = file_get_contents($url);
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

        $client_id = Api_Model_Key::findKeysFor('instagram')->getClientId();
        $token = Api_Model_Key::findKeysFor('instagram')->getToken();

        $this->_endpointUrls = array(
            'userSearch' => str_replace('%client_id%', $client_id, str_replace('%token%', $token, $this->_endpointUrls['userSearch'])),
            'mediaSearch' => str_replace('%client_id%', $client_id, str_replace('%token%', $token, $this->_endpointUrls['mediaSearch']))
        );

        return $this;
    }
}

