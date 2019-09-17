<?php

use PaymentMethod\Model\Gateway;
use Siberian\Hook;

class_alias("\PaymentMethod\Model\Gateway", "PaymentMethod_Model_Gateway");

function paymentGatewayEditorNav ($editorTree) {
    return Gateway::editorNav($editorTree);
}

$init = function($bootstrap) {
    Hook::listen(
        "editor.left.menu.ready",
        "payment_method_nav",
        "paymentGatewayEditorNav");
};

