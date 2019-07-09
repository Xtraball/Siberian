<?php

/**
 * Class Customer_Model_Db_Table_Customer
 */
class Customer_Model_Db_Table_Customer extends Core_Model_Db_Table
{
    /**
     * @var string
     */
    protected $_name = "customer";
    /**
     * @var string
     */
    protected $_primary = "customer_id";

    /**
     * @param $app_id
     * @param int $limit
     * @return array
     * @throws Zend_Exception
     */
    public function findByAppId($app_id, $limit = 5000)
    {
        if (Push_Model_Message::hasIndividualPush()) {
            $select = $this->_db->select()
                ->from($this->_name, [
                    "customer_id",
                    "firstname",
                    "lastname",
                    "email",
                    "registration_date" => "customer.created_at",
                    "registration_timestamp" => new Zend_Db_Expr("UNIX_TIMESTAMP(customer.created_at)"),
                    "has_push" => new Zend_Db_Expr("IF(push_gcm_devices.device_id IS NOT NULL, 1, IF(push_apns_devices.device_id IS NOT NULL, 1, 0))")
                ])
                ->joinLeft("push_gcm_devices", "push_gcm_devices.customer_id = customer.customer_id", [])
                ->joinLeft("push_apns_devices", "push_apns_devices.customer_id = customer.customer_id", [])
                ->where("customer.is_active = ?", true)
                ->where("customer.app_id = ?", $app_id)
                ->limit($limit);
        } else {
            $select = $this->_db->select()
                ->from($this->_name, [
                    "customer_id",
                    "firstname",
                    "lastname",
                    "email",
                    "registration_date" => "created_at",
                    "registration_timestamp" => new Zend_Db_Expr("UNIX_TIMESTAMP(created_at)"),
                    "has_push" => new Zend_Db_Expr("0"),
                ])
                ->where("is_active = ?", true)
                ->where("app_id = ?", $app_id)
                ->limit($limit);
        }

        return $this->_db->fetchAssoc($select);
    }

    /**
     * @param $values
     * @param $params
     * @return mixed
     */
    public function findAllCustomersByApp($values, $params)
    {

        $where = "";
        $subrequest = "(SELECT COUNT(customer_id) as nb_customers FROM customer WHERE app_id = " . $values["app_id"] . ") as nb_customer";
        if ($values AND is_array($values)) {
            $where = " WHERE ";
            $start = true;
            foreach ($values as $quote => $value) {
                if (!$start) {
                    $where .= " AND";
                }
                if ($quote != "search") {
                    $where .= " " . $quote . " = " . $value . " ";
                } else {
                    if ($value <> "") {
                        $where .= $value . " ";
                        $subrequest = "(SELECT COUNT(customer_id) as nb_customers FROM customer WHERE app_id = " . $values["app_id"] . " AND " . $value . ") as nb_customer";
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
              created_at," . $subrequest . "
              FROM customer" . $where . " ORDER BY customer_id ";

        $params_string = "";
        if ($params AND is_array($params)) {
            foreach ($params as $quote => $value) {
                $params_string .= $quote . " " . $value . " ";
            }
        }

        $select .= $params_string;

        return $this->toModelClass($this->_db->fetchAll($select));
    }

    /**
     * @param $appId
     * @param array $params
     * @return Customer_Model_Customer[]
     */
    public function findAllForApp($appId, $params = [])
    {
        $select = $this
            ->select()
            ->from(
                $this->_name,
                [
                    'customer_id',
                    'email',
                    'civility',
                    'firstname',
                    'lastname',
                    'nickname',
                    'is_active',
                    'created_at',
                ]
            )
            ->where('app_id = ?', $appId);

        if (array_key_exists("sorts", $params) && !empty($params["sorts"])) {
            $orders = [];
            foreach ($params["sorts"] as $key => $dir) {
                $order = ($dir == -1) ? "DESC" : "ASC";
                $orders = "{$key} {$order}";
            }
            $select->order($orders);
        } else {
            $select->order('customer_id DESC');
        }

        if (array_key_exists("limit", $params) && array_key_exists("offset", $params)) {
            $select->limit($params["limit"], $params["offset"]);
        }

        if (array_key_exists("filter", $params)) {
            $select->where("(customer_id LIKE ? OR firstname LIKE ? OR lastname LIKE ? OR nickname LIKE ? OR email LIKE ? OR created_at LIKE ?)", "%" . $params["filter"] . "%");
        }

        return $this->toModelClass($this->_db->fetchAll($select));
    }

    /**
     * @param $appId
     */
    public function countAllForApp($appId, $params = [])
    {
        $select = $this
            ->select()
            ->from(
                $this->_name,
                [
                    'COUNT(customer_id)',
                ]
            )
            ->where('app_id = ?', $appId);

        if (array_key_exists("filter", $params)) {
            $select->where("(customer_id LIKE ? OR firstname LIKE ? OR lastname LIKE ? OR nickname LIKE ? OR email LIKE ? OR created_at LIKE ?)", "%" . $params["filter"] . "%");
        }

        return $this->_db->fetchCol($select);
    }

    /**
     * @param $customer_id
     * @return array
     */
    public function findSocialDatas($customer_id)
    {

        $social_datas = [];

        $select = $this->_db->select()
            ->from("customer_social", ["type", "social_id", "datas"])
            ->where("customer_id = ?", $customer_id);

        $datas = $this->_db->fetchAll($select);
        foreach ($datas as $data) {
            $social_datas[$data["type"]] = ["social_id" => $data["social_id"], "datas" => $data["datas"]];
        }

        return $social_datas;
    }

    /**
     * @param $customer_id
     * @param $datas
     * @param $app_id
     * @return $this
     */
    public function insertSocialDatas($customer_id, $datas, $app_id)
    {

        $this->_db->beginTransaction();
        $table = 'customer_social';
        try {
//            $this->_db->delete($table, array('customer_id = ?' => $customer_id));
            foreach ($datas as $data) {
                $data['customer_id'] = $customer_id;

                if ($this->findBySocialId($data['social_id'], $data['type'], $app_id)) {
                    $this->_db->update($table, ['datas' => $data['datas']], ['social_id = ?' => $data['social_id'], 'type = ? ' => $data['type']]);
                } else {
                    $r = $this->_db->insert($table, $data);
                }
            }
            $this->_db->commit();
        } catch (Exception $e) {
            $this->_db->rollBack();
        }

        return $this;

    }

    /**
     * @param $id
     * @param $type
     * @param $app_id
     * @return mixed
     */
    public function findBySocialId($id, $type, $app_id)
    {

        $select = $this->_db->select()
            ->from($this->_name)
            ->join('customer_social', "customer_social.customer_id = {$this->_name}.customer_id", [])
            ->where('customer_social.social_id = ?', $id)
            ->where('customer_social.type = ?', $type)
            ->where('customer.app_id = ?', $app_id);

        return $this->_db->fetchRow($select);
    }

    /**
     * @param $customer_id
     * @param $customer_message
     * @param $message_type
     * @param $points
     * @return $this
     * @throws Zend_Db_Adapter_Exception
     */
    public function addSocialPost($customer_id, $customer_message, $message_type, $points)
    {

        $table = 'customer_social_post';

        $datas = [
            'customer_id' => $customer_id,
            'customer_message' => $customer_message,
            'message_type' => $message_type
        ];

        if (!empty($points)) {

            $datas['points'] = $points;

            $where = $this->_db->quoteInto('customer_id = ?', $customer_id);
            $current_points = $this->_db->fetchOne($this->_db->select()->from($table, ['points'])->where($where));

            if ($current_points AND empty($customer_message)) {
                $datas['points'] += $current_points;
                $this->_db->update($table, ['points' => $datas['points']], ['customer_id = ?' => $customer_id]);
            } else {
                $this->_db->insert($table, $datas);
            }

        } else {
            $this->_db->insert($table, $datas);
        }


        return $this;
    }

    /**
     * @param $customer_id
     * @param $post_id
     * @return $this
     */
    public function deleteSocialPost($customer_id, $post_id)
    {
        $this->_db->delete('customer_social_post', ['customer_id = ?' => $customer_id, 'id = ?' => $post_id]);
        return $this;
    }

    /**
     * @return array
     */
    public function findAllPosts()
    {
        return $this->_db->fetchAll($this->_db->select()->from('customer_social_post'));
    }

    /**
     * @param $app_id
     * @return array
     */
    public function findAllWithDeviceUid($app_id)
    {
        $select = $this->_db->select()
            ->from(['c' => $this->_name])
            ->joinLeft(['pad' => 'push_apns_devices'], "c.customer_id = pad.customer_id", ["device_uid"])
            ->joinLeft(['pgd' => 'push_gcm_devices'], "c.customer_id = pgd.customer_id", ["registration_id"]);

        if ($app_id) {
            $select->where('c.app_id = ?', $app_id);
        }

        return $this->_db->fetchAll($select);
    }


    /**
     * @return array
     */
    public function getAppIdByCustomerId()
    {
        $select = $this->select()
            ->from($this->_name, ['customer_id', 'app_id']);
        return $this->_db->fetchAssoc($select);
    }


    /**
     * @param $customer_id
     * @param null $module_code
     * @return array|mixed
     */
    public function findMetadatas($customer_id, $module_code = null)
    {
        $metadatas = [];

        $select = $this->_db->select()
            ->from("customer_metadata", ["code", "datas"])
            ->where("customer_id = ?", $customer_id);

        if (!is_null($module_code)) {
            $select = $select->where("code = ?", $module_code);
            return $this->_db->fetchRow($select);
        }

        $datas = $this->_db->fetchAll($select);
        foreach ($datas as $data) {
            $metadatas[$data["code"]] = unserialize($data["datas"]);
        }

        return $metadatas;
    }

    /**
     * @param $customer_id
     * @param $datas
     * @return $this
     */
    public function insertMetadatas($customer_id, $datas)
    {

        $this->_db->beginTransaction();
        $table = 'customer_metadata';
        try {
            foreach ($datas as $data) {
                $data['customer_id'] = $customer_id;

                if ($this->findMetadatas($customer_id, $data['code'])) {
                    $this->_db->update($table, ['datas' => $data['datas']], ['code = ?' => $data['code'], 'customer_id = ? ' => $data['customer_id']]);
                } else {
                    $r = $this->_db->insert($table, $data);
                }
            }
            $this->_db->commit();
        } catch (Exception $e) {
            die(var_dump($e, $customer_id, $datas));
            exit();
            $this->_db->rollBack();
        }

        return $this;

    }

}
