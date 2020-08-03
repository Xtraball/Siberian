<?php

namespace InAppPurchase\Form;

use Siberian_Form_Abstract as FormAbstract;

/**
 * Class Settings
 * @package InAppPurchase\Form
 */
class Settings extends FormAbstract
{

    public function init()
    {
        parent::init();

        $this
            ->setAction(__path('/inapppurchase/settings/save'))
            ->setAttrib('id', 'iap-settings');

        // Bind as a create form!
        self::addClass('onchange', $this);

        $billingKey = $this->addSimpleText('google_billing_key', p__('iap', 'Google Play public billing key'));
        $billingKey->setRequired(true);

        $submit = $this->addSubmit(p__('iap', 'Save'));
        $submit->addClass('pull-right');
    }
}
