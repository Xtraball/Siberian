<?php

/**
 * Class Wordpress2_Mobile_ListController
 */
class Wordpress2_Mobile_ListController extends Application_Controller_Mobile_Default
{
    /**
     *
     */
    public function findallAction()
    {
        $this->_sendJson(
            [
                'success' => true
            ]
        );
    }
}
