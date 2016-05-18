<?php

class Push_MessageController extends Core_Controller_Default
{

    public function sendAction() {

        set_time_limit(6000);
        ini_set('max_execution_time', 6000);

        $logger = Zend_Registry::get("logger");
        $message = new Push_Model_Message();
        $now = Zend_Date::now()->toString('y-MM-dd HH:mm:ss');
        $errors = array();

        if($id = $this->getRequest()->getParam('message_id')) {
            $message->find($id);
            $messages = array();
            if($message->getId() AND $message->getStatus() == "queued") {
                $messages[] = $message;
            }
        } else {
            $messages = $message->findAll(array('status IN (?)' => array('queued'), 'send_at IS NULL OR send_at <= ?' => $now, 'send_until IS NULL OR send_until >= ?' => $now,'type_id = ?' => 1), 'created_at DESC');
        }

        if(count($messages) > 0) {
            foreach($messages as $message) {
                try {
                    // Envoi et sauvegarde du message
                    $message->push();
                    if($message->getErrors()) {
                        $errors[$message->getId()] = $message->getErrors();
                    }
                }
                catch(Exception $e) {
                    $message->updateStatus('failed');
                    $errors[$message->getId()] = $e;
                }
            }
        }

        Zend_Debug::dump('Erreurs :');
        Zend_Debug::dump($errors);

        if(!empty($errors)) {
            $logger->sendException(print_r($errors, true), "push_", false);
        }

        die('done');
    }

    public function deleteAction() {

        if($id = $this->getRequest()->getParam('message_id')) {
            $message = new Push_Model_Message();
            $message->find($id);

            $message->delete();

            $data = array(
                'success' => 1,
                'success_message' => $this->_('Push successfully deleted.'),
                'message_loader' => 0,
                'message_button' => 0,
                'message_timeout' => 2
            );
        }else{
            $datas = array(
                'error' => 1,
                'message' => $this->_('An error occurred while deleting the push. Please try again later.')
            );
        }

        $this->getLayout()->setHtml(Zend_Json::encode($data));

    }

}