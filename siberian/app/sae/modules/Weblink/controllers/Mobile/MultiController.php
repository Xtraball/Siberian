<?php

/**
 * Class Weblink_Mobile_MultiController
 */
class Weblink_Mobile_MultiController extends Application_Controller_Mobile_Default
{
    /**
     *
     */
    public function findAction()
    {
        $option = $this->getCurrentOptionValue();
        $payload = $option->getObject()->getEmbedPayload($option);
        $this->_sendJson($payload);
    }

}