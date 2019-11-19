<?php

use Siberian\Assets;
use Siberian\Translation;
use PaymentMethod\Model\Gateway;

class_alias("PaymentStripe\Model\Payment", "PaymentStripe_Model_Payment");

$init = function($bootstrap) {
    Assets::registerScss([
        "/app/local/modules/PaymentStripe/features/payment_stripe/scss/payment-stripe.scss"
    ]);

    Translation::registerExtractor(
        "payment_stripe",
        "PaymentStripe",
        path("app/sae/modules/PaymentStripe/resources/translations/default/payment_stripe.po"));

    Gateway::register("stripe", [
        "class" => "\PaymentStripe\Model\Stripe",
        "aclCode" => "payment_stripe_settings",
        "label" => p__("payment_stripe", "Stripe"),
        "url" => "paymentstripe/settings",
        "icon" => "icon ion-sb-stripe",
        "paymentMethod" => "credit-card",
        "shortName" => "stripe",
        "templateUrl" => "./features/payment_stripe/assets/templates/l1/payment-stripe.html"
    ]);
};

