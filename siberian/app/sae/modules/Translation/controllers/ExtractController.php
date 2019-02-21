<?php

/**
 * Class Translation_ExtractController
 */
class Translation_ExtractController extends Application_Controller_Mobile_Default
{
    /**
     *
     */
    public function indexAction()
    {
        try {
            $request = $this->getRequest();
            $params = $request->getParams();

            if (empty($params["context"])) {
                __(base64_decode($params["text"]));
            } else {
                p__(base64_decode($params["context"]), base64_decode($params["text"]));
            }
        } catch (\Exception $e) {

        }

        die("thanks");
    }
}
