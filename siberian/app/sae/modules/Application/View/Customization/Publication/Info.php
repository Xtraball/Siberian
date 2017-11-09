<?php

class Application_View_Customization_Publication_Info extends Admin_View_Default {

    public function __construct($config = array()) {
        parent::__construct($config);
    }

    public function setTemplate($template) {

        if($this->getAdmin()->canPublishThemself()) {
            $template = 'application/customization/publication/sources.phtml';
        }

        parent::setTemplate($template);
        return $this;
    }

}
