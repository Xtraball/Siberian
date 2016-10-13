<?php

class Tip_ApplicationController extends Application_Controller_Default
{
    /**
     * @param $option
     * @return string
     * @throws Exception
     */
    public function exportAction() {
        if($this->getCurrentOptionValue()) {
            $tip = new Tip_Model_Tip();
            $result = $tip->exportAction($this->getCurrentOptionValue());

            $this->_download($result, "tip-".date("Y-m-d_h-i-s").".yml", "text/x-yaml");
        }
    }
}