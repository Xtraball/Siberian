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
            $bulk = $request->getBodyParams();

            foreach ($bulk as $translation) {
                // Extract only context lines!
                if (!empty($translation["context"])) {
                    p__(base64_decode($translation["context"]), base64_decode($translation["text"]), "mobile");
                }
            }
        } catch (\Exception $e) {

        }

        die("Thanks");
    }
}
