<?php

/**
 * Class Mcommerce_Form_Store
 */
class Mcommerce_Form_Store extends Siberian_Form_Abstract
{
    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/mcommerce/application_store/editpost"))
            ->setAttrib("id", "storeEditForm")
            ->addNav("store-nav");

        /** Bind as a create form */
        self::addClass("create", $this);

        $storeName = $this->addSimpleText("name", __("Store name"));
        $storeName->setRequired(true);

        $email = $this->addSimpleText("email", __("E-mail"));
        $email->setRequired(true);

        $phone = $this->addSimpleText("phone", __("Phone"));
        $phone->setRequired(true);

        $address = $this->addSimpleText("address", __("Address"));
        $address->setRequired(true);

        // Group delivery methods --- new_delivery_methods[][method_id]

        $deliveryOptions = [];
        $deliveryMethods = (new Mcommerce_Model_Delivery_Method())
            ->findAll();
        foreach ($deliveryMethods as $deliveryMethod) {
            $id = $deliveryMethod->getId();
            $name = $deliveryMethod->getName();

            $deliveryOptions[$id] = $name;
        }

        $deliveryMethods = $this->addSimpleMultiCheckbox(
            "new_delivery_methods",
            __("Delivery methods"),
            $deliveryOptions);
        $deliveryMethods->setRequired(true);


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
}