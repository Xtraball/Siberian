<?php

/**
 * Class Radio_Mobile_RadioController
 */
class Radio_Mobile_RadioController extends Application_Controller_Mobile_Default {

    /**
     * @param $radio
     * @return array
     */
    public function _toJson($radio){
        $json = [
            'url' => addslashes($radio->getData('link')),
            'title' => $radio->getTitle(),
            'background' => $this->getRequest()->getBaseUrl() .
                '/images/application' . $radio->getBackground(),
        ];

        return $json;
    }

    /**
     * Find action
     */
    public function findAction() {
        if ($valueId = $this->getRequest()->getParam('value_id')) {
            try {
                $radio = (new Radio_Model_Radio())
                    ->find([
                        'value_id' => $valueId
                    ]);

                $data = [
                    'radio' => $this->_toJson($radio)
                ];
            } catch (Exception $e) {
                $data = [
                    'error' => true,
                    'message' => $e->getMessage()
                ];
            }
        } else {
            $data = [
                'error' => true,
                'message' => __('An error occurred while loading. Please try again later.')
            ];
        }

        $this->_sendJson($data);
    }

}
