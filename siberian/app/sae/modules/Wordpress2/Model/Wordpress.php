<?php

/**
 * Class Wordpress2_Model_Wordpress
 */
class Wordpress2_Model_Wordpress extends Core_Model_Default
{
    /**
     * @var string
     */
    protected $_db_table = Wordpress2_Model_Db_Table_Wordpress::class;

    /**
     * @return array
     */
    public function getInappStates($valueId): array
    {
        $inAppStates = [
            [
                'state' => 'wordpress2-list',
                'offline' => false,
                'params' => [
                    'value_id' => $valueId,
                ],
            ],
        ];

        return $inAppStates;
    }

    /**
     * GET Feature url for app init
     *
     * @param $optionValue
     * @return array
     */
    public function getAppInitUris ($optionValue)
    {
        $featureUrl = __url("/wordpress2/mobile_list/index", [
            "value_id" => $this->getValueId(),
        ]);
        $featurePath = __path("/wordpress2/mobile_list/index", [
            "value_id" => $this->getValueId(),
        ]);


        return [
            "featureUrl" => $featureUrl,
            "featurePath" => $featurePath,
        ];
    }
}