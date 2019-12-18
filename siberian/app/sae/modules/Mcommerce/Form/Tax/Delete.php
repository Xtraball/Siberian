<?php

namespace Mcommerce\Form\Tax;

use Siberian_Form_Abstract as FormAbstract;

/**
 * Class Delete
 * @package Mcommerce\Form\Tax
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
            ->setAction(__path('/mcommerce/application_settings_tax/delete-post'))
            ->setAttrib('id', 'form-tax-delete')
            ->setConfirmText(p__('m_commerce', 'You are about to remove this tax! Are you sure?'));

        /** Bind as a delete form */
        self::addClass('delete', $this);

        $storeId = $this->addSimpleHidden('tax_id');
        $storeId->setMinimalDecorator();

        $this->addMiniSubmit();
    }

}