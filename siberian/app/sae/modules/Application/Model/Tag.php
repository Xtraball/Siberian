<?php

/**
 * This class allows the creation of Tags.
 * It also allows the lookup of Features and other domain objects (e.g. Pages) associated with a given Tag.
 *
 * @package    SAE
 * @subpackage Application
 */
class Application_Model_Tag extends Core_Model_Default
{
    public function __construct($params = array())
    {
        parent::__construct($params);
        $this->_db_table = 'Application_Model_Db_Table_Tag';
        return $this;
    }

    /**
     * Get the application features (i.e. Option_Value) associated with the Tag
     *
     * Usage example:
     * $tag = new Application_Model_Tag();
     * $tag->find($some_id);
     * $tag->getOwnOptions();
     *
     * @uses Application_Model_Tag::getPages()
     * @return collection A collection of Application_Model_Db_Table_Option_Value
     */
    public function getOwnOptions()
    {
        return self::getOptions($this->getName());
    }

    /**
     * Get the application Pages associated with the current Tag
     *
     * Usage example:
     * $tag = new Application_Model_Tag();
     * $tag->find($some_id);
     * $tag->getOwnPages();
     *
     * @uses Application_Model_Tag::getPages()
     * @return collection A collection of Cms_Model_Db_Table_Application_Page
     */
    public function getOwnPages()
    {
        return self::getPages($this->getName());
    }

    /**
     * Create or update Tags given an array of tag names
     *
     * Usage example:
     * $tags = Application_Model_Tag::upsert(array('tag 1', 'tag 2'));
     *
     * @param  array
     * @return collection A collection of Application_Model_Db_Table_Tag
     */
    public static function upsert($tags_names = array())
    {
        $tags = array();
        foreach ($tags_names as $tags_name) {
            $model_tag = new Application_Model_Tag();
	    $tag = $model_tag->setName(trim($tags_name))->insertOrUpdate(array('name'));
            array_push($tags, $tag);
        }
        return $tags;
    }

    /**
     * Find the Pages associated with tag name
     *
     * Usage example:
     * $pages = Application_Model_Tag::getPages('tag X');
     *
     * @param  string       $tagname
     * @uses Application_Model_Tag::getObjects()
     * @return collection A collection of Cms_Model_Db_Table_Application_Page
     */
    public static function getPages($tagname)
    {
        return self::getObjects($tagname, 'Cms_Model_Application_Page');
    }

    /**
     * Get the domain objects (e.g. Pages, Comment, etc.)
     *
     * Usage example:
     * $tags = Application_Model_Tag::getObjects('tag X', 'Cms_Model_Application_Page');
     *
     * @param  string       $tagname
     * @param  string       $model_name
     * @return collection A collection of instances of $model_name
     */
    public static function getObjects($tagname, $model_name)
    {
        $model = new $model_name();
        $table = $model->getTable();
        $table_info = $table->info();
        $table_name = $table_info['name'];
        // Supporting simple primary keys, which is sufficient.
        $primary_key = $table_info['primary']['1'];

        $select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
        $select
            ->setIntegrityCheck(false)
            ->join('application_tagoption', 'application_tagoption.object_id = ' . $table_name . '.' . $primary_key)
            ->join('application_tag', 'application_tag.tag_id = application_tagoption.tag_id')
            ->where('application_tagoption.model = ?', $model_name)
            ->where('application_tag.name = ?', $tagname);

        $rows = $table->fetchAll($select);
        return $rows;
    }

    /**
     * Get the Features (i.e. Option_Values) associated with the given Tag name
     *
     * Usage example:
     * $tags = Application_Model_Tag::getOptions('tag X');
     *
     * @param  string       $tagname
     * @return collection A collection of Application_Model_Db_Table_Option_Value
     */
    public static function getOptions($tagname)
    {
        $model_tag = new Application_Model_Db_Table_Tag();
	$tag = $model_tag->fetchRow(array('name = ?' => $tagname));

        if ($tag) {
            return $tag->findManyToManyRowset('Application_Model_Db_Table_Option_Value', 'Application_Model_Db_Table_TagOption');
        } else {
            return array();
        }
    }

}
