<?php

/**
 * Class Weblink_Model_Type_Mono
 */
class Weblink_Model_Type_Mono extends Weblink_Model_Weblink
{

    /**
     * Weblink_Model_Type_Mono constructor.
     * @param array $params
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_type_id = 1;
    }

    /**
     * @return |null
     */
    public function getLink()
    {
        $link = (new Weblink_Model_Weblink_Link())->find($this->getWeblinkId(), 'weblink_id');

        return $link->getId() ? $link : null;
    }
}
