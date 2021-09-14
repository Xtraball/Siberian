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

        $baseUrl = $this->getRequest()->getBaseUrl();
        $background = '/app/sae/modules/Radio/features/radio/img/radio-default.jpg';

        $radioBackground = '/images/application' . $radio->getBackground();
        $featureBackground = path($radioBackground);

        // Ensure we have a file uploaded!
        if (!empty($featureBackground) &&
            basename($featureBackground) !== 'application' &&
            is_file($featureBackground)) {
            $background = $radioBackground;
        }

        $json = [
            'url' => addslashes($radio->getData('link')),
            'title' => $radio->getTitle(),
            'background' => $baseUrl . $background,
            'backgroundNew' => $background
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
