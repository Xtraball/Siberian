<?php

class Social_Application_FacebookController extends Application_Controller_Default
{

    public function editpostAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {
                $isNew = false;
                $application = $this->getApplication();

                // Test s'il y a un value_id
                if(empty($datas['value_id'])) throw new Exception($this->_('An error occurred while saving. Please try again later.'));

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);

                // Récupère l'objet
                $facebook = $option_value->getObject();

                if(!empty($datas['id'])) {
                    $facebook->find($datas['id']);
                }
                else {
                    $datas['value_id'] = $option_value->getId();
                }
                if($facebook->getId() AND $facebook->getValueId() != $option_value->getId()) {
                    throw new Exception("Une erreur est survenue lors de la sauvegarde de votre galerie vidéos. Merci de réessayer ultérieurement.");
                }

                $facebook->setData($datas)->save();

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

            $this->getLayout()->setHtml(Zend_Json::encode($html));

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