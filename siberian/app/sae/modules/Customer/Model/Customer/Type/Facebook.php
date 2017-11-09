<?php
/** @deprecated only used for Angular apps */
require_once 'Connect/facebook.php';

class Customer_Model_Customer_Type_Facebook extends Customer_Model_Customer_Type_Abstract
{

    private static $__scope = 'email';
    protected $_facebook;

    public function __construct($params = array()) {
        parent::__construct($params);

        $config = array(
            'appId'     => Core_Model_Lib_Facebook::getAppId(),
            'secret'    => Core_Model_Lib_Facebook::getSecretKey(),
        );

        $this->_facebook = new Facebook($config);;

        if(!empty($params['social_datas'])) {
            $this->_facebook->setAccessToken(unserialize($params['social_datas']));
        }

        if($this->isValid()) {
            $this->loadData();
        }
        return $this;
    }

    public static function getScope() {
        return self::$__scope;
    }

    public function loadData() {

        $fields = array('pic_big', 'profile_url');
        $fields = implode(',', $fields);
        $fql = "SELECT $fields from user where uid = {$this->_id}";
        try {
            $ret_obj = $this->_facebook->api(array(
                'method' => 'fql.query',
                'query' => $fql,
            ));

            if(!empty($ret_obj[0])) {
                $this->setData($ret_obj[0]);
            }
        }
        catch(Exception $e) {
            if($e->getCode() == 190) {
                $this->_facebook->setAccessToken(null);
            }
        }

        return $this;
    }

    public function isValid() {
        return $this->_id && $this->_facebook->getAdmin() != 0;
    }

}