<?php

class Weblink_Application_MonoController extends Application_Controller_Default
{
    /**
     * @param $option
     * @return string
     * @throws Exception
     */
    public function exportAction() {
        if ($this->getCurrentOptionValue()) {
            $weblink = new Weblink_Model_Weblink();
            $result = $weblink->exportAction($this->getCurrentOptionValue());

            $this->_download($result, 'link-' .date('Y-m-d_h-i-s'). '.yml', 'text/x-yaml');
        }
    }

}