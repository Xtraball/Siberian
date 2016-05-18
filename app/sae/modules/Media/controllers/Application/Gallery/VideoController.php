<?php

class Media_Application_Gallery_VideoController extends Application_Controller_Default
{

    public function editpostAction() {

        if($datas = $this->getRequest()->getPost()) {

            $html = '';

            try {

                // Test s'il y a un value_id
                if(empty($datas['value_id'])) throw new Exception($this->_("An error occurred while saving your videos gallery. Please try again later."));

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);

                $isNew = true;
                $video = new Media_Model_Gallery_Video();
                if(!empty($datas['id'])) {
                    $video->find($datas['id']);
                    $isNew = false;
                }
                else {
                    $datas['value_id'] = $option_value->getId();
//                    $datas['type_id'] = 'youtube';
                }
                if($video->getId() AND $video->getValueId() != $option_value->getId()) {
                    throw new Exception($this->_("An error occurred while saving your videos gallery. Please try again later."));
                }

                $video->setData($datas)->save();

                $html = array(
                    'success' => 1,
                    'is_new' => (int) $isNew,
                    'id' => (int) $video->getId(),
                    'success_message' => $this->_('Videos gallery has been saved successfully'),
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

}