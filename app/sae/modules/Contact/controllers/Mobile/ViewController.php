<?php

class Contact_Mobile_ViewController extends Application_Controller_Mobile_Default {

    public function indexAction() {
        $this->forward('index', 'index', 'Front', $this->getRequest()->getParams());
    }

    public function templateAction() {
        $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
    }

    public function findAction() {

        $option = $this->getCurrentOptionValue();
        $contact = $option->getObject();
        $data = array();

        $cover_b64 = null;
        if($contact->getCoverUrl()) {
            $cover_path = Core_Model_Directory::getBasePathTo($contact->getCoverUrl());
            $image = Siberian_Image::open($cover_path)->cropResize(720);
            $cover_b64 = $image->inline($image->guessType());
        }

        $data["contact"] = array(
            "name"          => $contact->getName(),
            "cover_url"     => $cover_b64,
            "street"        => $contact->getStreet(),
            "postcode"      => $contact->getPostcode(),
            "city"          => $contact->getCity(),
            "description"   => $contact->getDescription(),
            "phone"         => $contact->getPhone(),
            "email"         => $contact->getEmail(),
            "form_url"      => $this->getPath("contact/mobile_form/index", array('value_id' => $option->getId())),
            "website_url"   => $contact->getWebsite(),
            "facebook_url"  => $contact->getFacebook(),
            "twitter_url"   => $contact->getTwitter()
        );


        if($contact->getLatitude() AND $contact->getLongitude()) {
            $data['contact']["coordinates"] = array(
                "latitude"      => $contact->getLatitude(),
                "longitude"     => $contact->getLongitude()
            );
        }


        $data["page_title"] = $option->getTabbarName();

        $this->_sendJson($data);

    }

}