<?php

/**
 * Class Front_Controller_App_Default
 */
class Front_Controller_App_Default extends Application_Controller_Mobile_Default
{
    /**
     * @var Application_Model_Option_Value
     */
    public $currentOptionValue;

    /**
     * @return Application_Controller_Mobile_Default|void
     * @throws Zend_Exception
     * @throws \Siberian\Exception
     */
    public function init()
    {
        parent::init();

        $request = $this->getRequest();
        $application = $this->getApplication();
        $params = $request->getParams();
        $rawBody = $request->getBodyParams();

        // Fallback test for value_id!
        $currentValueId = null;
        try {
            if (array_key_exists('option_value_id', $params) &&
                !empty($params['option_value_id'])) {
                $currentValueId = $params['option_value_id'];
            } else if (array_key_exists('value_id', $params) &&
                !empty($params['value_id'])) {
                $currentValueId = $params['value_id'];
            } else if (array_key_exists('value_id', $rawBody) &&
                !empty($rawBody['value_id'])) {
                $currentValueId = $rawBody['value_id'];
            }
        } catch (Exception $e) {}

        // We are in an application!
        Siberian::setApplication($application);

        // Testing if value_id belongs to the app (or is allowed)
        if (!$application->valueIdBelongsTo($currentValueId) && $currentValueId) {
            $this->_sendJson([
                'error' => true,
                'message' => __('Unauthorized access to feature.')
            ], true);
        } else {
            if ($currentValueId) {

                // Create optionValue
                $this->currentOptionValue = new Application_Model_Option_Value();

                if ($currentValueId !== 'homepage') {
                    $this->currentOptionValue->find($currentValueId);
                } else {
                    $this->currentOptionValue->setIsHomepage(true);
                }
            }

            Core_View_Mobile_Default::setCurrentOption($this->currentOptionValue);
        }
    }

    /**
     * @return mixed
     * @throws Zend_Session_Exception
     */
    public function isOverview()
    {
        return $this->getSession()->isOverview;
    }


    /**
     * @return Application_Model_Option_Value
     */
    public function getCurrentOptionValue()
    {
        return $this->currentOptionValue;
    }

    /**
     * Converts an array to json, set the header code to 400 if error
     *
     * @param $data
     * @param bool $send
     */
    public function _sendJson($data, $send = false)
    {
        $response = $this->getResponse();
        $response->setHeader('Content-type', 'application/json');

        if (isset($data['error']) && !empty($data['error'])) {
            if (isset($data['gone']) && $data['gone']) {
                /** Resource is gone */
                $response->setHttpResponseCode(410);
            } else {
                $response->setHttpResponseCode(400);
            }
        }

        // Handle development case, unset exception messages in production!
        if (!Siberian_Debug::isDevelopment() && isset($data['exceptionMessage'])) {
            unset($data['exceptionMessage']);
        }

        $json = Siberian_Json::encode($data);
        $this->getLayout()->setHtml($json);

        // Abort current request and send immediate response!
        if ($send === true) {
            Zend_Controller_Front::getInstance()->returnResponse(true);
            $response->sendResponse();
            echo $json;
            die;
        }

    }
}
