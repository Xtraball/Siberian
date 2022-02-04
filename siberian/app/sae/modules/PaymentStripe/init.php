<?php

use Siberian\Assets;
use Siberian\Translation;
use PaymentMethod\Model\Gateway;
use PaymentStripe\Model\Stripe as PaymentStripe;

class_alias('PaymentStripe\Model\Payment', 'PaymentStripe_Model_Payment');

$init = static function ($bootstrap) {
    Assets::registerScss([
        '/app/sae/modules/PaymentStripe/features/payment_stripe/scss/payment-stripe.scss'
    ]);

    Translation::registerExtractor(
        'payment_stripe',
        'PaymentStripe',
        path('app/sae/modules/PaymentStripe/resources/translations/default/payment_stripe.po'));

    Gateway::register('stripe', [
        'class' => PaymentStripe::class,
        'aclCode' => 'payment_stripe_settings',
        'label' => p__('payment_stripe', 'Stripe'),
        'url' => 'paymentstripe/settings',
        'icon' => 'icon ion-sb-stripe',
        'paymentMethod' => PaymentStripe::$paymentMethod,
        'shortName' => PaymentStripe::$shortName,
        'accepts' => [
            Gateway::PAY,
            Gateway::AUTHORIZE,
            Gateway::SUBSCRIPTION
        ],
        'templateUrl' => './features/payment_stripe/assets/templates/l1/payment-stripe.html'
    ]);
};

