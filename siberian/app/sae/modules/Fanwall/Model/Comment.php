<?php

namespace Fanwall\Model;

use Core\Model\Base;
use Customer_Model_Customer as Customer;
use Siberian\Xss;
use Zend_Date as Date;

/**
 * Class Answer
 * @package Fanwall\Model
 *
 * @method Db\Table\Comment getTable()
 *
 * @method integer getId()
 * @method $this setReportReasons(string $jsonReasons)
 * @method $this setReportToken(string $token)
 * @method $this setIsReported(boolean $isReported)
 * @method $this setIsVisible(boolean $isVisible)
 * @method string getReportReasons()
 * @method string getReportToken()
 */
class Comment extends Base
{

    /**
     * @var
     */
    protected $_customer;
    /**
     * @var
     */
    protected $_comment;

    /**
     * Answer constructor.
     * @param array $datas
     * @throws \Zend_Exception
     */
    public function __construct($datas = [])
    {
        parent::__construct($datas);
        $this->_db_table = 'Fanwall\Model\Db\Table\Comment';
    }

    /**
     * @param $postId
     * @return Comment[]
     * @throws \Zend_Exception
     */
    public function findForPostId($postId)
    {
        return $this->getTable()->findForPostId($postId);
    }

    /**
     * @return array|string
     * @throws \Zend_Date_Exception
     * @throws \Zend_Exception
     * @throws \Zend_Locale_Exception
     */
    public function forJson()
    {
        return [
            "id" => (integer) $this->getId(),
            "customerId" => (integer) $this->getCustomerId(),
            "text" => (string) Xss::sanitize($this->getText()),
            "isFlagged" => (boolean) $this->getFlag(),
            "date" => datetime_to_format($this->getCreatedAt(), Date::TIMESTAMP),
            "image" => (string) $this->getPicture(),
            "author" => [
                "firstname" => (string) $this->getFirstname(),
                "lastname" => (string) $this->getLastname(),
                "nickname" => (string) $this->getnickname(),
                "image" => (string) $this->getAuthorImage(),
            ],
        ];
    }
}