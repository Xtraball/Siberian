<?php

namespace Mcommerce\Form;

use Siberian_Form_Abstract as FormAbstract;
use Mcommerce_Model_Delivery_Method as DeliveryMethod;

/**
 * Class Mcommerce_Form_Store
 */
class Store extends FormAbstract
{
    /**
     * @throws \Zend_Form_Exception
     * @throws \Zend_Validate_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path('/mcommerce/application_store/edit-post'))
            ->setAttrib('id', 'storeEditForm')
            ->addNav('store-nav');

        /** Bind as a create form */
        self::addClass('create', $this);

        $this->addSimpleHidden('store_id');
        $this->addSimpleHidden('mcommerce_id');

        $storeName = $this->addSimpleText('name', __('Store name'));
        $storeName->setRequired(true);

        $email = $this->addSimpleText('email', __('E-mail'));
        $email->setRequired(true);

        $phone = $this->addSimpleText('phone', __('Phone'));
        $phone->setRequired(true);

        $address = $this->addSimpleText('address', __('Address'));
        $address->setRequired(true);

        $this->addSimpleText('opening_hours', __('Opening hours'));

        $this->addSimpleText('printer_email', __('Connected printer e-mail'));

        $this->groupElements(
            'store_information',
            ['name', 'email', 'phone', 'address', 'printer_email'],
            p__('m_commerce', 'Information'));

        $deliveryOptions = [];
        $deliveryMethods = (new DeliveryMethod())
            ->findAll();
        foreach ($deliveryMethods as $deliveryMethod) {
            $id = $deliveryMethod->getId();
            $name = $deliveryMethod->getName();

            $deliveryOptions[$id] = $name;
        }

        $deliveryMethods = $this->addSimpleMultiCheckbox(
            'new_delivery_methods',
            __('Delivery methods'),
            $deliveryOptions);
        $deliveryMethods->setRequired(true);

        $this->groupElements(
            'store_delivery',
            ['new_delivery_methods'],
            p__('m_commerce', 'Delivery'));

        // Shipping/delivery options!
        $this->addSimpleNumber(
            'delivery_fees',
            p__('m_commerce', 'Shipping/delivery fees'),
            0, null, true, 0.0001);

        // Shipping/delivery options!
        $this->addSimpleSelect(
            'delivery_tax',
            p__('m_commerce', 'Shipping/delivery tax'));

        $this->addSimpleNumber(
            'delivery_free_shipping',
            p__('m_commerce', 'Free shipping/delivery starting from'),
            0, null, true, 0.0001);

        $this->addSimpleNumber(
            'delivery_radius',
            p__('m_commerce', 'Shipping/delivery radius (in kilometers)'),
            0, null, true, 0.1);

        $deliveryDelay = $this->addSimpleNumber(
            'delivery_delay',
            p__('m_commerce', 'Shipping/delivery delay (in minutes)'),
            0, null, true, 1);
        $deliveryDelay->setDescription('-');

        $this->addSimpleNumber(
            'delivery_minimum_order',
            p__('m_commerce', 'Minimum order amount'),
            0, null, true, 0.0001);

        $this->addSimpleCheckbox(
            'delivery_client_calculate_change',
            p__('m_commerce', 'The client will calculate the change'));

        $this->groupElements(
            'store_delivery_options',
            [
                'delivery_fees',
                'delivery_tax',
                'delivery_free_shipping',
                'delivery_radius',
                'delivery_delay',
                'delivery_minimum_order',
                'delivery_client_calculate_change',
            ],
            p__('m_commerce', 'Shipping/delivery options'));

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

    /**
     * @param $id
     * @return $this
     */
    public function setStoreId($id): self
    {
        $this->getElement('store_id')->setValue($id);

        return $this;
    }

}