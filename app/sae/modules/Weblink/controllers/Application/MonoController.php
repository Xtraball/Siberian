<?php

class Weblink_Application_MonoController extends Application_Controller_Default
{

    public function editpostAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {
                $application = $this->getApplication();

                // Test s'il y a un value_id
                if(empty($datas['value_id'])) throw new Exception($this->_('An error occurred while saving. Please try again later.'));

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);
                
                if(empty($datas['link']) OR !Zend_Uri::check($datas['link'])) {
                    throw new Exception($this->_('Please enter a valid url'));
                }

                // Prépare le weblink
                $html = array();
                $weblink = new Weblink_Model_Type_Mono();
                $weblink->find($option_value->getId(), 'value_id');
                if(!$weblink->getId()) {
                    $weblink->setValueId($datas['value_id']);
                }

                // Affecte l'url au lien
                $weblink->getLink()->setUrl(!empty($datas['link']) ? $datas['link'] : null);

                // Sauvegarde
                $weblink->save();

                $html = array(
                    'success' => '1',
                    'success_message' => $this->_('Link has been successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );
                if(!$weblink->getIsDeleted()) {
                    $html['link'] = $weblink->getLink()->getUrl();
                }

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

}