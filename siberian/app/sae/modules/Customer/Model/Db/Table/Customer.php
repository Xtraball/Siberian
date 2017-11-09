<?php

class Customer_Model_Db_Table_Customer extends Core_Model_Db_Table
{
    protected $_name = "customer";
    protected $_primary = "customer_id";

    /**
     * @param $app_id
     * @param int $limit
     * @return array
     */
    public function findByAppId($app_id, $limit = 5000) {
        $select = $this->_db->select()
            ->from($this->_name, array(
                "customer_id",
                "firstname",
                "lastname",
                "email",
                "registration_date" => "created_at",
                "registration_timestamp" => new Zend_Db_Expr("UNIX_TIMESTAMP(created_at)"),
            ))
            ->where("is_active = ?", true)
            ->where("app_id = ?", $app_id)
            ->limit($limit)
        ;

        return $this->_db->fetchAssoc($select);
    }

    public function findAllCustomersByApp($values, $params) {

        $where = "";
        $subrequest = "(SELECT COUNT(customer_id) as nb_customers FROM customer WHERE app_id = ".$values["app_id"].") as nb_customer";
        if($values AND is_array($values)) {
            $where = " WHERE ";
            $start = true;
            foreach($values as $quote => $value) {
                if(!$start) {
                    $where .= " AND";
                }
                if($quote != "search") {
                    $where .= " ".$quote." = ".$value." ";
                } else {
                    if($value <> "") {
                        $where .= $value." ";
                        $subrequest = "(SELECT COUNT(customer_id) as nb_customers FROM customer WHERE app_id = ".$values["app_id"]." AND ".$value.") as nb_customer";
                    }
                }

                $start = false;
            }
        }

        $select = "
              SELECT
              customer_id,
              email,
              firstname,
              lastname,
              created_at,".$subrequest."
              FROM customer".$where." ORDER BY customer_id ";

        $params_string = "";
        if($params AND is_array($params)) {
            foreach($params as $quote => $value) {
                $params_string .= $quote." ".$value." ";
            }
        }

        $select .= $params_string;

        return $this->toModelClass($this->_db->fetchAll($select));
    }

    public function findSocialDatas($customer_id) {

        $social_datas = array();

        $select = $this->_db->select()
            ->from("customer_social", array("type", "social_id", "datas"))
            ->where("customer_id = ?", $customer_id)
        ;

        $datas = $this->_db->fetchAll($select);
        foreach($datas as $data) {
            $social_datas[$data["type"]] = array("social_id" => $data["social_id"], "datas" => $data["datas"]);
        }

        return $social_datas;
    }

    public function insertSocialDatas($customer_id, $datas, $app_id) {

        $this->_db->beginTransaction();
        $table = 'customer_social';
        try {
//            $this->_db->delete($table, array('customer_id = ?' => $customer_id));
            foreach($datas as $data) {
                $data['customer_id'] = $customer_id;

                if($this->findBySocialId($data['social_id'], $data['type'], $app_id)) {
                    $this->_db->update($table, array('datas' => $data['datas']), array('social_id = ?' => $data['social_id'], 'type = ? ' => $data['type']));
                }
                else {
                    $r = $this->_db->insert($table, $data);
                }
            }
            $this->_db->commit();
        }
        catch(Exception $e) {
            $this->_db->rollBack();
        }

        return $this;

    }

    public function findBySocialId($id, $type, $app_id) {

        $select = $this->_db->select()
            ->from($this->_name)
            ->join('customer_social', "customer_social.customer_id = {$this->_name}.customer_id", array())
            ->where('customer_social.social_id = ?', $id)
            ->where('customer_social.type = ?', $type)
            ->where('customer.app_id = ?', $app_id)
        ;

        return $this->_db->fetchRow($select);
    }

    public function addSocialPost($customer_id, $customer_message, $message_type, $points) {

        $table = 'customer_social_post';

        $datas = array(
            'customer_id' => $customer_id,
            'customer_message' => $customer_message,
            'message_type' => $message_type
        );

        if(!empty($points)) {

            $datas['points'] = $points;

            $where = $this->_db->quoteInto('customer_id = ?', $customer_id);
            $current_points = $this->_db->fetchOne($this->_db->select()->from($table, array('points'))->where($where));

            if($current_points AND empty($customer_message)) {
                $datas['points'] += $current_points;
                $this->_db->update($table, array('points' => $datas['points']), array('customer_id = ?' => $customer_id));
            }
            else {
                $this->_db->insert($table, $datas);
            }

        }
        else {
            $this->_db->insert($table, $datas);
        }


        return $this;
    }

    public function deleteSocialPost($customer_id, $post_id) {
        $this->_db->delete('customer_social_post', array('customer_id = ?' => $customer_id, 'id = ?' => $post_id));
        return $this;
    }

    public function findAllPosts() {
        return $this->_db->fetchAll($this->_db->select()->from('customer_social_post'));
    }

    public function findAllWithDeviceUid($app_id) {
        $select = $this->_db->select()
            ->from(array('c' => $this->_name))
            ->joinLeft(array('pad' => 'push_apns_devices'), "c.customer_id = pad.customer_id", array("device_uid"))
            ->joinLeft(array('pgd' => 'push_gcm_devices'), "c.customer_id = pgd.customer_id", array("registration_id"))
        ;

        if($app_id) {
            $select->where('c.app_id = ?', $app_id);
        }

        return $this->_db->fetchAll($select);
    }


    public function getAppIdByCustomerId() {
        $select = $this->select()
            ->from($this->_name, array('customer_id','app_id'));
        return $this->_db->fetchAssoc($select);
    }


    public function findMetadatas($customer_id, $module_code = null) {
        $metadatas = array();

        $select = $this->_db->select()
            ->from("customer_metadata", array("code", "datas"))
            ->where("customer_id = ?", $customer_id)
        ;

        if(!is_null($module_code)) {
            $select = $select->where("code = ?", $module_code);
            return $this->_db->fetchRow($select);
        }

        $datas = $this->_db->fetchAll($select);
        foreach($datas as $data) {
            $metadatas[$data["code"]] = unserialize($data["datas"]);
        }

        return $metadatas;
    }

    public function insertMetadatas($customer_id, $datas) {

        $this->_db->beginTransaction();
        $table = 'customer_metadata';
        try {
//            $this->_db->delete($table, array('customer_id = ?' => $customer_id));
            foreach($datas as $data) {
                $data['customer_id'] = $customer_id;

                if($this->findMetadatas($customer_id, $data['code'])) {
                    $this->_db->update($table, array('datas' => $data['datas']), array('code = ?' => $data['code'], 'customer_id = ? ' => $data['customer_id']));
                }
                else {
                    $r = $this->_db->insert($table, $data);
                }
            }
            $this->_db->commit();
        }
        catch(Exception $e) {
            die(var_dump($e, $customer_id, $datas));
            exit();
            $this->_db->rollBack();
        }

        return $this;

    }

}
