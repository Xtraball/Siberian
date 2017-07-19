<?php

class Sourcecode_ApplicationController extends Application_Controller_Default
{

    /**
     * @var array
     */
    public $cache_triggers = array(
        "editpost" => array(
            "tags" => array(
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
                "homepage_app_#APP_ID#"
            ),
        )
    );

    public function editpostAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                $option_value = $this->getCurrentOptionValue();
                if(!$option_value) {
                    throw new Exception('An error occurred while saving. Please try again later.');
                }

                $html = '';
                $sourcecode = $option_value->getObject();
                if(!$sourcecode->getId()) {
                    $sourcecode->setValueId($option_value->getId());
                }

                $datas["allow_offline"] = !!$datas["allow_offline"];
                $sourcecode->addData($datas)->save();

                /** Update touch date, then never expires (until next touch) */
                $option_value
                    ->touch()
                    ->expires(-1);

                $html = array(
                    'success' => '1',
                    'success_message' => $this->_('Info successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

            }
            catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getResponse()
                ->setBody(Zend_Json::encode($html))
                ->sendResponse()
            ;
            die;

        }

    }

    /**
     * @param $option
     * @return string
     * @throws Exception
     */
    public function exportAction() {
        if($this->getCurrentOptionValue()) {
            $sourcecode = new Sourcecode_Model_Sourcecode();
            $result = $sourcecode->exportAction($this->getCurrentOptionValue());

            $this->_download($result, "sourcecode-".date("Y-m-d_h-i-s").".yml", "text/x-yaml");
        }
    }

}