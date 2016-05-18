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
//        $address = "";
//        $latlon = array();

//        if($contact->getStreet() AND $contact->getPostcode() AND $contact->getCity()) {
//
//            $latlon = Siberian_Google_Geocoding::getLatLng(array(
//                "street" => $contact->getStreet(),
//                "postcode" => $contact->getPostcode(),
//                "city" => $contact->getCity()
//            ));
//
//            if(!empty($latlon[0]) && !empty($latlon[1])) {
//                $latlon = array(
//                    "latitude" => $latlon[0],
//                    "longitude" => $latlon[1]
//                );
//            }
//        }

        $data['contact'] = array(
            "name" => $contact->getName(),
            "cover_url" => $contact->getCoverUrl() ? $this->getRequest()->getBaseUrl().$contact->getCoverUrl() : null,
            "street" => $contact->getStreet(),
            "postcode" => $contact->getPostcode(),
            "city" => $contact->getCity(),
            "description" => $contact->getDescription(),
            "phone" => $contact->getPhone(),
            "email" => $contact->getEmail(),
            "form_url" => $this->getPath("contact/mobile_form/index", array('value_id' => $option->getId())),
            "website_url" => $contact->getWebsite(),
            "facebook_url" => $contact->getFacebook(),
            "twitter_url" => $contact->getTwitter()
        );


        if($contact->getLatitude() AND $contact->getLongitude()) {
            $data['contact']["coordinates"] = array(
                "latitude" => $contact->getLatitude(),
                "longitude" => $contact->getLongitude()
            );
        }


        $data['page_title'] = $option->getTabbarName();


        $this->_sendHtml($data);

    }

}