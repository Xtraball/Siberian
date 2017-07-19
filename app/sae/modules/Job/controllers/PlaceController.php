<?php

class Job_PlaceController extends Application_Controller_Default {

    /**
     *
     */
    public function loadformAction() {
        $place_id = $this->getRequest()->getParam("place_id");

        $place = new Job_Model_Place();
        $place->find($place_id);
        if($place->getId()) {
            $form = new Job_Form_Place();
            $form->populate($place->getData());
            $form->setValueId($this->getCurrentOptionValue()->getId());
            $form->removeNav("job-place-nav");
            $form->addNav("job-place-edit-nav", "Save", false);
            $form->setPlaceId($place->getId());

            $company = new Job_Model_Company();
            $job_id = $company->find($place->getCompanyId())->getJobId();
            $companies = $company->findAll(array(
                "job_id" => $job_id,
                "is_active" => true,
            ));
            $company_options = array();
            foreach($companies as $_company) {
                $company_options[$_company->getId()] = $_company->getName();
            }

            if(!empty($company_options)) {
                $form->getElement("company_id")->addMultiOptions($company_options);
            }

            $category = new Job_Model_Category();
            $categories = $category->findAll(array(
                "job_id" => $job_id,
                "is_active" => true,
            ));
            $category_options = array();
            foreach($categories as $_category) {
                $category_options[$_category->getId()] = $_category->getName();
            }
            if(!empty($company_options)) {
                $form->getElement("category_id")->addMultiOptions($category_options);
            }

            $html = array(
                "success" => 1,
                "form" => $form->render(),
                "message" => __("Success."),
            );
        } else {
            /** Do whatever you need when form is not valid */
            $html = array(
                "error" => 1,
                "message" => __("The place you are trying to edit doesn't exists."),
            );
        }

        $this->_sendJson($html);
    }

    /**
     * Create/Edit place
     *
     * @throws exception
     */
    public function editpostAction() {
        $values = $this->getRequest()->getPost();

        $form = new Job_Form_Place();
        if($form->isValid($values)) {
            /** Do whatever you need when form is valid */
            $place = new Job_Model_Place();
            $place
                ->addData($values)
                ->addData(array(
                    "is_active" => true,
                ))
            ;

            if($values["banner"] == "_delete_") {
                $place->setData("banner", "");
            } else if(file_exists(Core_Model_Directory::getBasePathTo("images/application".$values["banner"]))) {
                # Nothing changed, skip
            } else {
                $path_banner = Siberian_Feature::moveUploadedFile($this->getCurrentOptionValue(), Core_Model_Directory::getTmpDirectory()."/".$values['banner'], $values['banner']);
                $place->setData("banner", $path_banner);
            }

            if($values["icon"] == "_delete_") {
                $place->setData("icon", "");
            } else if(file_exists(Core_Model_Directory::getBasePathTo("images/application".$values["icon"]))) {
                # Nothing changed, skip
            } else {
                $path_icon = Siberian_Feature::moveUploadedFile($this->getCurrentOptionValue(), Core_Model_Directory::getTmpDirectory()."/".$values["icon"]);
                $place->setData("icon", $path_icon);
            }


            /** Geocoding */
            if(!empty($values["location"])) {
                $coordinates = Siberian_Google_Geocoding::getLatLng(array("address" => $values["location"]));
                $place->setData("latitude", $coordinates[0]);
                $place->setData("longitude", $coordinates[1]);
            }


            $place->save();

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
                "errors" => $form->getTextErrors(true),
            );
        }

        $this->_sendJson($html);
    }

    /**
     * Toggle place
     */
    public function togglepostAction() {
        $values = $this->getRequest()->getPost();

        $form = new Job_Form_Place_Toggle();
        if($form->isValid($values)) {
            $place = new Job_Model_Place();
            $result = $place->find($values["place_id"])->toggle();

            /** Update touch date, then never expires (until next touch) */
            $this->getCurrentOptionValue()
                ->touch()
                ->expires(-1);

            $html = array(
                "success" => 1,
                "state" => $result,
                "message" => ($result) ? __("Place enabled") : __("Place disabled"),
            );
        } else {
            /** Do whatever you need when form is not valid */
            $html = array(
                "error" => 1,
                "message" => $form->getTextErrors(),
                "errors" => $form->getTextErrors(true),
            );
        }

        $this->_sendJson($html);
    }

    /**
     * Delete place
     */
    public function deletepostAction() {
        $values = $this->getRequest()->getPost();

        $form = new Job_Form_Place_Delete();
        if($form->isValid($values)) {
            $place = new Job_Model_Company();
            $place->find($values["place_id"]);
            $place->delete();

            /** Update touch date, then never expires (until next touch) */
            $this->getCurrentOptionValue()
                ->touch()
                ->expires(-1);

            $html = array(
                'success' => 1,
                'success_message' => __('Place successfully deleted.'),
                'message_loader' => 0,
                'message_button' => 0,
                'message_timeout' => 2
            );
        } else {
            $html = array(
                "error" => 1,
                "message" => $form->getTextErrors(),
                "errors" => $form->getTextErrors(true),
            );
        }

        $this->_sendJson($html);
    }


}