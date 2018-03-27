<?php

/**
 * Class Importer_Model_SocialFacebook
 */
class Importer_Model_SocialFacebook extends Importer_Model_Importer_Abstract
{
    /**
     * Importer_Model_SocialFacebook constructor.
     * @param array $params
     */
    public function __construct($params = []) {
        parent::__construct($params);

    }

    /**
     * @param $data
     * @param null $app_id
     * @return bool
     */
    public function importFromFacebook($data, $appId = null) {
        try{
            (new Social_Model_Facebook())
                ->addData($data)
                ->save();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

}
