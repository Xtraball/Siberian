<?php

/**
 * Class Codescan_Model_Codescan
 */
class Codescan_Model_Codescan extends Core_Model_Default
{
    /**
     * @var bool
     */
    protected $_is_cacheable = true;

    /**
     * @param $valueId
     * @return array
     */
    public function getInappStates($valueId): array
    {
        return [
            [
                'state' => 'codescan',
                'offline' => false,
                'params' => [
                    'value_id' => $valueId,
                ],
            ],
        ];
    }

}
