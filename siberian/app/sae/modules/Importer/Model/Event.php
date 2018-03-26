<?php

/**
 * Class Importer_Model_Event
 */
class Importer_Model_Event extends Importer_Model_Importer_Abstract
{
    /**
     * Importer_Model_Event constructor.
     * @param array $params
     */
    public function __construct($params = []) {
        parent::__construct($params);

    }

    /**
     * @param $data
     * @param null $appId
     * @return bool
     */
    public function importFromFacebook($data, $appId = null)
    {
        try {
            (new Event_Model_Event())
                ->addData($data)
                ->save();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

}
