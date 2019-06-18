<?php

namespace Fanwall\Model;

use Core\Model\Base;

/**
 * Class Post
 * @package Fanwall\Model
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
     * @return array
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
     * @param $valueId
     * @param $pos_id
     * @return $this
     */
    public function findLast($valueId, $pos_id)
    {
        $row = $this->getTable()->findLast($valueId, $pos_id);
        if ($row) {
            $this->setData($row->getData())
                ->setId($row->getId());
        }
        return $this;
    }

    /**
     * @param $valueId
     * @return mixed
     */
    public function findLastest($valueId)
    {
        return $comments = $this->getTable()->findLastest($valueId);
    }

    /**
     * @param $valueId
     * @return mixed
     */
    public function findAllWithPhoto($valueId)
    {
        return $comments = $this->getTable()->findAllWithPhoto($valueId);
    }

    /**
     * @param $valueId
     * @param $offset
     * @return mixed
     */
    public function findAllWithLocation($valueId, $offset)
    {
        return $comments = $this->getTable()->findAllWithLocation($valueId, $offset);
    }

    /**
     * @param $valueId
     * @return mixed
     */
    public function findAllWithLocationAndPhoto($valueId)
    {
        return $comments = $this->getTable()->findAllWithLocationAndPhoto($valueId);
    }

    /**
     * @param $valueId
     * @param $start
     * @param $count
     * @return mixed
     */
    public function pullMore($valueId, $start, $count)
    {
        return $comments = $this->getTable()->pullMore($valueId, $start, $count);
    }

    /**
     * @return string|null
     */
    public function getImageUrl()
    {
        $image_path = Application_Model_Application::getImagePath() . $this->getData('image');
        $base_image_path = Application_Model_Application::getBaseImagePath() . $this->getData('image');
        if ($this->getData('image') AND file_exists($base_image_path)) {
            return $image_path;
        }
        return null;
    }

    /**
     * @return array
     */
    public function getAnswers()
    {
        if (!$this->getId()) return [];
        if (is_null($this->_answers)) {
            $answer = new Fanwall_Model_Answer();
            $answer->setStatus($this);
            $this->_answers = $answer->findByComment($this->getId(), true);
            foreach ($this->_answers as $answer) {
                $answer->setComment($this);
            }
        }

        return $this->_answers;
    }

    /**
     * @return array
     */
    public function getLikes()
    {
        if (!$this->getId()) return [];
        if (is_null($this->_likes)) {
            $like = new Fanwall_Model_Like();
            $this->_likes = $like->findByComment($this->getId());
            foreach ($this->_likes as $like) {
                $like->setComment($this);
            }
        }

        return $this->_likes;
    }

    /**
     * @return mixed
     */
    public function getCustomer()
    {
        if (is_null($this->_customer)) {
            $customer = new Customer_Model_Customer();
            $this->_customer = $customer->find($this->getCustomerId());
        }

        return $this->_customer;
    }

    /**
     * @param $option_value
     * @param $design
     * @param $category
     * @throws Zend_Exception
     */
    public function createDummyContents($option_value, $design, $category)
    {

        $option = new Application_Model_Option();
        $option->find($option_value->getOptionId());

        $dummy_content_xml = $this->_getDummyXml($design, $category);

        if ($dummy_content_xml->{$option->getCode()}) {
            foreach ($dummy_content_xml->{$option->getCode()}->children() as $content) {
                $this->unsData();

                $this->addData((array)$content)
                    ->setValueId($option_value->getId())
                    ->save();
            }
        }

    }

    /**
     * @param $option
     * @return $this
     */
    public function copyTo($option)
    {
        $this->setId(null)
            ->setValueId($option->getId());

        if ($image_url = $this->getImageUrl()) {

            $file = pathinfo($image_url);
            $filename = $file['basename'];

            $relativePath = $option->getImagePathTo();
            $folder = Core_Model_Directory::getBasePathTo(Application_Model_Application::PATH_IMAGE . $relativePath);

            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }

            $img_src = Core_Model_Directory::getBasePathTo($image_url);
            $img_dst = $folder . '/' . $filename;

            if (copy($img_src, $img_dst)) {
                $this->setImage($relativePath . '/' . $filename);
            }
        }

        $this->save();

        return $this;
    }

}
