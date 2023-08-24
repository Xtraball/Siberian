<?php

/**
 * Class LoyaltyCard_Model_LoyaltyCard
 *
 * @method LoyaltyCard_Model_Db_Table_LoyaltyCard getTable()
 */
class LoyaltyCard_Model_LoyaltyCard extends Core_Model_Default
{

    /**
     * @var string
     */
    protected $_action_view = 'findall';

    /**
     * @var string
     */
    protected $_db_table = LoyaltyCard_Model_Db_Table_LoyaltyCard::class;

    /**
     * @param $valueId
     * @return array
     */
    public function getInappStates($valueId): array
    {
        $inAppStates = [
            [
                'state' => 'loyaltycard-view',
                'offline' => false,
                'params' => [
                    'value_id' => $valueId,
                ],
            ],
        ];

        return $inAppStates;
    }

    /**
     * @param $value_id
     * @return mixed
     */
    public function findByValueId($value_id)
    {
        return $this->getTable()->findByValueId($value_id);
    }

    /**
     * @param $value_id
     * @return mixed
     */
    public function findLast($value_id = [])
    {
        return $this->getTable()->findLast($value_id);
    }

    /**
     * @return mixed
     */
    public function getAppIdByLoyaltycardId()
    {
        return $this->getTable()->getAppIdByLoyaltycardId();
    }
}