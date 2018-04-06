<?php

/**
 * Class Importer_Model_FacebookParser
 */
class Importer_Model_FacebookParser extends Core_Model_Default
{
    /**
     * Importer_Model_FacebookParser constructor.
     * @param array $params
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
    }

    /**
     * @return bool
     * @throws Zend_Json_Exception
     */
    public function getToken()
    {
        $facebookAPP_ID = Api_Model_Key::findKeysFor('facebook')
            ->getAppId();
        $facebookAPP_SECRET = Api_Model_Key::findKeysFor('facebook')
            ->getSecretKey();

        if (!$facebookAPP_SECRET || !$facebookAPP_ID) {
            return false;
        } else {
            $url = self::buildUrl('oauth/access_token', [
                'grant_type' => 'client_credentials',
                'client_id' => $facebookAPP_ID,
                'client_secret' => $facebookAPP_SECRET,
            ]);
            $content = Siberian_Json::decode(Siberian_Request::get($url));

            return $content['access_token'] ? $content['access_token'] : false;
        }

    }

    /**
     * @param $page_id
     * @param $token
     * @param null $fields
     * @return array|mixed
     * @throws Siberian_Exception
     */
    public function parsePage($page_id, $token, $fields = null)
    {
        $fields = $fields ? $fields : 'id,hours,location,phone,emails,events,general_info,contact_address,description,'
            . 'current_location,about,name,genre,cover,website,link';
        $url = self::buildUrl($page_id, [
            'access_token' => $token,
            'fields' => $fields
        ]);

        $response = Siberian_Request::get($url);
        $response = Siberian_Json::decode($response);

        if (array_key_exists('error', $response)) {
            // Parsing the error
            $data = $response['error'];
            if (array_key_exists('message', $data)) {
                throw new Siberian_Exception($data['message']);
            } else {
                throw new Siberian_Exception(__('An unknown error occured with the Facebook API.<br />' . print_r($response, true)));
            }
        }

        return $response;
    }

    /**
     * @param $page_id
     * @param $token
     * @return mixed
     * @throws Zend_Json_Exception
     */
    public function parsePageAlbums($page_id, $token)
    {
        $url = self::buildUrl($page_id . '/albums', [
            'access_token' => $token
        ]);

        $response = Siberian_Request::get($url);
        $response = Siberian_Json::decode($response);

        return $response;
    }

    /**
     * @param $path
     * @param array $params
     * @return string
     */
    private static function buildUrl($path, $params = [])
    {
        return sprintf('%s/%s?%s', 'https://graph.facebook.com/v2.12', $path, http_build_query($params));
    }
}
