<?php

use Siberian\Exception;
use Siberian\Feature;
use Siberian\Json;
use Siberian_Google_Geocoding as Geocoding;
use Contact_Model_Contact as Contact;
use Application_Model_Application as Application;

/**
 * Class Contact_ApplicationController
 */
class Contact_ApplicationController extends Application_Controller_Default
{
    /**
     * @var array
     */
    public $cache_triggers = [
        "edit-post" => [
            "tags" => [
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
                "homepage_app_#APP_ID#",
            ],
        ],
        "edit-design" => [
            "tags" => [
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
                "homepage_app_#APP_ID#",
            ],
        ],
    ];

    /**
     *
     */
    public function editPostAction()
    {
        try {
            $application = $this->getApplication();
            $optionValue = $this->getCurrentOptionValue();
            $request = $this->getRequest();
            $values = $request->getPost();

            if (!$optionValue->getId()) {
                throw new Exception(p__("contact","This feature doesn't exists!"));
            }

            if (empty($values)) {
                throw new Exception(p__("contact","Values are required!"));
            }

            $warning = false;
            $warningMessage = "";

            $form = new Contact_Form_Contact();
            if ($form->isValid($values)) {

                // Websites
                $keys = [
                    'website',
                    'facebook',
                    'twitter',
                ];
                foreach ($keys as $_key) {
                    $_tmpValue = $values[$_key];
                    if (!empty($_tmpValue)) {
                        $values[$_key] = preg_replace('#^https?://#i', 'https://', $_tmpValue);
                    }
                }

                $contact = new Contact();
                $contact->find($optionValue->getId(), "value_id");
                $contact->setData($values);

                if ($contact->getAddress()) {

                    $validate = Geocoding::validateAddress([
                        "refresh" => true,
                        "address" => $contact->getAddress()
                    ], $application->getGooglemapsKey());

                    if ($validate === false) {
                        $warning = true;
                        $warningMessage = p__("contact","We were unable to validate your address!");

                        $contact
                            ->setStreet($contact->getAddress()) // Nope!
                            ->setPostcode("")
                            ->setCity("");

                        $contact
                            ->setLatitude(null)
                            ->setLongitude(null);
                    } else {
                        $parts = Geocoding::rawToParts($validate->getRawResult());

                        $contact
                            ->setLatitude($validate->getLatitude())
                            ->setLongitude($validate->getLongitude());

                        // Set like previous version too!
                        $contact
                            ->setStreet($parts["street_number"] . " " . $parts["route"])
                            ->setPostcode($parts["postal_code"])
                            ->setCity($parts["locality"]);
                    }
                } else {
                    $contact
                        ->setLatitude(null)
                        ->setLongitude(null);
                }

                $cover = Feature::saveImageForOptionDelete($optionValue, $values["cover"]);

                $contact->setCover($cover);
                $contact->setDisplayLocateAction(filter_var($values['display_locate_action'], FILTER_VALIDATE_BOOLEAN));
                $contact->setVersion(2);
                $contact->save();

                /** Update touch date, then never expires (until next touch) */
                $optionValue
                    ->touch()
                    ->expires(-1);

                if ($warning) {
                    $payload = [
                        "warning" => true,
                        "message" => $warningMessage,
                    ];
                } else {
                    $payload = [
                        "success" => true,
                        "message" => p__("contact","Contact saved"),
                    ];
                }
            } else {
                $payload = [
                    "error" => true,
                    "message" => $form->getTextErrors(),
                    "errors" => $form->getTextErrors(true)
                ];
            }
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }
        
        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function editDesignAction()
    {
        try {
            $optionValue = $this->getCurrentOptionValue();
            $request = $this->getRequest();
            $values = $request->getPost();

            if (!$optionValue->getId()) {
                throw new Exception(p__("contact","This feature doesn't exists!"));
            }

            if (empty($values)) {
                throw new Exception(p__("contact","Values are required!"));
            }

            $form = new Contact_Form_Design();
            if ($form->isValid($values)) {

                $contact = new Contact();
                $contact->find($optionValue->getId(), "value_id");
                $contact->setDesign($values["design"]);
                $contact->save();

                /** Update touch date, then never expires (until next touch) */
                $optionValue
                    ->touch()
                    ->expires(-1);

                $payload = [
                    "success" => true,
                    "message" => p__("contact","Settings saved"),
                ];
            } else {
                $payload = [
                    "error" => true,
                    "message" => $form->getTextErrors(),
                    "errors" => $form->getTextErrors(true)
                ];
            }
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }
}
