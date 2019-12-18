<?php

namespace Weblink\Form\Delete;

use Siberian_Form_Abstract as FormAbstract;
use Zend_Db_Table as DbTable;

/**
 * Class Link
 * @package Weblink\Form\Delete
 */
class Link extends FormAbstract
{
    /**
     * @throws \Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path('/weblink/application/delete-link'))
            ->setAttrib('id', 'form-link-delete')
            ->setConfirmText(p__('weblink', 'You are about to remove this Link ! Are you sure ?'));

        /** Bind as a delete form */
        self::addClass('delete', $this);

        $db = DbTable::getDefaultAdapter();
        $select = $db->select()
            ->from('weblink_link')
            ->where('weblink_link.link_id = :value');

        $category_id = $this->addSimpleHidden('link_id', p__('weblink', 'Link'));
        $category_id->addValidator('Db_RecordExists', true, $select);
        $category_id->setMinimalDecorator();

        $value_id = $this->addSimpleHidden('value_id');
        $value_id
            ->setRequired(true);

        $mini_submit = $this->addMiniSubmit();
    }
}