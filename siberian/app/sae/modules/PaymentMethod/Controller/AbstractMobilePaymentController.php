<?php

namespace PaymentMethod\Controller;

use \Application_Controller_Mobile_Default;

/**
 * Class AbstractMobilePaymentController
 * @package PaymentMethod\Controller
 */
abstract class AbstractMobilePaymentController
    extends Application_Controller_Mobile_Default
    implements AbstractPaymentInterface
{
    /**
     * @throws \Zend_Controller_Response_Exception
     */
    public function testAction()
    {
        $this->_sendJson([
            'success' => true,
            'message' => 'Success',
        ]);
    }
}