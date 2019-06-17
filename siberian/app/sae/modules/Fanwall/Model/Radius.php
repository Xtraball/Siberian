<?php

namespace Fanwall\Model;

use Core\Model\Base;

/**
 * Class Radius
 * @package Fanwall\Model
 */
class Radius extends Base
{
    /**
     * Radius constructor.
     * @param array $params
     * @throws \Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = "Fanwall\Model\Db\Table\Radius";
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
