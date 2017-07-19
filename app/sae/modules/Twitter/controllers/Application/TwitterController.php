<?php

class Twitter_Application_TwitterController extends Application_Controller_Default
{
    /**
     * @var array
     */
    public $cache_triggers = array(
        "editpost" => array(
            "tags" => array(
                "homepage_app_#APP_ID#"
            ),
        )
    );

    public function editpostAction() {

        if($data = $this->getRequest()->getPost()) {

            try {
                $isNew = false;
                $application = $this->getApplication();

                // Test s'il y a un value_id
                if(empty($data['value_id'])) throw new Exception($this->_('An error occurred while saving. Please try again later.'));

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($data['value_id']);

                // Récupère l'objet
                $twitter = $option_value->getObject();

                if(!empty($data['id'])) {
                    $twitter->find($data['id']);
                }
                else {
                    $data['value_id'] = $option_value->getId();
                }
                if($twitter->getId() AND $twitter->getValueId() != $option_value->getId()) {
                    throw new Exception("An error occurred while saving.");
                }

                $twitter->setData($data)->save();

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

            $this->_sendJson($html);

        }

    }

    public function verifyAction() {
        try {
            // Returns a Twitter_Model_Twitter instance
            $twitter = $this->getCurrentOptionValue()->getObject();

            $data = $this->getRequest()->getPost();

            // then retrieve tweeter user info
            $twitter->getInfo($data["user"]);

            $data = array(
                'success' => '1',
                'success_message' => $this->_('Handle') . ': ' . $data["user"] . ' ' . $this->_('was verified with success'),
                'message_timeout' => 2,
                'message_button' => 0,
                'message_loader' => 0
            );
        } catch (Exception $e) {
            $data = array('error' => 1, 'message' => $this->_($e->getMessage()), 'code' => $e->getCode());
        }

        $this->_sendJson($data);
    }

}