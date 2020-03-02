<?php

/**
 * Class Weblink_Application_MultiController
 */
class Weblink_Application_MultiController extends Application_Controller_Default
{
    /**
     * @param $option
     * @return string
     * @throws Exception
     */
    public function exportAction()
    {
        if ($this->getCurrentOptionValue()) {
            $weblink = new Weblink_Model_Weblink();
            $result = $weblink->exportAction($this->getCurrentOptionValue());

            $this->_download($result, 'links-' . date('Y-m-d_h-i-s') . '.yml', 'text/x-yaml');
        }
    }

}