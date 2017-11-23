<?php

class Twitter_Application_TwitterController extends Application_Controller_Default
{
    /**
     * @var array
     */
    public $cache_triggers = [
        'editpost' => [
            'tags' => [
                'homepage_app_#APP_ID#'
            ],
        ]
    ];

    public function editpostAction() {
        if($data = $this->getRequest()->getPost()) {
            // then retrieve tweeter user info
            $user = preg_replace('#(https?://)?twitter\.com\/#', '', $data['twitter_user']);
            try {
                // Test s'il y a un value_id
                if (empty($data['value_id'])) {
                    throw new Siberian_Exception(__('An error occurred while saving. Please try again later.'));
                }

                try {
                    // Returns a Twitter_Model_Twitter instance
                    $twitter = $this->getCurrentOptionValue()->getObject();
                    $twitter->getInfo($user);
                } catch (Exception $e) {
                    throw new Siberian_Exception(__('Invalid twitter handle/url!'));
                }
                

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($data['value_id']);

                // Récupère l'objet
                $twitter = $option_value->getObject();

                if (!empty($data['id'])) {
                    $twitter->find($data['id']);
                } else {
                    $data['value_id'] = $option_value->getId();
                }
                if ($twitter->getId() AND $twitter->getValueId() != $option_value->getId()) {
                    throw new Siberian_Exception(__('An error occurred while saving.'));
                }

                $twitter
                    ->setData($data)
                    ->save();

                /** Update touch date, then never expires (until next touch) */
                $option_value
                    ->touch()
                    ->expires(-1);

                $payload = [
                    'success' => '1',
                    'success_message' => __('Info successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                ];

            }
            catch(Exception $e) {
                $payload = [
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                ];
            }

            $this->_sendJson($payload);
        }
    }
}