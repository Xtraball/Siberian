<?php

namespace Fanwall\Model;

use Core\Model\Base;

/**
 * Class Approval
 * @package Fanwall\Model
 *
 * @method Db\Table\Approval getTable()
 *
 */
class Approval extends Base
{
    /**
     * @var string
     */
    protected $_db_table = 'Fanwall\Model\Db\Table\Approval';

    /**
     * @param $valueId
     */
    public function findAllWithPost($valueId)
    {
        return $this->getTable()->findAllWithPost($valueId);
    }
}