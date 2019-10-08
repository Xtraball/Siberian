<?php

namespace PaymentMethod\Controller;

use \Application_Controller_Default;

/**
 * Class AbstractMobilePaymentController
 * @package PaymentMethod\Controller
 */
abstract class AbstractPaymentController
    extends Application_Controller_Default
    implements AbstractPaymentInterface
{
    /**
     * Test endpoint for payment_method!
     */
    public function testAction()
    {
        $this->_sendJson([
            "success" => true,
            "message" => "Success",
        ]);
    }
}