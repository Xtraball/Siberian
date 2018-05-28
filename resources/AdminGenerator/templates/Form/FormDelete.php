<?php

/**
 * Class #MODULE#_Form_#MODEL#_Delete
 */
class #MODULE#_Form_#MODEL#_Delete extends Siberian_Form_Abstract
{
    /**
     * init wrapper
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("#FORM_DELETE_ACTION#"))
            ->setAttrib("id", "form-delete-#FORM_ID#")
            ->setConfirmText("You are about to remove this #HUMAN# ! Are you sure ?");

        /** Bind as a delete form */
        self::addClass("delete", $this);

        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select()
            ->from('#TABLE_NAME#')
            ->where('#TABLE_NAME#.#PRIMARY_KEY# = :value');

        $#PRIMARY_KEY# = $this->addSimpleHidden("#PRIMARY_KEY#", __("#MODEL#"));
        $#PRIMARY_KEY#->addValidator("Db_RecordExists", true, $select);
        $#PRIMARY_KEY#->setMinimalDecorator();

        $miniSubmit = $this->addMiniSubmit();
    }
}
