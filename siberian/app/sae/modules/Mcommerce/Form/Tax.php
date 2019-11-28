<?php

namespace Mcommerce\Form;

use Siberian_Form_Abstract as FormAbstract;

/**
 * Class Mcommerce_Form_Store
 */
class Tax extends FormAbstract
{
    /**
     * @throws \Zend_Form_Exception
     * @throws \Zend_Validate_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path('/mcommerce/application_settings_tax/edit-post'))
            ->setAttrib('id', 'tax_edit_form')
            ->addNav('nav_tax_form', 'Save');

        /** Bind as a create form */
        self::addClass('create', $this);

        $this->addSimpleHidden('tax_id');
        $this->addSimpleHidden('mcommerce_id');
        $type = $this->addSimpleHidden('type');
        $type->setValue(1);

        $this->addSimpleText('name',
            p__('m_commerce', 'Label'));
        $this->addSimpleNumber('rate',
            p__('m_commerce', 'Rate'), 0, 100, true, 0.0001);
    }

    /**
     * @param $id
     * @return $this
     */
    public function setTaxId($id): self
    {
        $this->getElement('tax_id')->setValue($id);

        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setMcommerceId($id): self
    {
        $this->getElement('mcommerce_id')->setValue($id);

        return $this;
    }
}