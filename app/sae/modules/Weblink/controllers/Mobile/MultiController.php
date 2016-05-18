<?php

class Weblink_Mobile_MultiController extends Application_Controller_Mobile_Default {

    public function indexAction() {
        $this->forward('index', 'index', 'Front', $this->getRequest()->getParams());
    }

    public function templateAction() {
        $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
    }

    public function findAction() {

        $option = $this->getCurrentOptionValue();
        $weblink = $option->getObject();
        $data = array();

        $data["weblink"] = array(
            "cover_url" => $weblink->getCoverUrl() ? $this->getRequest()->getBaseUrl().$weblink->getCoverUrl() : null,
            "links" => array()
        );

        foreach($weblink->getLinks() as $link) {
            $data["weblink"]["links"][] = array(
                "id" => $link->getId(),
                "title" => $link->getTitle(),
                "picto_url" => $link->getPictoUrl() ? $this->getRequest()->getBaseUrl().$link->getPictoUrl() : null,
                "url" => $link->getUrl()
            );
        }

        $data['page_title'] = $option->getTabbarName();

        $this->_sendHtml($data);

    }

}