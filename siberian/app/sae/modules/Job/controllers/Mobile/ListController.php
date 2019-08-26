<?php

use Siberian\Layout;
use Siberian_Google_Geocoding as Geocoding;
use Core\Model\Base;

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
        try {
            $request = $this->getRequest();
            $values = $request->getBodyParams();

            $optionValue = $this->getCurrentOptionValue();
            $valueId = $optionValue->getId();

            $job = new Job_Model_Job();
            $job->find($valueId, "value_id");

            if (!$job->getId()) {
                throw new \Siberian\Exception(
                    p__("job", "This feature doesn't exists."));
            }

            $radius = $values["radius"];
            $categories = $values["categories"];
            $fulltext = $values["fulltext"];
            $keywords = $values["keywords"];
            $offset = $values["offset"];

            $position = [
                "latitude" => $values["latitude"],
                "longitude" => $values["longitude"]
            ];

            $distanceRanges = [1, 5, 10, 20, 50, 75, 100, 150, 200, 500, 1000];
            if ($radius >= 0) {
                $radiusIndex = (integer) floor($radius);
                $radius = $distanceRanges[$radiusIndex];
            }

            $sortingType = "distance";
            $params = [
                "offset" => $offset,
                "limit" => 20,
                "fulltext" => $fulltext,
                "radius" => $radius,
                "categories" => $categories,
                "keywords" => $keywords,
                "sortingType" => $sortingType,
            ];

            $place = new Job_Model_Place();

            $places = $place->findAllWithFilters($valueId, [
                "search_by_distance" => true,
                "latitude" => $position["latitude"],
                "longitude" => $position["longitude"],
            ], $params);

            $totalParams = $params;
            unset($totalParams["offset"]);
            unset($totalParams["limit"]);
            $total = $place->findAllWithFilters($valueId, [
                "search_by_distance" => true,
                "latitude" => $position["latitude"],
                "longitude" => $position["longitude"],
            ], $totalParams);

            $collection = [];
            foreach ($places as $place) {
                $collection[] = [
                    "id" => (integer) $place->getId(),
                    "title" => (string) $place->getName(),
                    "subtitle" => (string) strip_tags($place->getDescription()),
                    "location" => $place->getLocation(),
                    "icon" => ($place->getData("icon")) ?
                        $this->getRequest()->getBaseUrl() . "/images/application" . $place->getData("icon") :
                        $this->getRequest()->getBaseUrl() . "/images/application" . $place->getCompanyLogo(),
                    "company_name" => $place->getCompanyName(),
                    "distance" => $place->getDistance(),
                ];
            }

            $payload = [
                "success" => true,
                "sortingType" => $sortingType,
                "page_title" => $optionValue->getTabbarName(),
                "displayed_per_page" => sizeof($collection),
                "socialSharing" => (boolean) $optionValue->getSocialSharingIsActive(),
                "total" => $total->count(),
                "places" => $collection
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }
        
        $this->_sendJson($payload);
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

                        $currency = $job->getCurrency();

                        $display_contact = ($company->getDisplayContact() !== "global" && !empty($company->getDisplayContact())) ?
                            $company->getDisplayContact() : $job->getDisplayContact();

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

                        $contacts = (new Job_Model_PlaceContact())->findAll(["place_id = ?" => $place->getId()]);

                        $place = [
                            "id" => (integer) $place->getId(),
                            "title" => (string) $place->getName(),
                            "subtitle" => (string) $place->getDescription(),
                            "email" => (string) $place->getEmail(),
                            "banner" => (string) ($place->getBanner()) ? $this->getRequest()->getBaseUrl() . "/images/application" . $place->getBanner() : $this->getRequest()->getBaseUrl() . "/app/sae/modules/Job/resources/media/default/job-header.png",
                            "location" => (string) $place->getLocation(),
                            "income_from" => (string) Base::_formatPrice($place->getIncomeFrom(), $currency, ["precision" => 0]),
                            "income_to" => (string) Base::_formatPrice($place->getIncomeTo(), $currency, ["precision" => 0]),
                            "company_id" => (integer) $place->getCompanyId(),
                            "keywords" => (string) $place->getKeywords(),
                            "display_contact" => (string) $display_contact,
                            "views" => (integer) $place->getViews(),
                            "contacts" => (integer) $contacts->count(),
                            "is_active" => (boolean) filter_var($place->getIsActive(), FILTER_VALIDATE_BOOLEAN),
                            "company" => [
                                "title" => (string) $company->getName(),
                                "subtitle" => (string) strip_tags($company->getDescription()),
                                "location" => (string) $company->getLocation(),
                                "email" => (string) $company->getEmail(),
                                "logo" => (string) ($company->getLogo()) ? $this->getRequest()->getBaseUrl() . "/images/application" . $company->getLogo() : null,
                            ],
                        ];

                        $html = [
                            "success" => true,
                            "place" => $place,
                            "page_title" => $this->getCurrentOptionValue()->getTabbarName(),
                            "is_admin" => $is_admin,
                            "socialSharing" => (boolean) $this->getCurrentOptionValue()->getSocialSharingIsActive(),
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
                            "id" => (integer) $company->getId(),
                            "title" => (string) $company->getName(),
                            "subtitle" => (string) htmlspecialchars_decode($company->getDescription()),
                            "logo" => (string) ($company->getLogo()) ? $this->getRequest()->getBaseUrl() . "/images/application" . $company->getLogo() : null,
                            "header" => (string) ($company->getHeader()) ? $this->getRequest()->getBaseUrl() . "/images/application" . $company->getHeader() : null,
                            "location" => (string) $company->getLocation(),
                            "employee_count" => (integer) $company->getEmployeeCount(),
                            "website" => (string) $company->getWebsite(),
                            "email" => (string) $company->getEmail(),
                            "views" => (integer) $company->getViews(),
                            "places" => $_places,
                            "is_active" => filter_var($company->getIsActive(), FILTER_VALIDATE_BOOLEAN),
                        ];

                        $html = [
                            "success" => true,
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

                $optionValue = $this->getCurrentOptionValue();

                if (($value_id = $values['value_id']) && ($place_id = $values['place_id'])) {

                    $place = new Job_Model_Place();
                    $place->find($place_id);

                    if ($place->getId()) {
                        $place_email = $place->getEmail();
                        $place_title = $place->getName();

                        $fullName = $values["fullname"];
                        $email = $values["email"];

                        if ($email && $fullName) {
                            try {
                                // E-Mail back the user!
                                $subject = sprintf("%s - %s: %s",
                                    $optionValue->getTabbarName(),
                                    p__("job", "New contact request for the offer"),
                                    $place_title);


                                $baseEmail = $this->baseEmail("contact_form", $subject, "", false);

                                foreach ($values as $key => $value) {
                                    $baseEmail->setContentFor("content_email", $key, $value);
                                }
                                $baseEmail->setContentFor("content_email", "place_title", $place_title);
                                $baseEmail->setContentFor("content_email", "customer_id", $this->getSession()->getCustomerId());

                                $content = $baseEmail->render();

                                $mail = new \Siberian_Mail();

                                // Adds all attached resume/images
                                foreach ($values["resumes"] as $index => $resume) {
                                    preg_match("#^data:(.*);base64,#", $resume, $matches);
                                    $rawBase64 = preg_replace("#^(data:(.*);base64,)#", "", $resume);
                                    $mime = $matches[0];

                                    $ext = (strpos($mime, "png") !== false) ? "png" : "jpg";

                                    $attachment = new Zend_Mime_Part(base64_decode($rawBase64));
                                    $attachment->type = $mime;
                                    $attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
                                    $attachment->encoding = Zend_Mime::ENCODING_BASE64;
                                    $attachment->filename = "resume-{$index}.{$ext}";

                                    $mail->addAttachment($attachment);
                                }
                                // Unset resumes, we don't want to show them in e-mail text!
                                unset($values["resumes"]);

                                $mail->setBodyHtml($content);
                                $mail->setFrom($email, $fullName);
                                $mail->addTo($place_email);
                                $mail->setSubject($subject);
                                $mail->send();
                            } catch (\Exception $e) {
                                // Silently fails!
                            }

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
                            $coordinates = Geocoding::getLatLng(["address" => $values["location"]], $this->getApplication()->getGooglemapsKey());
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
            $valueId = $optionValue->getId();

            $job = new Job_Model_Job();
            $job->find($valueId, "value_id");

            if (!$job->getId()) {
                throw new \Siberian\Exception(
                    p__("job", "This feature doesn't exists."));
            }

            $category = new Job_Model_Category();
            $categories = $category->findAll([
                "job_id" => $job->getId(),
                "is_active" => true,
            ]);

            $all_categories = [];
            foreach ($categories as $_category) {
                $all_categories[] = [
                    "id" => (integer) $_category->getId(),
                    "title" => (string) $_category->getName(),
                    "subtitle" => (string) $_category->getDescription(),
                    "icon" => (string) $_category->getIcon(),
                    "keywords" => (string) $_category->getKeywords(),
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

            $settings = [
                "display_place_icon" => (string) filter_var($job->getDisplayPlaceIcon(), FILTER_VALIDATE_BOOLEAN),
                "display_income" => (string) filter_var($job->getDisplayIncome(), FILTER_VALIDATE_BOOLEAN),
                "distance_unit" => (string) $job->getDistanceUnit(),
                "cardDesign" => (boolean) ($job->getCardDesign() === "card"),
                "default_radius" => (string) $job->getDefaultRadius(),
                "title_company" => (string) __($job->getTitleCompany()),
                "title_place" => (string) __($job->getTitlePlace()),
                "categories" => $all_categories,
                "admin_companies" => $admin_companies,
            ];

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

    /**
     * @param $nodeName
     * @param $title
     * @param $message
     * @param $showLegals
     * @return Siberian_Layout|Siberian_Layout_Email
     * @throws Zend_Layout_Exception
     */
    public function baseEmail($nodeName,
                              $title,
                              $message = '',
                              $showLegals = false)
    {
        $layout = new Siberian\Layout();
        $layout = $layout->loadEmail('job', $nodeName);
        $layout
            ->setContentFor('base', 'email_title', $title)
            ->setContentFor('content_email', 'message', $message)
            ->setContentFor('footer', 'show_legals', $showLegals);

        return $layout;
    }
}