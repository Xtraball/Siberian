<?php

class Tour_Model_Step extends Core_Model_Default
{

    public function __construct($params = array())
    {
        parent::__construct($params);
        $this->_db_table = 'Tour_Model_Db_Table_Step';
        return $this;
    }

    public function findAllForJS($language_code, $url) {
        $steps = $this->getTable()->findAllForJS($language_code, $url);
        $stepsJS = array();
        foreach($steps as $step) {
            $stepsJS[$step->getElementId()] = array(
                "language_code" => $step->getLanguageCode(),
                "title" => $step->getTitle(),
                "text" => $step->getText(),
                "placement" => $step->getPlacement(),
                "order_index" => $step->getOrderIndex(),
                "elem_id" => $step->getElementId()
            );
        }
        return $stepsJS;
    }
}