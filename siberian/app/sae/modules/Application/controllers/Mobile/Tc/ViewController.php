<?php

class Application_Mobile_Tc_ViewController extends Application_Controller_Mobile_Default {

    public function findAction() {

        try {

            if($tc_id = $this->getRequest()->getParam("tc_id")) {

                try {

                    $tc = new Application_Model_Tc();
                    $tc->find($tc_id);
                    $data = array(
                        "success"           => true,
                        "page_title"        => __("Terms & Conditions"),
                        "terms_conditions"  => $tc->getText(),

                        /** Pre 5.0.0 backward compatibility. */
                        "html_file_path"    => $this->getRequest()->getBaseUrl().$tc->getHtmlFilePath()
                    );

                } catch(Exception $e) {
                    throw new Siberian_Exception(__("Unable to find TC with id %s.", $tc_id));
                }

            } else {
                throw new Siberian_Exception(__("Missing parameters."));
            }

        } catch (Exception $e) {

            $message = $e->getMessage();
            $message = (empty($message)) ? __("%s An unknown error occurred, please try again later.", "Tc::findAction") : $message;

            $data = array(
                "error"     => true,
                "message"   => $message
            );

        }

        $this->_sendJson($data);



    }

}
