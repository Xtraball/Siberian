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

            $job->save();

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

    /***
     *
     */
    public function exportAction() {
        if($this->getCurrentOptionValue()) {

            $current_option = $this->getCurrentOptionValue();
            $value_id = $current_option->getId();

            $job_model = new Job_Model_Job();
            $company_model = new Job_Model_Company();
            $category_model = new Job_Model_Category();
            $place_model = new Job_Model_Place();
            $place_contact_model = new Job_Model_PlaceContact();

            $job = $job_model->find($value_id, "value_id");

            $categories = $category_model->findAll(array(
                "job_id = ?" => $job->getId(),
            ));
            $data_categories = array();
            foreach($categories as $category) {

                $category_data = $category->getData();
                $category_data["icon"] = $category->_getIcon();

                $data_categories[] = $category_data;
            }

            $companies = $company_model->findAll(array(
                "job_id = ?" => $job->getId(),
            ));

            $companies_id = array();
            $data_companies = array();
            foreach($companies as $company) {
                $companies_id[] = $company->getId();

                $company_data = $company->getData();
                $company_data["logo"] = $company->_getLogo();
                $company_data["header"] = $company->_getHeader();

                $data_companies[] = $company_data;
            }

            $places = $place_model->findAll(array(
                "company_id IN (?)" => array_values($companies_id),
            ));

            $places_id = array();
            $data_places = array();
            foreach($places as $place) {
                $places_id[] = $place->getId();

                $place_data = $place->getData();
                $place_data["icon"] = $place->_getIcon();
                $place_data["banner"] = $place->_getBanner();

                $data_places[] = $place_data;
            }

            $place_contacts = $place_contact_model->findAll(array(
                "place_id IN (?)" => array_values($places_id),
            ));

            $data_place_contacts = array();
            foreach($place_contacts as $place_contact) {
                $data_place_contacts[] = $place_contact->getData();
            }

            $dataset = array(
                "option" => $current_option->getData(),
                "job" => $job->getData(),
                "categories" => $data_categories,
                "companies" => $data_companies,
                "places" => $data_places,
                "place_contacts" => $data_place_contacts,
            );

            $output = Siberian_Yaml::encode($dataset);

            echo $output;
            die;

        } else {
            throw new Exception("#087-01: Unable to export the feature, non-existing id.");
        }
    }

    /***
     *
     */
    public function importAction() {
        $content = file_get_contents(Core_Model_Directory::getBasePathTo("var/tmp/job.yml"));
        $dataset = Siberian_Yaml::decode($content);

        $application = $this->getApplication();

        $application_option = new Application_Model_Option_Value();
        $job_model = new Job_Model_Job();

        if(isset($dataset["option"])) {
            $new_application_option = $application_option
                ->setData($dataset["option"])
                ->unsData("value_id")
                ->unsData("id")
                ->setData('app_id', $application->getId())
                ->save()
            ;

            $new_value_id = $new_application_option->getId();

            /** Create Job/Options */
            if(isset($dataset["job"]) && $new_value_id) {
                $new_job = $job_model
                    ->setData($dataset["job"])
                    ->unsData("job_id")
                    ->unsData("id")
                    ->setData("value_id", $new_value_id)
                    ->save()
                ;

                /** Insert categories */
                $match_category_ids = array();
                if(isset($dataset["categories"]) && $new_job->getId()) {

                    foreach($dataset["categories"] as $category) {

                        $new_category = new Job_Model_Category();
                        $new_category
                            ->setData($category)
                            ->unsData("category_id")
                            ->unsData("id")
                            ->setData("job_id", $new_job->getId())
                            ->_setIcon($category["icon"], $new_application_option)
                            ->save()
                        ;

                        $match_category_ids[$category["category_id"]] = $new_category->getId();
                    }

                } else {
                    /** Log, empty categories */
                }

                /** Insert companies */
                $match_company_ids = array();
                if(isset($dataset["companies"]) && $new_job->getId()) {

                    foreach($dataset["companies"] as $company) {

                        $new_company = new Job_Model_Company();
                        $new_company
                            ->setData($company)
                            ->unsData("company_id")
                            ->unsData("id")
                            ->unsData("administrators") /** clear admins */
                            ->setData("job_id", $new_job->getId())
                            ->_setLogo($company["logo"], $new_application_option)
                            ->_setHeader($company["header"], $new_application_option)
                            ->save()
                        ;

                        $match_company_ids[$company["company_id"]] = $new_company->getId();
                    }

                } else {
                    /** Log, empty categories */
                }

                /** Insert places */
                $match_place_ids = array();
                if(isset($dataset["places"]) && $new_job->getId()) {

                    foreach($dataset["places"] as $place) {

                        $old_category_id = $place["category_id"];
                        $old_company_id = $place["company_id"];

                        $category_id = (isset($match_category_ids[$old_category_id])) ? $match_category_ids[$old_category_id] : null;

                        if(isset($match_company_ids[$old_company_id])) {
                            $new_place = new Job_Model_Place();
                            $new_place
                                ->setData($place)
                                ->unsData("place_id")
                                ->unsData("id")
                                ->setData("category_id", $category_id)
                                ->setData("company_id", $match_company_ids[$old_company_id])
                                ->_setIcon($place["icon"], $new_application_option)
                                ->_setBanner($place["banner"], $new_application_option)
                                ->save()
                            ;

                            $match_place_ids[$place["place_id"]] = $new_place->getId();
                        } else {
                            /** Log, no matching company */
                        }

                    }

                } else {
                    /** Log, empty categories */
                }

                /** Insert place contacts */
                if(isset($dataset["place_contacts"]) && $new_job->getId()) {

                    foreach($dataset["place_contacts"] as $place_contact) {

                        $old_place_id = $place_contact["place_id"];

                        if(isset($match_place_ids[$old_place_id])) {
                            $new_place_contact = new Job_Model_PlaceContact();
                            $new_place_contact
                                ->setData($place_contact)
                                ->unsData("place_contact_id")
                                ->unsData("id")
                                ->setData("place_id", $match_place_ids[$old_place_id])
                                ->save()
                            ;

                        } else {
                            /** Log, no matching place */
                        }

                    }

                } else {
                    /** Log, empty categories */
                }

            } else {
                /** Log, empty feature/default */
            }


        } else {
            throw new Exception("#087-02: Missing option, unable to import data.");
        }

        $this->_sendHtml(array(
            "success" => 1,
            "message" => __("Successful import for JOB template"),
        ));
    }

}