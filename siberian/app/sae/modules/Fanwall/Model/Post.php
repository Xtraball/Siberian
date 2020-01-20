<?php

namespace Fanwall\Model;

use Core\Model\Base;
use Siberian\Json;
use Siberian\Xss;

/**
 * Class Post
 * @package Fanwall\Model
 *
 * @method Db\Table\Post getTable()
 *
 * @method integer getId()
 * @method $this setReportReasons(string $jsonReasons)
 * @method $this setReportToken(string $token)
 * @method $this setIsReported(boolean $isReported)
 * @method $this setIsVisible(boolean $isVisible)
 * @method string getReportReasons()
 * @method string getReportToken()
 */
class Post extends Base
{

    /**
     * @var bool
     */
    protected $_is_cacheable = false;
    /**
     *
     */
    const DISPLAYED_PER_PAGE = 10;

    /**
     * @var
     */
    protected $_answers;
    /**
     * @var
     */
    protected $_likes;
    /**
     * @var
     */
    protected $_customer;

    /**
     * Fanwall constructor.
     * @param array $params
     * @throws \Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Fanwall\Model\Db\Table\Post';
        return $this;
    }

    /**
     * @param $valueId
     * @return array|bool
     */
    public function getInappStates($valueId)
    {

        $in_app_states = [
            [
                "state" => "fanwall-list",
                "offline" => true,
                "params" => [
                    "value_id" => $valueId,
                ],
            ],
        ];

        return $in_app_states;
    }

    /**
     * @return mixed
     */
    public function toggle()
    {
        $this->setIsVisible(!$this->getIsVisible())->save();

        return $this->getIsVisible();
    }

    /**
     * @return mixed
     */
    public function toggleSticky()
    {
        $this->setSticky(!$this->getSticky())->save();

        return $this->getSticky();
    }

    /**
     * @param $customerId
     * @param $headers
     * @throws \Zend_Exception
     */
    public function like ($customerId, $headers = [])
    {
        $postLike = (new Like())->find([
            "post_id" => $this->getId(),
            "customer_id" => $customerId,
        ]);

        if (!$postLike->getId()) {
            $postLike
                ->setPostId($this->getId())
                ->setCustomerId($customerId)
                ->setUserAgent($headers["user_agent"])
                ->setCustomerIp($headers["forwarded-for"] . ", " .  $headers["remote-addr"])
                ->save();
        }
    }

    /**
     * @param $customerId
     * @throws \Zend_Exception
     */
    public function unlike ($customerId)
    {
        $postLike = (new Like())->find([
            "post_id" => $this->getId(),
            "customer_id" => $customerId,
        ]);

        if ($postLike->getId()) {
            $postLike->delete();
        }
    }

    public function comment ($customerId, $message, $headers = [])
    {

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
            $item['text'] = (string) Xss::sanitize(base64_decode($item['text']));

            $_images = explode(',', $item['image']);
            $item['image'] = (string) $_images[0];
            $item['images'] = $_images;

            $parsedHistory[] = $item;
        }

        return $parsedHistory;
    }

    /**
     * @param array $values
     * @param null $order
     * @param array $params
     * @return Post[]
     * @throws \Zend_Exception
     */
    public function findAllWithCustomer($values = [], $order = null, $params = [])
    {
        return $this->getTable()->findAllWithCustomer($values, $order, $params);
    }

    /**
     * @param array $values
     * @param null $order
     * @param array $params
     * @return Post[]
     * @throws \Zend_Exception
     */
    public function findAllImages($values = [], $order = null, $params = [])
    {
        return $this->getTable()->findAllImages($values, $order, $params);
    }
}
