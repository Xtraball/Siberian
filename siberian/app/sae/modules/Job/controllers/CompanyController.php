<?php

class Job_CompanyController extends Application_Controller_Default {

    /**
     * Load form edit
     */
    public function loadformAction() {
        $company_id = $this->getRequest()->getParam("company_id");
        $application = $this->getApplication();

        $company = new Job_Model_Company();
        $company->find($company_id);
        if($company->getId()) {
            $form = new Job_Form_Company([], $application->getId());

            $form->populate($company->getData());
            $form->setValueId($this->getCurrentOptionValue()->getId());
            $form->removeNav("job-company-nav");
            $form->addNav("job-company-edit-nav", "Save", false);
            $form->setCompanyId($company->getId());

            $form->getElement("administrators")->setValue(explode(",", $company->getData("administrators")));

            $form->getElement('description')->setAttrib('id', 'company-edit-description-' . $company->getId());

            $html = [
                "success" => 1,
                "form" => $form->render(),
                "message" => __("Success."),
            ];
        } else {
            /** Do whatever you need when form is not valid */
            $html = [
                "error" => 1,
                "message" => __("The company you are trying to edit doesn't exists."),
            ];
        }

        $this->_sendJson($html);
    }

    /**
     * Create/Edit Company
     *
     * @throws exception
     */
    public function editpostAction() {
        $values = $this->getRequest()->getPost();
        $application = $this->getApplication();

        $form = new Job_Form_Company([], $application->getId());
        if($form->isValid($values)) {
            /** Do whatever you need when form is valid */
            $company = new Job_Model_Company();
            $company->addData($values);

            $company->setData("is_active", true);

            if(file_exists(Core_Model_Directory::getBasePathTo("images/application".$values["logo"]))) {
                # Nothing changed, skip
            } else {
                $path_logo = Siberian_Feature::moveUploadedFile($this->getCurrentOptionValue(), Core_Model_Directory::getTmpDirectory()."/".$values['logo']);
                $company->setData("logo", $path_logo);
            }

            if(file_exists(Core_Model_Directory::getBasePathTo("images/application".$values["header"]))) {
                # Nothing changed, skip
            } else {
                $path_header = Siberian_Feature::moveUploadedFile($this->getCurrentOptionValue(), Core_Model_Directory::getTmpDirectory()."/".$values['header']);
                $company->setData("header", $path_header);
            }

            $company->setData("administrators", implode_polyfill(",", $company->getData("administrators")));

            /** Password */
            if(!empty($values["password"]) && ($values["password"] != "_remove_")) {
                $company->setData("password", sha1($values["password"]));
            } else if($values["password"] == "_remove_") {
                $company->setData("password", "");
            }

            /** Geocoding */
            if(!empty($values["location"])) {
                $coordinates = Siberian_Google_Geocoding::getLatLng(["address" => $values["location"]],
                    $this->getApplication()->getGooglemapsKey());
                $company->setData("latitude", $coordinates[0]);
                $company->setData("longitude", $coordinates[1]);
            }

            $company->save();

            /** Update touch date, then never expires (until next touch) */
            $this->getCurrentOptionValue()
                ->touch()
                ->expires(-1);

            $html = [
                "success" => 1,
                "message" => __("Success."),
            ];
        } else {
            /** Do whatever you need when form is not valid */
            $html = [
                "error" => 1,
                "message" => $form->getTextErrors(),
                "errors" => $form->getTextErrors(true),
            ];
        }

        $this->_sendJson($html);
    }

    public function togglepostAction() {
        $values = $this->getRequest()->getPost();

        $form = new Job_Form_Company_Toggle();

        if($form->isValid($values)) {
            $company = new Job_Model_Company();
            $result = $company->find($values["company_id"])->toggle();

            /** Update touch date, then never expires (until next touch) */
            $this->getCurrentOptionValue()
                ->touch()
                ->expires(-1);

            $html = [
                "success" => 1,
                "state" => $result,
                "message" => ($result) ? __("Company enabled") : __("Company disabled"),
            ];
        } else {
            /** Do whatever you need when form is not valid */
            $html = [
                "error" => 1,
                "message" => $form->getTextErrors(),
                "errors" => $form->getTextErrors(true),
            ];
        }

        $this->_sendJson($html);
    }

    /**
     * Delete company
     */
    public function deletepostAction() {
        $values = $this->getRequest()->getPost();

        $form = new Job_Form_Place_Delete();
        if($form->isValid($values)) {
            $company = new Job_Model_Company();
            $company->find($values["company_id"]);

            $company->delete();

            /** Update touch date, then never expires (until next touch) */
            $this->getCurrentOptionValue()
                ->touch()
                ->expires(-1);

            $html = [
                'success' => 1,
                'success_message' => __('Company successfully deleted.'),
                'message_loader' => 0,
                'message_button' => 0,
                'message_timeout' => 2
            ];
        }else{
            $html = [
                "error" => 1,
                "message" => $form->getTextErrors(),
                "errors" => $form->getTextErrors(true),
            ];
        }

        $this->_sendJson($html);
    }


}