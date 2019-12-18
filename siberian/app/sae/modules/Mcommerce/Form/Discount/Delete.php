<?php

namespace Mcommerce\Form\Discount;

use Siberian_Form_Abstract as FormAbstract;

/**
 * Class Delete
 * @package Mcommerce\Form\Discount
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
            ->setAction(__path("/mcommerce/application_settings_discount/delete-post"))
            ->setAttrib("id", "form-discount-delete")
            ->setConfirmText(p__("m_commerce", "You are about to remove this discount! Are you sure?"));

        /** Bind as a delete form */
        self::addClass("delete", $this);

        $storeId = $this->addSimpleHidden("promo_id");
        $storeId->setMinimalDecorator();

        $this->addMiniSubmit();
    }
}