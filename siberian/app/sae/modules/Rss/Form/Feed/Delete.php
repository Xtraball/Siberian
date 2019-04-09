<?php

/**
 * Class Rss_Form_Feed_Delete
 */
class Rss_Form_Feed_Delete extends Siberian_Form_Abstract
{

    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/rss/application/delete-feed"))
            ->setAttrib("id", "form-feed-delete")
            ->setConfirmText(p__("rss", "You are about to remove this Feed ! Are you sure ?"));

        /** Bind as a delete form */
        self::addClass("delete", $this);

        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select()
            ->from('rss_feed')
            ->where('rss_feed.feed_id = :value');

        $category_id = $this->addSimpleHidden("feed_id", p__("rss", "Feed"));
        $category_id->addValidator("Db_RecordExists", true, $select);
        $category_id->setMinimalDecorator();

        $value_id = $this->addSimpleHidden("value_id");
        $value_id
            ->setRequired(true);

        $mini_submit = $this->addMiniSubmit();
    }
}