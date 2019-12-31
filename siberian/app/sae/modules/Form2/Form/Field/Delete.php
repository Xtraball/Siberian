<?php

namespace Cabride\Form\Field;

use Siberian_Form_Abstract as FormAbstract;

/**
 * Class Delete
 * @package Cabride\Form\Field
 */
class Delete extends FormAbstract
{
    /**
     * @throws \Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/cabride/field/delete"))
            ->setAttrib("id", "form-field-delete")
            ->setConfirmText("You are about to remove this custom field! Are you sure ?");

        /** Bind as a delete form */
        self::addClass("delete", $this);

        $categoryId = $this->addSimpleHidden("field_id");
        $categoryId->setMinimalDecorator();

        $valueId = $this->addSimpleHidden("value_id");
        $valueId->setMinimalDecorator();

        $this->addMiniSubmit();
    }
}