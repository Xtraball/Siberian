<?php

/**
 * Class Contact_Mobile_ViewController
 */
class Contact_Mobile_ViewController extends Application_Controller_Mobile_Default
{

    /**
     *
     */
    public function indexAction()
    {
        $this->forward('index', 'index', 'Front', $this->getRequest()->getParams());
    }

    /**
     *
     */
    public function templateAction()
    {
        $this->loadPartials($this->getFullActionName('_') . '_l' . $this->_layout_id, false);
    }

    /**
     *
     */
    public function findAction()
    {

        $option = $this->getCurrentOptionValue();
        $contact = $option->getObject();
        $data = [];

        $cover_b64 = null;
        if ($contact->getCoverUrl()) {
            $cover_path = path($contact->getCoverUrl());
            $image = Siberian_Image::open($cover_path)->cropResize(720);
            $cover_b64 = $image->inline($image->guessType());
        }

        $data["contact"] = [
            "name" => $contact->getName(),
            "cover_url" => $cover_b64,
            "street" => $contact->getStreet(),
            "postcode" => $contact->getPostcode(),
            "city" => $contact->getCity(),
            "address" => str_replace("\n", "<br />", $contact->getAddress()),
            "description" => $contact->getDescription(),
            "phone" => $contact->getPhone(),
            "email" => $contact->getEmail(),
            "form_url" => $this->getPath("contact/mobile_form/index", ['value_id' => $option->getId()]),
            "website_url" => $contact->getWebsite(),
            "facebook_url" => $contact->getFacebook(),
            "twitter_url" => $contact->getTwitter(),
            "display_locate_action" => (boolean) $contact->getDisplayLocateAction()
        ];

        if ($contact->getLatitude() && $contact->getLongitude()) {
            $data['contact']["coordinates"] = [
                "latitude" => $contact->getLatitude(),
                "longitude" => $contact->getLongitude()
            ];
        }


        $data["page_title"] = $option->getTabbarName();
        $data["card_design"] = (boolean) ($contact->getDesign() === "card");

        $this->_sendJson($data);

    }

}
