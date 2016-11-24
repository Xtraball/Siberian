<?php

class Application_Model_Option_Value_Metadata extends Core_Model_Default
{

    protected $_object;
    protected $method_lookup = array(
        'string' => 'idempotent',
        'float' => 'getFloatVal',
        'boolean' => 'getBooleanVal'
    );

    /**
     * Application_Model_Option_Value_Metadata constructor.
     * @param array $params
     */
    public function __construct($params = array())
    {
        parent::__construct($params);
        $this->_db_table = 'Application_Model_Db_Table_Option_Value_Metadata';
        return $this;
    }

    /**
     * Return the string value of the payload as it is
     *
     * @return string
     */
    protected function idempotent()
    {
        if (array_key_exists('payload', $this->_data)) {
            return $this->_data['payload'];
        }
        return '';
    }

    /**
     * Casts the payload into a float
     *
     * @return float
     */
    protected function getFloatVal()
    {
        if (array_key_exists('payload', $this->_data)) {
            return floatval($this->_data['payload']);
        }
        return 0.0;
    }

    /**
     * Casts the payload into a boolean
     *
     * @return bool
     */
    protected function getBooleanVal()
    {
        if (array_key_exists('payload', $this->_data)) {
            return $this->_data['payload'] == 'true';
        }
        return false;
    }

    /**
     * First casts the payload into a value corresponding to the current metadata type
     *
     * @return *
     */
    public function getPayload()
    {
        if(array_key_exists($this->getType(), $this->method_lookup)){
            $method_name = $this->method_lookup[$this->getType()];
            return $this->$method_name();
        }else{
            return "";
        }
    }

    /**
     * Remove a metadatum by key
     *
     * @param $value_id
     * @param $code
     */
    public static function deleteByCode($value_id, $code){
        $metadatum = new Application_Model_Option_Value_Metadata();
        $metadatum->find(array('code' => $code, 'value_id' => $value_id))->delete();
    }
}