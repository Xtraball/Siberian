<?php

class Application_Model_TagOption extends Core_Model_Default
{
    public function __construct($params = array())
    {
        parent::__construct($params);
        $this->_db_table = 'Application_Model_Db_Table_TagOption';
        return $this;
    }

    /**
     * Configures the association between a tag and a domain object.
     * Sets the value_id, object_id and model properties of an association between a tag and a domain object.
     *
     * Usage example:
     * $tag_option = new Application_Model_TagOption();
     * $tag_option->setObject($page);
     * $tag_option->setTagId($tag->getTagId());
     * $tag_option->save();
     *
     * @param Object              A domain Object
     * @return collection A collection of Application_Model_Db_Table_Option_Value
     */
    public function setObject($object)
    {
        $this->setValueId($object->getValueId());
        $this->setObjectId($object->getId());

        $model_name = method_exists($object, "getModelClass") ? $object->getModelClass() : get_class($object);
        $this->setModel($model_name);

        return $this;
    }

}
