<?php

use PaymentMethod\Model\Gateway;
use Siberian\Hook;
use Siberian\Translation;

class_alias('\PaymentMethod\Model\Gateway', 'PaymentMethod_Model_Gateway');

function paymentGatewayEditorNav ($editorTree) {
    return Gateway::editorNav($editorTree);
}

$init = static function ($bootstrap) {
    Translation::registerExtractor(
        'payment_method',
        'PaymentMethod',
        path('app/sae/modules/PaymentMethod/resources/translations/default/payment_method.po'));

    Hook::listen(
        'editor.left.menu.ready',
        'payment_method_nav',
        'paymentGatewayEditorNav');
};

