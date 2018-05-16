<?php

/**
 * Class Wordpress2_Form_Query_Delete
 */
class Wordpress2_Form_Query_Delete extends Siberian_Form_Abstract
{
    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/wordpress2/application/deletequery"))
            ->setAttrib("id", "form-query-delete")
            ->setConfirmText('You are about to remove this Query ! Are you sure ?');

        /** Bind as a delete form */
        self::addClass('delete', $this);

        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select()
            ->from('wordpress2_query')
            ->where('wordpress2_query.query_id = :value');

        $queryId = $this->addSimpleHidden('query_id', __('Query'));
        $queryId->addValidator('Db_RecordExists', true, $select);
        $queryId->setMinimalDecorator();

        $value_id = $this->addSimpleHidden('value_id');
        $value_id
            ->setRequired(true);

        $mini_submit = $this->addMiniSubmit();
    }
}