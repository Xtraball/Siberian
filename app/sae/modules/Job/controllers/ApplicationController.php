<?php

class Job_ApplicationController extends Application_Controller_Default {

    /**
     * Save options
     */
    public function editoptionspostAction() {
        $values = $this->getRequest()->getPost();

        $form = new Job_Form_Options();
        if($form->isValid($values)) {

            $this->getCurrentOptionValue();

            $job = new Job_Model_Job();
            $job->find($this->getCurrentOptionValue()->getId(), "value_id");

            if(isset($values["display_search"])) {
                $job->setDisplaySearch($values["display_search"]);
            }

            if(isset($values["display_place_icon"])) {
                $job->setDisplayPlaceIcon($values["display_place_icon"]);
            }

            if(isset($values["display_income"])) {
                $job->setDisplayIncome($values["display_income"]);
            }

            if(isset($values["display_contact"])) {
                $job->setDisplayContact($values["display_contact"]);
            }

            if(isset($values["title_company"])) {
                $job->setTitleCompany($values["title_company"]);
            }

            if(isset($values["title_place"])) {
                $job->setTitlePlace($values["title_place"]);
            }

            if(isset($values["distance_unit"])) {
                $job->setDistanceUnit($values["distance_unit"]);
            }

            if(isset($values["default_radius"])) {
                $job->setDefaultRadius($values["default_radius"]);
            }

            $job->save();

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

        $this->_sendHtml($html);
    }

    /**
     * @param $option
     * @return string
     * @throws Exception
     */
    public function exportAction() {
        if($this->getCurrentOptionValue()) {
            $job = new Job_Model_Job();
            $result = $job->exportAction($this->getCurrentOptionValue());
            
            $this->_download($result, "job-".date("Y-m-d_h-i-s").".yml", "text/x-yaml");
        }
    }

}