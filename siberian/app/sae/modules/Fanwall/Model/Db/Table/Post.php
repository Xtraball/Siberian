<?php

namespace Fanwall\Model\Db\Table;

use Fanwall\Model\Post as ModelPost;
use Core_Model_Db_Table as DbTable;
use Siberian_Google_Geocoding as Geocoding;
use Zend_Db_Expr as DbExpr;

/**
 * Class Post
 * @package Fanwall\Model\Db\Table
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.18.20
 */
class Post extends DbTable
{

    /**
     * @var string
     */
    protected $_name = 'fanwall_post';
    /**
     * @var string
     */
    protected $_primary = 'post_id';

    /**
     * @param array $values
     * @param null $order
     * @param array $params
     * @return ModelPost[]
     * @throws \Zend_Exception
     */
    public function findAllWithCustomer($values = [], $order = null, $params = [])
    {
        $searchByDistance = false;
        $columns = ['*'];
        $radius = 0;
        if (array_key_exists('search_by_distance', $values) &&
            $values['search_by_distance']) {
            $formula = Geocoding::getDistanceFormula(
                $values['latitude'],
                $values['longitude'],
                'fanwall_post',
                'latitude',
                'longitude');

            $radius = $values['radius'];

            unset($values['search_by_distance'], $values['longitude'], $values['longitude'], $values['radius']);

            $searchByDistance = true;

            $columns = ['*', 'distance' => $formula];
        }

        $select = $this->_db
            ->select()
            ->from('fanwall_post', $columns)
            ->joinLeft(
                'customer',
                'customer.customer_id = fanwall_post.customer_id',
                [
                    'firstname',
                    'lastname',
                    'nickname',
                    'author_id' => new DbExpr('customer.customer_id'),
                    'author_image' => new DbExpr('customer.image'),
                ]);

        if ($searchByDistance) {
            // Filtering unlocated posts
            $select->where('(latitude != 0 AND longitude !=0 AND latitude IS NOT NULL AND longitude IS NOT NULL)');
            $select->having('distance < ?', $radius);
            $select->order(['distance ASC']);
        } else {
            if ($order !== null) {
                $select->order($order);
            }
        }

        foreach ($values as $condition => $value) {
            $select->where($condition, $value);
        }

        // Scheduled limit
        if (!array_key_exists('all_scheduled', $params)) {
            $select
                ->where('((is_scheduled = 1 AND date <= UNIX_TIMESTAMP()) OR (is_scheduled = 0))');
        }

        if (array_key_exists('limit', $params) &&
            array_key_exists('offset', $params)) {
            $select->limit($params['limit'], $params['offset']);
        }

        return $this->toModelClass($this->_db->fetchAll($select));
    }

    /**
     * @param array $values
     * @param null $order
     * @param array $params
     * @return ModelPost[]
     * @throws \Zend_Exception
     */
    public function findAllImages($values = [], $order = null, $params = [])
    {
        $select = $this->_db
            ->select()
            ->from("fanwall_post")
            ->where("(fanwall_post.image != '' AND fanwall_post.image IS NOT NULL)");
            //->joinLeft(
            //    "customer",
            //    "customer.customer_id = fanwall_post.customer_id",
            //    [
            //        "firstname",
            //        "lastname",
            //        "nickname",
            //        "author_image" => new DbExpr("customer.image"),
            //    ]);

        foreach ($values as $condition => $value) {
            $select->where($condition, $value);
        }

        // Scheduled limit
        $select
            ->where('((is_scheduled = 1 AND date <= UNIX_TIMESTAMP()) OR (is_scheduled = 0))');

        if ($order !== null) {
            $select->order($order);
        }

        if (array_key_exists("limit", $params) &&
            array_key_exists("offset", $params)) {
            $select->limit($params["limit"], $params["offset"]);
        }

        return $this->toModelClass($this->_db->fetchAll($select));
    }
}
