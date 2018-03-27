<?php

/**
 * Class Importer_Model_Importer_Abstract
 */
abstract class Importer_Model_Importer_Abstract extends Core_Model_Default
{

    /**
     * Importer_Model_Importer_Abstract constructor.
     * @param array $datas
     */
    public function __construct($datas = []) {
        parent::__construct($datas);
    }

    /**
     * @param $appId
     * @param $code
     * @return bool|int
     */
    public function createOption($appId, $code) {
        try {
            $option = new Application_Model_Option();
            $option->find(
                [
                    'code' => $code
                ]
            );
            if (!$option->getId()) {
                return false;
            }

            $optionValue = new Application_Model_Option_Value();

            // Adds data!
            $optionValue->addData([
                'app_id' => $appId,
                'option_id' => $option->getId(),
                'position' => 0,
                'is_visible' => 1
            ]);

            $optionValue->setIconId($option->getDefaultIconId());

            $optionValue->save();
            $id = $optionValue->getId();
            $optionValue = new Application_Model_Option_Value();
            $optionValue->find($id);

            $optionValue->getObject()->prepareFeature($optionValue);

            return $optionValue->getValueId();
        } catch (Exception $e) {
            return false;
        }
    }

}