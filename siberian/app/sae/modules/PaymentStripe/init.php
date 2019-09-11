<?php

use Siberian\Assets;
use Siberian\Hook;
use Siberian\Translation;
use PaymentStripe\Model\Payment;

class_alias("PaymentStripe\Model\Payment", "PaymentStripe_Model_Payment");

function stripeEditorNav ($editorTree) {
    return Payment::editorNav($editorTree);
}

$init = function($bootstrap) {
    // Cab-Ride
    Assets::registerScss([
        "/app/local/modules/PaymentStripe/features/payment_stripe/scss/payment-stripe.scss"
    ]);

    Translation::registerExtractor(
        "payment_stripe",
        "PaymentStripe",
        path("app/local/modules/PaymentStripe/resources/translations/default/payment-stripe.po"));

    Hook::listen(
        "editor.left.menu.ready",
        "payment_stripe_nav",
        "stripeEditorNav");
};

