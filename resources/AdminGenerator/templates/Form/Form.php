<?php

/**
 * Class #MODULE#_Form_#MODEL#
 */
class #MODULE#_Form_#MODEL# extends Siberian_Form_Abstract
{
    /**
     * init wrapper
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("#FORM_SAVE_ACTION#"))
            ->setAttrib("id", "form-#FORM_ID#")
            ->addNav("nav-#FORM_ID#");

        // Bind as a create form!
        self::addClass("create", $this);

        $#PRIMARY_KEY# = $this->addSimpleHidden("#PRIMARY_KEY#");

        // Builds the default form from schema!
        #ELEMENTS#
    }

    /**
     * @param $#PRIMARY_KEY#
     */
    public function set#PRIMARY_KEY_CAMEL#($#PRIMARY_KEY#)
    {
        $this->getElement("#PRIMARY_KEY#")->setValue($#PRIMARY_KEY#)->setRequired(true);
    }
}
