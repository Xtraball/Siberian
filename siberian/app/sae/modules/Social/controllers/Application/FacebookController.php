<?php

/**
 * Class Social_Application_FacebookController
 */
class Social_Application_FacebookController extends Application_Controller_Default
{
    /**
     *
     */
    public function editpostAction()
    {
        if($datas = $this->getRequest()->getPost()) {

            try {
                $isNew = false;
                $application = $this->getApplication();

                // Test s'il y a un value_id
                if (empty($datas['value_id'])) {
                    throw new \Siberian\Exception(__('An error occurred while saving. Please try again later.'));
                }

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);

                // Récupère l'objet
                $facebook = $option_value->getObject();

                if (!empty($datas['id'])) {
                    $facebook->find($datas['id']);
                } else {
                    $datas['value_id'] = $option_value->getId();
                }
                if ($facebook->getId() AND $facebook->getValueId() != $option_value->getId()) {
                    throw new \Siberian\Exception("Une erreur est survenue lors de la sauvegarde de votre galerie vidéos. Merci de réessayer ultérieurement.");
                }

                // Test connection
                $accessToken = Core_Model_Lib_Facebook::getAppToken();
                $testResponse = Siberian_Request::get('https://graph.facebook.com/v2.7/' .
                    $datas['fb_user'] .
                    '?fields=id,about,name,genre,cover,fan_count,likes,talking_about_count&access_token=' .
                    $accessToken);
                $response = Siberian_Json::decode($testResponse);

                if (array_key_exists('error', $response)) {
                    $data = $response['error'];
                    if (array_key_exists('message', $data)) {
                        throw new \Siberian\Exception($data['message']);
                    } else {
                        throw new \Siberian\Exception(__('An unknown error occured with the Facebook API.<br />' . print_r($response, true)));
                    }
                }

                $facebook
                    ->setData($datas)
                    ->save();

                $payload = [
                    'success' => '1',
                    'success_message' => __('Info successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                ];

            } catch (Exception $e) {
                $payload = [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'message_timeout' => 2,
                    'message_button' => 1,
                    'message_loader' => 1
                ];
            }

            $this->_sendJson($payload);
        }

    }

    /**
     * @param $option
     * @return string
     * @throws Exception
     */
    public function exportAction() {
        if($this->getCurrentOptionValue()) {
            $facebook = new Social_Model_Facebook();
            $result = $facebook->exportAction($this->getCurrentOptionValue());

            $this->_download($result, "facebook-".date("Y-m-d_h-i-s").".yml", "text/x-yaml");
        }
    }


}