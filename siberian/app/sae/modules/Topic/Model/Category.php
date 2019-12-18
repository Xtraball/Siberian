<?php

/**
 * Class Topic_Model_Category
 *
 * @method Topic_Model_Db_Table_Category getTable()
 */
class Topic_Model_Category extends Core_Model_Default
{

    /**
     * Topic_Model_Category constructor.
     * @param array $params
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = Topic_Model_Db_Table_Category::class;
    }

    /**
     * @return $this
     */
    public function delete()
    {
        $this->getTable()->deleteByParentId($this->getId());
        parent::delete();
        return $this;
    }

    /**
     * @param $topic_id
     * @return mixed
     */
    public function getTopicCategories($topic_id)
    {
        return $this->getTable()->getTopicCategories($topic_id);
    }

    /**
     * @return mixed
     */
    public function getChildren()
    {
        return $this->getTable()->getChildren($this->getId());
    }

    /**
     * @param $topic_id
     * @return int
     */
    public function getMaxPosition($topic_id): int
    {
        $position = $this->getTable()->getMaxPosition($topic_id);

        return is_numeric($position) ? $position : 0;
    }
}
