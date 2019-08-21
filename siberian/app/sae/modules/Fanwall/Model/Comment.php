<?php

namespace Fanwall\Model;

use Core\Model\Base;
use Customer_Model_Customer as Customer;
use Siberian\Json;
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
     * @return array
     * @throws \Zend_Exception
     */
    public function getHistoryJson ()
    {
        try {
            $history = Json::decode($this->getHistory());
        } catch (\Exception $e) {
            $history = [];
        }

        $parsedHistory = [];
        foreach ($history as $item) {
            $item["text"] = (string) Xss::sanitize(base64_decode($item["text"]));

            $parsedHistory[] = $item;
        }

        return $parsedHistory;
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
            "postId" => (integer) $this->getPostId(),
            "text" => (string) Xss::sanitize(base64_decode($this->getText())),
            "isFlagged" => (boolean) $this->getFlag(),
            "isBlocked" => (boolean) false,
            "date" => (integer) $this->getDate(),
            "image" => (string) $this->getPicture(),
            "history" => $this->getHistoryJson(),
            "author" => [
                "firstname" => (string) $this->getFirstname(),
                "lastname" => (string) $this->getLastname(),
                "nickname" => (string) $this->getnickname(),
                "image" => (string) $this->getAuthorImage(),
            ],
        ];
    }
}