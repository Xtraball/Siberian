<?php

class Customer_Model_Db_Table_Customer extends Core_Model_Db_Table
{
    protected $_name = "customer";
    protected $_primary = "customer_id";

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

}