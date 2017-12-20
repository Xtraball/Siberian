<?php

$init = function ($bootstrap) {
    Payment_Model_Payment::addPaymentType('paypal', 'Paypal');
};
    