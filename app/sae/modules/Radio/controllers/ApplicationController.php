<?php

class Radio_ApplicationController extends Application_Controller_Default {
    /**
     * Simple edit post, validator
     */
    public function editpostAction() {
        $values = $this->getRequest()->getPost();

        $form = new Radio_Form_Radio();
        if($form->isValid($values)) {
            /** Do whatever you need when form is valid */
            $radio = new Radio_Model_Radio();
            $radio->find($values["radio_id"]);
            $radio->setData($values);

            if($values["background"] == "_delete_") {
                $radio->setData("background", "");
            } else if(file_exists(Core_Model_Directory::getBasePathTo("images/application".$values["background"]))) {
                # Nothing changed, skip
            } else {
                $background = Siberian_Feature::moveUploadedFile($this->getCurrentOptionValue(), Core_Model_Directory::getTmpDirectory()."/".$values["background"]);
                $radio->setData("background", $background);
            }

            /** Alert ipv4 */
            $warning_message = Siberian_Network::testipv4($values['link']);

            $radio->save();

            $html = array(
                "success" => 1,
                "message" => __("Success."),
            );
        } else {
            /** Do whatever you need when form is not valid */
            $html = array(
                "error" => 1,
                "message" => $form->getTextErrors(),
                "errors" => $form->getTextErrors(true)
            ); 
        }

        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

}