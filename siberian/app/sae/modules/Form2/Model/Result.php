<?php

namespace Form2\Model;
use Core\Model\Base;
use Siberian\Json;

/**
 * Class Result
 * @package Form2\Model
 *
 * @method Db\Table\Result getTable()
 */
class Result extends Base
{
    protected $_db_table = Db\Table\Result::class;

    /**
     * @param array $payload
     * @return Result
     */
    public function setPayload (array $payload)
    {
        try {
            $safePayload = base64_encode(Json::encode($payload, JSON_UNESCAPED_UNICODE));
        } catch (\Exception $e) {
            $safePayload =  base64_encode(Json::encode([]));
        }

        return $this->setData('payload', $safePayload);
    }

    /**
     * @return array
     */
    public function getPayload (): array
    {
        $dbPayload = $this->getData('payload');

        try {
            $clearPayload = Json::decode(base64_decode($dbPayload));
        } catch (\Exception $e) {
            $clearPayload =  [];
        }

        return $clearPayload;
    }

    /**
     * @param $valueId
     * @param $excludeAnonymous
     * @param $lastUserRecord
     * @return mixed
     * @throws \Zend_Exception
     */
    public function fetchForCsv ($valueId, $excludeAnonymous, $lastUserRecord)
    {
        return $this->getTable()->fetchForCsv($valueId, $excludeAnonymous, $lastUserRecord);
    }
}
