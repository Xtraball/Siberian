<?php

namespace Mcommerce\Form;

use Siberian_Form_Abstract as FormAbstract;

/**
 * Class Mcommerce_Form_Store
 */
class Settings extends FormAbstract
{
    /**
     * @throws \Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path('/mcommerce/application_settings/edit-post'))
            ->setAttrib('id', 'settings_edit_form');

        /** Bind as a create form */
        self::addClass('create', $this);

        $this->addSimpleHidden('mcommerce_id');
        $this->addSimpleHidden('value_id');

        $userFieldOptions = [
            'hidden' => p__('m_commerce', 'Hidden'),
            'optional' => p__('m_commerce', 'Optional'),
            'mandatory' => p__('m_commerce', 'Mandatory'),
        ];

        $this->addSimpleSelect('phone',
            p__('m_commerce', 'Phone number'), $userFieldOptions);
        $this->addSimpleSelect('birthday',
            p__('m_commerce', 'Birthday'), $userFieldOptions);
        $this->addSimpleSelect('invoicing_address',
            p__('m_commerce', 'Invoicing address'), $userFieldOptions);
        $this->addSimpleSelect('delivery_address',
            p__('m_commerce', 'Delivery address'), $userFieldOptions);

        $this->groupElements(
            'user_fields',
            ['phone', 'birthday', 'invoicing_address', 'delivery_address'],
            p__('m_commerce', 'User fields'));


        $this->addSimpleCheckbox('show_search',
            p__('m_commerce', 'Show search fields on product list?'));

        $this->addSimpleCheckbox('add_tip',
            p__('m_commerce', 'Customer can add tip to a command?'));

        $this->addSimpleCheckbox('guest_mode',
            p__('m_commerce', 'Allow guest to order?'));

        $this->groupElements(
            'order_options',
            ['show_search', 'add_tip', 'guest_mode'],
            p__('m_commerce', 'Options'));

        $saveLabel = p__('m_commerce', 'Save');
        $submit = $this->addSubmit($saveLabel, $saveLabel);
        $submit->addClass('pull-right');
    }
}