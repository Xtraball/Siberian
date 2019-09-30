<?php

use Siberian\Assets;
use Siberian\Translation;
use PaymentMethod\Model\Gateway;

class_alias("PaymentCash\Model\Payment", "PaymentCash_Model_Payment");

$init = function($bootstrap) {
    Assets::registerScss([
        "/app/local/modules/PaymentCash/features/payment_cash/scss/payment-cash.scss"
    ]);

    Translation::registerExtractor(
        "payment_cash",
        "PaymentCash",
        path("app/local/modules/PaymentCash/resources/translations/default/payment-cash.po"));

    Gateway::register("cash", [
        "class" => "\PaymentCash\Model\Cash",
        "aclCode" => "payment_cash_settings",
        "label" => p__("payment_cash", "Cash"),
        "url" => "paymentcash/settings",
        "icon" => "icon ion-sb-stripe",
        "paymentMethods" => ["cash"],
        "templateUrl" => "./features/payment_cash/assets/templates/l1/payment-cash.html",
    ]);
};

