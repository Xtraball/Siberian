<?php

use Form2\Model\Form;

/**
 * Class Form2_MobileController
 */
class Form2_MobileController extends Application_Controller_Mobile_Default
{
    /**
     *
     */
    public function findAction()
    {
        try {
            $payload = (new Form())->getEmbedPayload($this->getCurrentOptionValue());
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }
}
