<?php

abstract class Importer_Model_Importer_Abstract extends Core_Model_Default
{

    public function __construct($datas = array()) {
        parent::__construct($datas);
    }

    public function createOption($app_id, $code) {
        try {
            $option = new Application_Model_Option();
            $option->find(array("code" => $code));
            if(!$option->getId()) return false;

            $option_value = new Application_Model_Option_Value();
            // Ajoute les données
            $option_value->addData(array(
                'app_id' => $app_id,
                'option_id' => $option->getId(),
                'position' => 0,
                'is_visible' => 1
            ));

            $option_value->setIconId($option->getDefaultIconId());

            $option_value->save();
            $id = $option_value->getId();
            $option_value = new Application_Model_Option_Value();
            $option_value->find($id);

            $option_value->getObject()->prepareFeature($option_value);

            return $option_value->getValueId();
        } catch(Siberian_Exception $e) {
            return false;
        }
    }


}