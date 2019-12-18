<?php

namespace Mcommerce\Form;

use Siberian_Form_Abstract as FormAbstract;

/**
 * Class Mcommerce_Form_Store
 */
class Discount extends FormAbstract
{
    /**
     * @throws \Zend_Form_Exception
     * @throws \Zend_Validate_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path('/mcommerce/application_store/edit-discount'))
            ->setAttrib('id', 'discount_edit_form')
            ->addNav('discount_tax_form', 'Save');

        /** Bind as a create form */
        self::addClass('create', $this);

        $this->addSimpleCheckbox('enabled',
            p__('m_commerce', 'Active?'));

        $this->addSimpleText('label', p__('m_commerce', 'Label'));
        $this->addSimpleText('code', p__('m_commerce', 'Code'));
        $this->addSimpleNumber('minimum_amount', p__('m_commerce', 'Code'), 0, 999999999999999, true, 0.0001);
        $this->addSimpleSelect('type', p__('m_commerce', 'Type'), [
            'fixed' => p__('m_commerce', 'Fixed'),
            'percentage' => p__('m_commerce', 'Percentage'),
        ]);
        $this->addSimpleNumber('discount', p__('m_commerce', 'Amount'), 0, 999999999999999, true, 0.0001);

        $validUntil = $this->addSimpleDatetimepicker(
            "valid_until",
            __("Valid until"),
            false,
            FormAbstract::DATETIMEPICKER,
            'yy-mm-dd'
        );
        $validUntil->setDescription(p__('m_commerce', 'Leave blank for unlimited.'));

        $this->addSimpleCheckbox('use_once',
            p__('m_commerce', 'Usable once?'));
    }

    /**
     * label=&code=&minimum_amount=&type=fixed&discount=&valid_until=&option_value_id=1
     */
}

