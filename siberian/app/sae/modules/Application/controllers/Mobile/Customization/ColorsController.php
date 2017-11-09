<?php

class Application_Mobile_Customization_ColorsController extends Application_Controller_Mobile_Default {

    public function indexAction() {
        $this->loadPartials("front_index_index");
        $this->getLayout()->addPartial("style", "core_view_mobile_default", "application/customization/css.phtml");
    }

}
