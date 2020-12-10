<?php

/**
 * Class Places_Form_Place_Delete
 */
class Places_Form_Place_Delete extends Siberian_Form_Abstract
{
    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/places/application/delete"))
            ->setAttrib("id", "form-place-delete")
            ->setConfirmText("You are about to remove this Place ! Are you sure ?");;

        /** Bind as a delete form */
        self::addClass("delete", $this);

        $id = $this->addSimpleHidden("id");
        $id->setMinimalDecorator();

        $option_value_id = $this->addSimpleHidden("option_value_id");
        $option_value_id->setMinimalDecorator();

        $mini_submit = $this->addMiniSubmit();
    }
}
