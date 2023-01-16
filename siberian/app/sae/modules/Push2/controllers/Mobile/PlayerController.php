<?php

namespace Push2\Mobile;

use Siberian\Json;
use \Application_Controller_Mobile_Default as MobileController;

/**
 * Class PlayerController
 */
class PlayerController extends MobileController
{
    public function registerAction()
    {
        $application = $this->getApplication();
        $option = $this->getCurrentOptionValue();

    }

}

// @important!
class_alias(ListController::class, 'Push2_Mobile_ListController');