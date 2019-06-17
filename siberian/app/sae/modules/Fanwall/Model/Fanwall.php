<?php

namespace Fanwall\Model;

use Core\Model\Base;

/**
 * Class Fanwall
 * @package Fanwall\Model
 */
class Fanwall extends Base
{
    /**
     * Radius constructor.
     * @param array $params
     * @throws \Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = "Fanwall\Model\Db\Table\Fanwall";
        return $this;
    }

    /**
     * @param $id
     * @param null $field
     * @return $this
     */
    public function find($id, $field = null)
    {
        parent::find($id, $field);

        if (!$this->getId()) {
            $this->setRadius(10.0);
        }

        return $this;
    }

}
