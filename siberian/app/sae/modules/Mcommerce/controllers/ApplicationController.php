<?php

/**
 * Class Mcommerce_ApplicationController
 */
class Mcommerce_ApplicationController extends Application_Controller_Default_Ajax
{
    /**
     *
     */
    public function humanDeliveryTimeAction()
    {
        try {
            $request = $this->getRequest();
            $deliveryTime = $request->getParam('delivery_time', 0);
            $textualDeliveryTime = Mcommerce_Model_Utility::getHumanDeliveryTime($deliveryTime);

            $payload = [
                'success' => true,
                'message' => p__('m_commerce', 'Displayed delay in app') . ': ' . $textualDeliveryTime,
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }
}