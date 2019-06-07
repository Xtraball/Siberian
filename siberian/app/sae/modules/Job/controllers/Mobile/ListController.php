<?php

/**
 * Class Job_Mobile_ListController
 */
class Job_Mobile_ListController extends Application_Controller_Mobile_Default
{
    /**
     * @var int
     */
    public static $pager = 15;

    /**
     *
     */
    public function findallAction()
    {
        $request = $this->getRequest();

        if ($values = Siberian_Json::decode($request->getRawBody())) {
            try {

                if ($value_id = $values['value_id']) {

                    $job = new Job_Model_Job();
                    $job->find($value_id, "value_id");

                    $time = $values["time"];
                    $pull_to_refresh = filter_var($values["pull_to_refresh"], FILTER_VALIDATE_BOOLEAN);
                    $count = $values["count"];
                    $radius = $values["radius"];
                    $distance = $values["distance"];
                    $categories = $values["categories"];
                    $keywords = $values["keywords"];
                    $position = filter_var($values["position"], FILTER_VALIDATE_BOOLEAN);
                    $more_search = filter_var($values["more_search"], FILTER_VALIDATE_BOOLEAN);
                    $limit = ($more_search) ? 100 : self::$pager;

                    $distance_ranges = [1, 5, 10, 20, 50, 75, 100, 150, 200, 500, 1000];
                    if ($radius >= 0) {
                        $radius = $distance_ranges[floor($radius)];
                    }

                    if (!$more_search) {
                        $radius = 1000;
                    }

                    /** Convert to miles */
                    $distance_unit = $job->getDistanceUnit();
                    if ($distance_unit === "mi") {
                        $radius = $radius * 0.621371;
                    }

                    $search_by_distance = false;
                    $latitude = 0;
                    $longitude = 0;

                    $locality = null;
                    if (!$more_search && $values["latitude"] && $values["longitude"]) {
                        $georeverse = Siberian_Google_Geocoding::geoReverse($values["latitude"], $values["longitude"], $this->getApplication()->getGooglemapsKey());
                        if (isset($georeverse["locality"])) {
                            $locality = $georeverse["locality"];
                        }
                        $latitude = $values["latitude"];
                        $longitude = $values["longitude"];

                        $search_by_distance = true;
                        $position = true;
                    }
                    if ($more_search && $values["locality"] != $locality) {
                        $geocode = Siberian_Google_Geocoding::getLatLng(["address" => $values["locality"]], $this->getApplication()->getGooglemapsKey());
                        $locality = $values["locality"];

                        $latitude = $geocode[0];
                        $longitude = $geocode[1];

                        $search_by_distance = true;
                        $position = true;
                    }

                    $place = new Job_Model_Place();
                    $total = $place->findActive(
                        [
                            "value_id" => $value_id,
                            "time" => $time,
                            "pull_to_refresh" => $pull_to_refresh,
                            "is_active" => 1,
                            "search_by_distance" => $search_by_distance,
                            "search_by_distance" => $search_by_distance,
                            "latitude" => $latitude,
                            "longitude" => $longitude,
                            "radius" => $radius,
                            "distance" => $distance,
                            "categories" => $categories,
                            "keywords" => $keywords,
                            "more_search" => $more_search,
                            "position" => $position,
                        ],
                        "place.created_at DESC",
                        [
                            "limit" => null
                        ]
                    );
                    $places = $place->findActive(
                        [
                            "value_id" => $value_id,
                            "time" => $time,
                            "pull_to_refresh" => $pull_to_refresh,
                            "is_active" => 1,
                            "search_by_distance" => $search_by_distance,
                            "latitude" => $latitude,
                            "longitude" => $longitude,
                            "radius" => $radius,
                            "distance" => $distance,
                            "categories" => $categories,
                            "keywords" => $keywords,
                            "more_search" => $more_search,
                            "position" => $position,
                        ],
                        "place.created_at DESC",
                        [
                            "limit" => $limit
                        ]
                    );

                    $collection = [];

                    foreach ($places as $place) {
                        $collection[] = [
                            "id" => $place["place_id"],
                            "title" => $place["name"],
                            "subtitle" => strip_tags($place["description"]),
                            "location" => $place["location"],
                            "icon" => ($place["icon"]) ? $this->getRequest()->getBaseUrl() . "/images/application" . $place["icon"] : $this->getRequest()->getBaseUrl() . "/images/application" . $place["company_logo"],
                            "company_name" => $place["company_name"],
                            "time" => $place["time"],
                            "distance" => $place["distance"],
                        ];
                    }

                }

                $category = new Job_Model_Category();
                $categories = $category->findAll([
                    "job_id" => $job->getId(),
                    "is_active" => true,
                ]);

                $all_categories = [];
                foreach ($categories as $_category) {
                    $all_categories[] = [
                        "id" => $_category->getId(),
                        "title" => $_category->getName(),
                        "subtitle" => $_category->getDescription(),
                        "icon" => ($_category->getIcon()) ? $this->getRequest()->getBaseUrl() . "/images/application" . $_category->getIcon() : null,
                        "keywords" => $_category->getKeywords(),
                    ];
                }

                $company = new Job_Model_Company();
                $companies = $company->findAll([
                    "job_id" => $job->getId(),
                ]);

                $admin_companies = [];
                $customer_id = $this->getSession()->getCustomerId();
                if (!empty($customer_id)) {
                    foreach ($companies as $_company) {
                        $administrators = explode(",", $_company->getAdministrators());
                        if (in_array($customer_id, $administrators)) {
                            $admin_companies[] = [
                                "id" => $_company->getId(),
                                "title" => $_company->getName(),
                                "subtitle" => strip_tags($_company->getDescription()),
                                "location" => $_company->getLocation(),
                                "is_active" => filter_var($_company->getIsActive(), FILTER_VALIDATE_BOOLEAN),
                            ];
                        }
                    }
                }


                $options = [
                    "display_search" => filter_var($job->getDisplaySearch(), FILTER_VALIDATE_BOOLEAN),
                    "display_place_icon" => filter_var($job->getDisplayPlaceIcon(), FILTER_VALIDATE_BOOLEAN),
                    "display_income" => filter_var($job->getDisplayIncome(), FILTER_VALIDATE_BOOLEAN),
                    "distance_unit" => $distance_unit,
                    "default_radius" => $job->getDefaultRadius(),
                    "title_company" => __($job->getTitleCompany()),
                    "title_place" => __($job->getTitlePlace()),
                ];

                $html = [
                    "success" => 1,
                    "collection" => $collection,
                    "options" => $options,
                    "categories" => $all_categories,
                    "locality" => $locality,
                    "admin_companies" => $admin_companies,
                    "more" => (count($total) > ($count + count($places))),
                    "page_title" => $this->getCurrentOptionValue()->getTabbarName(),
                    "social_sharing_active" => (boolean)$this->getCurrentOptionValue()->getSocialSharingIsActive(),
                ];

            } catch (Exception $e) {
                $html = [
                    "error" => 1,
                    "message" => $e->getMessage()
                ];
            }

            $this->_sendJson($html);
        }

    }

    public function findAction()
    {
        if ($data = $this->getRequest()->getParams()) {
            try {

                if (($place_id = $this->getRequest()->getParam('place_id')) && ($value_id = $this->getRequest()->getParam('value_id'))) {
                    $place = new Job_Model_Place();
                    $place = $place->find([
                        "place_id" => $place_id,
                    ]);

                    if ($place) {
                        $company = new Job_Model_Company();
                        $company->find($place->getCompanyId());

                        $job = new Job_Model_Job();
                        $job->find($company->getJobId());

                        $display_contact = ($company->getDisplayContact() != "global") ? $company->getDisplayContact() : $job->getDisplayContact();

                        /** is administrator */
                        $is_admin = false;
                        if ($this->getSession()->getCustomerId()) {
                            $administrators = explode(",", $company->getAdministrators());
                            if (in_array($this->getSession()->getCustomerId(), $administrators)) {
                                $is_admin = true;
                            }
                        }

                        if (!$is_admin) {
                            $place->setViews($place->getViews() + 1)->save();
                        }

                        if (!$is_admin && !$place->getIsActive()) {
                            throw new Exception("This place is inactive.");
                        }

                        $place = [
                            "id" => $place->getId(),
                            "title" => $place->getName(),
                            "subtitle" => $place->getDescription(),
                            "email" => $place->getEmail(),
                            "banner" => ($place->getBanner()) ? $this->getRequest()->getBaseUrl() . "/images/application" . $place->getBanner() : $this->getRequest()->getBaseUrl() . "/app/sae/modules/Job/resources/media/default/job-header.png",
                            "location" => $place->getLocation(),
                            "income_from" => $place->getIncomeFrom(),
                            "income_to" => $place->getIncomeTo(),
                            "company_id" => $place->getCompanyId(),
                            "keywords" => $place->getKeywords(),
                            "display_contact" => $display_contact,
                            "views" => $place->getViews(),
                            "is_active" => filter_var($place->getIsActive(), FILTER_VALIDATE_BOOLEAN),
                            "company" => [
                                "title" => $company->getName(),
                                "subtitle" => strip_tags($company->getDescription()),
                                "location" => $company->getLocation(),
                                "email" => $company->getEmail(),
                                "logo" => ($company->getLogo()) ? $this->getRequest()->getBaseUrl() . "/images/application" . $company->getLogo() : null,
                            ],
                        ];

                        $html = [
                            "success" => 1,
                            "place" => $place,
                            "page_title" => $this->getCurrentOptionValue()->getTabbarName(),
                            "is_admin" => $is_admin,
                            "social_sharing_active" => (boolean)$this->getCurrentOptionValue()->getSocialSharingIsActive(),
                        ];

                    }
                } else {
                    throw new Exception("#567-01: Missing value_id or place_id");
                }

            } catch (Exception $e) {
                $html = [
                    "error" => 1,
                    "message" => $e->getMessage()
                ];
            }

            $this->_sendJson($html);
        }


    }

    public function findcompanyAction()
    {
        if ($data = $this->getRequest()->getParams()) {
            try {

                if (($company_id = $this->getRequest()->getParam('company_id')) && ($value_id = $this->getRequest()->getParam('company_id'))) {
                    $company = new Job_Model_Company();
                    $company = $company->find([
                        "company_id" => $company_id,
                    ]);

                    if ($company) {

                        /** is administrator */
                        $is_admin = false;
                        if ($this->getSession()->getCustomerId()) {
                            $administrators = explode(",", $company->getAdministrators());
                            if (in_array($this->getSession()->getCustomerId(), $administrators)) {
                                $is_admin = true;
                            }
                        }

                        if (!$is_admin) {
                            $company->setViews($company->getViews() + 1)->save();
                        }

                        $place = new Job_Model_Place();

                        if (!$is_admin) {
                            $places = $place->findAll([
                                "company_id = ?" => $company->getId(),
                                "is_active = ?" => 1,
                            ]);
                        } else {
                            $places = $place->findAll([
                                "company_id = ?" => $company->getId(),
                            ]);
                        }


                        $_places = [];
                        foreach ($places as $place) {
                            $_places[] = [
                                "id" => $place->getId(),
                                "title" => $place->getName(),
                                "views" => $place->getViews(),
                                "subtitle" => strip_tags($place->getDescription()),
                                "banner" => ($place->getBanner()) ? $this->getRequest()->getBaseUrl() . "/images/application" . $place->getBanner() : null,
                                "location" => $place->getLocation(),
                                "income_from" => $place->getIncomeFrom(),
                                "income_to" => $place->getIncomeTo(),
                                "is_active" => filter_var($place->getIsActive(), FILTER_VALIDATE_BOOLEAN),
                            ];
                        }

                        $category = new Job_Model_Category();
                        $categories = $category->findAll([
                            "is_active" => true,
                            "job_id" => $company->getJobId(),
                        ]);

                        $all_categories[] = [
                            "id" => "",
                            "title" => __("None")
                        ];
                        foreach ($categories as $_category) {
                            $all_categories[] = [
                                "id" => $_category->getId(),
                                "title" => $_category->getName(),
                            ];
                        }

                        $company = [
                            "id" => $company->getId(),
                            "title" => $company->getName(),
                            "subtitle" => htmlspecialchars_decode($company->getDescription()),
                            "logo" => ($company->getLogo()) ? $this->getRequest()->getBaseUrl() . "/images/application" . $company->getLogo() : null,
                            "header" => ($company->getHeader()) ? $this->getRequest()->getBaseUrl() . "/images/application" . $company->getHeader() : null,
                            "location" => $company->getLocation(),
                            "employee_count" => $company->getEmployeeCount(),
                            "website" => $company->getWebsite(),
                            "email" => $company->getEmail(),
                            "views" => $company->getViews(),
                            "places" => $_places,
                            "is_active" => filter_var($company->getIsActive(), FILTER_VALIDATE_BOOLEAN),
                        ];

                        $html = [
                            "success" => 1,
                            "company" => $company,
                            "categories" => $all_categories,
                            "is_admin" => $is_admin,
                            "page_title" => $this->getCurrentOptionValue()->getTabbarName(),
                        ];

                    }
                } else {
                    throw new Exception("#567-02: Missing value_id or company_id");
                }

            } catch (Exception $e) {
                $html = [
                    "error" => 1,
                    "message" => $e->getMessage()
                ];
            }

            $this->_sendJson($html);
        }


    }


    public function contactformAction()
    {
        $request = $this->getRequest();

        if ($values = Siberian_Json::decode($request->getRawBody())) {

            try {

                if (($value_id = $values['value_id']) && ($place_id = $values['place_id'])) {

                    $place = new Job_Model_Place();
                    $place->find($place_id);

                    if ($place->getId()) {
                        $place_email = $place->getEmail();
                        $place_title = $place->getName();

                        $fullname = $values["fullname"];
                        $email = $values["email"];
                        $message = $values["message"];
                        $phone = $values["phone"];
                        $address = $values["address"];

                        $layout = Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->getLayoutInstance()->loadEmail('job', 'contact_form');
                        $layout
                            ->getPartial('content_email')
                            ->setPlaceTitle($place_title)
                            ->setFullname($fullname)
                            ->setEmail($email)
                            ->setPhone($phone)
                            ->setAddress($address)
                            ->setMessage($message);

                        $content = $layout->render();

                        if ($email AND $fullname) {
                            /** Mail to place */

                            # @version 4.8.7 - SMTP
                            $mail = new Siberian_Mail();
                            $mail->setBodyHtml($content);
                            $mail->setFrom($email, $fullname);
                            $mail->addTo($place_email);
                            $mail->setSubject(__("New contact for: %s", $place_title));
                            $mail->send();

                            $place_contact = new Job_Model_PlaceContact();
                            $place_contact->addData($values);
                            $place_contact->setData("customer_id", $this->getSession()->getCustomerId());
                            $place_contact->save();

                            $html = [
                                "success" => 1,
                                "message" => __("Email successfully sent."),
                            ];

                        }
                    }
                }

            } catch (Exception $e) {
                $html = [
                    "error" => 1,
                    "message" => $e->getMessage()
                ];
            }
        } else {
            $html = [
                "error" => 1,
                "message" => __("#567-03: Missing value_id or place_id.")
            ];
        }

        if (empty($html)) {
            $html = [
                "error" => 1,
                "message" => __("#567-04: An error occured.")
            ];
        }

        $this->_sendJson($html);

    }

    public function editplaceAction()
    {
        $request = $this->getRequest();

        if ($values = Siberian_Json::decode($request->getRawBody())) {

            try {

                if (($value_id = $values['value_id']) && ($place_id = $values['place_id'])) {

                    $place = new Job_Model_Place();
                    $place->find($place_id);

                    if ($place->getId()) {

                        $company = new Job_Model_Company();
                        $company = $company->find($place->getCompanyId());

                        /** is administrator */
                        $is_admin = false;
                        if ($this->getSession()->getCustomerId()) {
                            $administrators = explode(",", $company->getAdministrators());
                            if (in_array($this->getSession()->getCustomerId(), $administrators)) {
                                $is_admin = true;
                            }
                        }

                        if (!$is_admin) {
                            throw new Exception("You are not allowed to edit this Place");
                        }

                        $place->setName($values["title"]);
                        $place->setEmail($values["email"]);
                        $place->setKeywords($values["keywords"]);
                        $place->setIsActive(filter_var($values["is_active"], FILTER_VALIDATE_BOOLEAN));

                        /** Geocoding */
                        if (!empty($values["location"])) {
                            $coordinates = Siberian_Google_Geocoding::getLatLng(["address" => $values["location"]], $this->getApplication()->getGooglemapsKey());
                            $place->setData("latitude", $coordinates[0]);
                            $place->setData("longitude", $coordinates[1]);
                            $place->setLocation($values["location"]);
                        }

                        $place->save();

                        $html = [
                            "success" => 1,
                            "message" => __("Place saved.")
                        ];

                    }
                }

            } catch (Exception $e) {
                $html = [
                    "error" => 1,
                    "message" => $e->getMessage()
                ];
            }
        } else {
            $html = [
                "error" => 1,
                "message" => __("#567-07: Missing value_id or place_id.")
            ];
        }

        $this->_sendJson($html);

    }

    public function createplaceAction()
    {
        $request = $this->getRequest();
        $values = Siberian_Json::decode($request->getRawBody());

        $form = new Job_Form_Place();

        /** Remove icon element */
        $form->getElement("banner")->setRequired(false);

        if ($form->isValid($values)) {

            try {

                $company = new Job_Model_Company();
                $company->find($values["company_id"]);

                /** is administrator */
                $is_admin = false;
                if ($this->getSession()->getCustomerId()) {
                    $administrators = explode(",", $company->getAdministrators());
                    if (in_array($this->getSession()->getCustomerId(), $administrators)) {
                        $is_admin = true;
                    }
                }

                if (!$is_admin) {
                    throw new Exception("You are not allowed to create a place for this company");
                }

                $place = new Job_Model_Place();
                $place->setData($values);

                $place->setData("description", nl2br($values["description"]));

                /** Geocoding */
                if (!empty($values["location"])) {
                    $coordinates = Siberian_Google_Geocoding::getLatLng(["address" => $values["location"]], $this->getApplication()->getGooglemapsKey());
                    $place->setData("latitude", $coordinates[0]);
                    $place->setData("longitude", $coordinates[1]);
                }

                $place->save();

                $html = [
                    "success" => 1,
                    "message" => __("The place is successfully created.")
                ];

            } catch (Exception $e) {
                $html = [
                    "error" => 1,
                    "message" => $e->getMessage()
                ];
            }
        } else {
            $html = [
                "error" => 1,
                "message" => $form->getTextErrors(),
                "errors" => $form->getTextErrors(true)
            ];
        }

        $this->_sendJson($html);
    }

    public function editcompanyAction()
    {
        $request = $this->getRequest();

        if ($values = Siberian_Json::decode($request->getRawBody())) {

            try {

                if (($value_id = $values['value_id']) && ($company_id = $values['company_id'])) {

                    $company = new Job_Model_Company();
                    $company->find($company_id);

                    if ($company->getId()) {

                        /** is administrator */
                        $is_admin = false;
                        if ($this->getSession()->getCustomerId()) {
                            $administrators = explode(",", $company->getAdministrators());
                            if (in_array($this->getSession()->getCustomerId(), $administrators)) {
                                $is_admin = true;
                            }
                        }

                        if (!$is_admin) {
                            throw new Exception("You are not allowed to edit this Company");
                        }

                        $company->setName($values["title"]);
                        $company->setWebsite($values["website"]);
                        $company->setEmail($values["email"]);
                        $company->setEmployeeCount($values["employee_count"]);
                        $company->setDisplayContact($values["display_contact"]);

                        /** Geocoding */
                        if (!empty($values["location"])) {
                            $coordinates = Siberian_Google_Geocoding::getLatLng(["address" => $values["location"]], $this->getApplication()->getGooglemapsKey());
                            $company->setData("latitude", $coordinates[0]);
                            $company->setData("longitude", $coordinates[1]);
                            $company->setLocation($values["location"]);
                        }

                        $company->save();

                        $html = [
                            "success" => 1,
                            "message" => __("Company saved.")
                        ];

                    }
                }

            } catch (Exception $e) {
                $html = [
                    "error" => 1,
                    "message" => $e->getMessage()
                ];
            }
        } else {
            $html = [
                "error" => 1,
                "message" => __("#567-09: Missing value_id or company_id.")
            ];
        }

        $this->_sendJson($html);

    }

    /**
     *
     */
    public function fetchSettingsAction ()
    {
        try {
            $optionValue = $this->getCurrentOptionValue();

            // Set default settings
            $defaults = [
                "default_page" => (string) "places",
                "default_layout" => (string) "place-100",
                "distance_unit" => (string) "km",
                "listImagePriority" => (string) "thumbnail",
                "defaultPin" => (string) "pin",
                "categories" => []
            ];

            if (!$optionValue->getId()) {
                $settings = $defaults;
            } else {
                try {
                    $settings = Json::decode($optionValue->getSettings());
                } catch (\Exception $e) {
                    $settings = $defaults;
                }

                $categories = (new Places_Model_Category())
                    ->findAll(["value_id" => $optionValue->getId()], "position ASC");

                $settings["categories"] = [];
                foreach ($categories as $category) {
                    $settings["categories"][] = [
                        'id' => (integer) $category->getId(),
                        'title' => (string) $category->getTitle(),
                        'subtitle' => (string) $category->getSubtitle(),
                        'picture' => (string) $category->getPicture(),
                    ];
                }
            }

            $payload = [
                "success" => true,
                "settings" => $settings,
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }
}