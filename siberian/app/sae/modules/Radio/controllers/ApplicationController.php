<?php

class Radio_ApplicationController extends Application_Controller_Default {

    /**
     * @var array
     */
    public $cache_triggers = array(
        "editpost" => array(
            "tags" => array(
                "homepage_app_#APP_ID#",
            ),
        ),
    );

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

            /** Update touch date, then never expires (until next touch) */
            $this->getCurrentOptionValue()
                ->touch()
                ->expires(-1);

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

    /**
     * @param $option
     * @return string
     * @throws Exception
     */
    public function exportAction() {
        if($this->getCurrentOptionValue()) {
            $radio = new Radio_Model_Radio();
            $result = $radio->exportAction($this->getCurrentOptionValue());

            $this->_download($result, "radio-".date("Y-m-d_h-i-s").".yml", "text/x-yaml");
        }
    }

}