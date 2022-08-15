<?php

namespace PaymentCash\Form;

use Siberian_Form_Abstract as FormAbstract;

/**
 * Class Settings
 * @package PaymentCash\Form
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
            ->setAction(__path("/paymentcash/settings/save"))
            ->setAttrib("id", "form-payment-cash");

        // Bind as a create form!
        self::addClass("create", $this);

        // Builds the default form from schema!
        $this->addSimpleHidden("value_id");
        $gateway = $this->addSimpleHidden("gateway");
        $gateway->setValue("cash");

        $this->addSimpleCheckbox("is_enabled", p__("payment_cash", "Enabled?"));

        $save = $this->addSubmit(p__("payment_cash", "Save"), p__("payment_cash", "Save"));
        $save->addClass("pull-right");
    }
}
