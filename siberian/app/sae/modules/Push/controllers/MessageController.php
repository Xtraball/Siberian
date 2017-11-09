<?php

class Push_MessageController extends Core_Controller_Default
{

    /**
     * @deprecated un-used action called from old wget crontab
     * @param null $message_id
     */
    public function sendAction($message_id = null) {
        # Do nothing, die silently
        die();
    }
    
    public function deleteAction() {

        if($id = $this->getRequest()->getParam('message_id')) {
            $message = new Push_Model_Message();
            $message->find($id);

            $message->delete();

            $data = array(
                'success' => 1,
                'success_message' => __('Push successfully deleted.'),
                'message_loader' => 0,
                'message_button' => 0,
                'message_timeout' => 2
            );
        }else{
            $data = array(
                'error' => 1,
                'message' => __('An error occurred while deleting the push. Please try again later.')
            );
        }

        $this->getLayout()->setHtml(Zend_Json::encode($data));

    }

}