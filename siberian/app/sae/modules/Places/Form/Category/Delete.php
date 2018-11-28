<?php

/**
 * Class Places_Form_Category_Delete
 */
class Places_Form_Category_Delete extends Siberian_Form_Abstract
{
    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/places/application/delete-category"))
            ->setAttrib("id", "form-category-delete")
            ->setConfirmText("You are about to remove this Category ! Are you sure ?");

        /** Bind as a delete form */
        self::addClass("delete", $this);

        $categoryId = $this->addSimpleHidden("category_id");
        $categoryId->setMinimalDecorator();

        $valueId = $this->addSimpleHidden("value_id");
        $valueId->setMinimalDecorator();

        $this->addMiniSubmit();
    }
}