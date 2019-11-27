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
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path('/mcommerce/application_store/editpost'))
            ->setAttrib('id', 'storeEditForm')
            ->addNav('store-nav');

        /** Bind as a create form */
        self::addClass('create', $this);

        $storeName = $this->addSimpleText('name', __('Store name'));
        $storeName->setRequired(true);

        $email = $this->addSimpleText('email', __('E-mail'));
        $email->setRequired(true);

        $phone = $this->addSimpleText('phone', __('Phone'));
        $phone->setRequired(true);

        $address = $this->addSimpleTextarea('address', __('Address'));
        $address->setRequired(true);

        // Group delivery methods --- new_delivery_methods[][method_id]
        
        $this->groupElements(
            'store_information',
            ['name', 'email', 'phone', 'address'],
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
                'delivery_free_shipping',
                'delivery_radius',
                'delivery_delay',
                'delivery_minimum_order',
                'delivery_client_calculate_change',
            ],
            p__('m_commerce', 'Shipping/delivery options'));

        /**
 * if($method->getStoreDeliveryMethodId()) checked
 * value = $method->getId(); ?
* $method->getName();
 *
 */


        /**
        $delivery_method = new Mcommerce_Model_Delivery_Method(); ?>
                    $delivery_methods = $delivery_method->findAll(); ?>
                    foreach($delivery_methods as $k => $method) :
                        <div class="form-group">
                            <div class="col-md-12">
                                <?php $method->addData($store->getDeliveryMethod($method->getId())->getData()); ?>
                                <label for="delivery_method_<?php echo $method->getId() ?>"
                                       class="control required delivery_method <?php if(!$method->isFree()) : ?> toggle_shippging_cost<?php endif; ?>">
                                    <input type="checkbox"
                                           id="delivery_method_<?php echo $method->getId() ?>"
                                           color="color-blue"
                                           class="required sb-form-checkbox color-blue"
                                           name="new_delivery_methods[][method_id]"
                                           value="<?php echo $method->getId(); ?>"<?php if($method->getStoreDeliveryMethodId()) : ?>
                                            checked="checked"<?php endif; ?> />
                                    <span class="sb-checkbox-label">
                                        <?php echo $method->getName(); ?>
                                    </span>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
*/
        // opening_hours

    }

    /**
     * @param array $values
     * @return Zend_Form
     */
    public function populate(array $values)
    {
        //$options = [];
        //foreach ($deliveryMethods as $deliveryMethod) {
        //    $deliveryMethod->addData($store->getDeliveryMethod($deliveryMethod->getId())->getData());
        //    $options[] = [
        //        "id" => $deliveryMethod->getId(),
        //        "checked" => $deliveryMethod->getStoreDeliveryMethodId(),
        //    ];
        //}

        return parent::populate($values); // TODO: Change the autogenerated stub
    }
}