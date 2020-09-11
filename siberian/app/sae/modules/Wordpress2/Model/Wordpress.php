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
     * @param $valueId
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
}
