<?php

/**
 * Class Push_Api_GlobalController
 */
class Push_Api_GlobalController extends Api_Controller_Default {

    /**
     * @var string
     */
    public $namespace = 'push';

    /**
     * @var array
     */
    public $secured_actions = [
        'list',
        'send',
    ];

    public function listAction() {
        try {

            if($params = $this->getRequest()->getPost()) {
                $application_table = new Application_Model_Db_Table_Application();
                $all_applications = $application_table->findAllForGlobalPush();

                if(isset($params['admin_id'])) {
                    // Get apps that belong to the current admin!
                    $all_for_admin = $application_table->findAllByAdmin(
                        $this->getSession()->getAdminId()
                    )->toArray();

                    $filtered = array_map(function($app) {
                        return $app['app_id'];
                    }, $all_for_admin);

                    // We keep only apps that belongs to the admin!
                    $applications = array_intersect($all_applications, $filtered);
                } else {
                    $applications = $all_applications;
                }

                $result = [];
                if (!empty($applications)) {
                    $ids = join(',', $applications);
                    $result = $application_table->getAdapter()->fetchAll('
                        SELECT `app_id`, `name`, `key`, `bundle_id`, `package_name`, `admin_id`
                        FROM `application`
                        WHERE `app_id` IN (' . $ids . ')
                    ');
                }

                $data = [
                    'success' => true,
                    'applications' => $result,
                ];
            } else {
                throw new Siberian_Exception(
                    __("%s, No params sent.",
                        "Push_Api_GlobalController::listAction"
                    )
                );
            }
        } catch(Exception $e) {
            $message = $e->getMessage();
            $message = (empty($message)) ?
                __("An unknown error occurred while listing applications.") :
                $message;

            $data = array(
                "error" => true,
                "message" => $message,
            );
        }
        $this->_sendJson($data);
    }

    public function sendAction() {
        try {

            if($params = $this->getRequest()->getPost()) {

                // Filter checked applications!
                $params["checked"] = array_keys(
                    array_filter($params["checked"], function($v) {
                        return ($v == true);
                    })
                );

                $params["base_url"] = $this->getRequest()->getBaseUrl();

                if(empty($params["title"]) || empty($params["message"])) {
                    throw new Siberian_Exception(
                        __("Title & Message are both required.")
                    );
                }

                if(empty($params["checked"]) && !$params["send_to_all"]) {
                    throw new Siberian_Exception(
                        __("Please select at least one application.")
                    );
                }

                $push_global = new Push_Model_Message_Global();
                $result = $push_global->createInstance($params);

                $data = array(
                    "success" => true,
                    "message" => ($result) ?
                        __("Push message is sent.") :
                        __("No message sent, there is no available applications."),
                    "debug" => $this->getRequest()->getPost()
                );
            } else {
                throw new Siberian_Exception(
                    __("%s, No params sent.",
                        "Push_Api_GlobalController::sendAction"
                    )
                );
            }
        } catch(Exception $e) {
            $message = $e->getMessage();
            $message = (empty($message)) ?
                __("An unknown error occurred while creating the push notification.")
                : $message;

            $data = array(
                "error" => true,
                "message" => $message,
            );
        }
        $this->_sendJson($data);
    }


}
