<?php

/**
 * Class Application_PwaController
 */
class Application_PwaController extends Application_Controller_Default
{
    /**
     *
     */
    public function buildAction()
    {
        try {
            //$request = $this->getRequest();
            $application = $this->getApplication();

            Application_Model_Pwa::generate($application);
        
            $payload = [
                "success" => true,
                "message" => __("Success"),
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }
        
        $this->_sendJson($payload);
    }
}
