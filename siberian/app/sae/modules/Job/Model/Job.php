<?php

class Job_Model_Job extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Job_Model_Db_Table_Job';
        return $this;
    }

    /**
     * Creates the root option used for options
     *
     * @param $option_value
     * @return $this
     */
    public function prepareFeature($option_value) {

        parent::prepareFeature($option_value);

        if (!$this->getId()) {
            $this->setValueId($option_value->getId())->save();
        }

        return $this;
    }

    /**
     * @param $option
     * @return string
     * @throws Exception
     */
    public function exportAction($option, $export_type = null) {
        if($option && $option->getId()) {

            $current_option = $option;
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

            $data_places = array();
            if(!empty($companies_id)) {
                $places = $place_model->findAll(array(
                    "company_id IN (?)" => array_values($companies_id),
                ));

                $places_id = array();
                foreach($places as $place) {
                    $places_id[] = $place->getId();

                    $place_data = $place->getData();
                    $place_data["icon"] = $place->_getIcon();
                    $place_data["banner"] = $place->_getBanner();

                    $data_places[] = $place_data;
                }
            }

            $data_place_contacts = array();
            if(!empty($places_id)) {
                $place_contacts = $place_contact_model->findAll(array(
                    "place_id IN (?)" => array_values($places_id),
                ));

                foreach($place_contacts as $place_contact) {
                    $data_place_contacts[] = $place_contact->getData();
                }
            }

            $dataset = array(
                "option" => $current_option->getData(),
                "job" => $job->getData(),
                "categories" => $data_categories,
                "companies" => $data_companies,
                "places" => $data_places,
                "place_contacts" => $data_place_contacts,
            );

            try {
                $result = Siberian_Yaml::encode($dataset);
            } catch(Exception $e) {
                throw new Exception("#087-03: An error occured while exporting dataset to YAML.");
            }

            return $result;

        } else {
            throw new Exception("#087-01: Unable to export the feature, non-existing id.");
        }
    }

    /**
     * @param $path
     * @throws Exception
     */
    public function importAction($path) {
        $content = file_get_contents($path);

        try {
            $dataset = Siberian_Yaml::decode($content);
        } catch(Exception $e) {
            throw new Exception("#087-04: An error occured while importing YAML dataset '$path'.");
        }

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
    }
}