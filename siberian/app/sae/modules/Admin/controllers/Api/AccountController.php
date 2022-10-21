<?php

use Siberian\Hook;

/**
 * Class Admin_Api_AccountController
 */
class Admin_Api_AccountController extends Api_Controller_Default
{
    /**
     * @var string
     */
    public $namespace = "user";

    /**
     * @var array
     */
    public $secured_actions = [
        "exist",
        "authenticate",
        "create",
        "update",
        "forgotpassword",
    ];

    public function existAction()
    {
            try {
            $request = $this->getRequest();
            $bodyParams = $request->getPost();

            if (empty($bodyParams['email'])) {
                throw new \Siberian\Exception(__('The email is required'));
                }

            $admin = (new Admin_Model_Admin())->find($bodyParams['email'], 'email');

            $payload = [
                'success' => true,
                'id' => $admin->getId(),
                'exists' => (bool) $admin->getId()
                ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
                ];
            }

        $this->_sendJson($payload);
    }

    public function authenticateAction()
    {
        $request = $this->getRequest();
        try {
            $data = $request->getPost();
            $domain = $request->getBaseUrl();

            if (empty($data["email"])) {
                throw new \Siberian\Exception(__("The email is required"));
            }
            if (empty($data["password"])) {
                throw new \Siberian\Exception(__("The password is required"));
            }

            $email = $data["email"];
            $password = $data["password"];
            $data = ["success" => 1];
            $admin = new Admin_Model_Admin();
            $admin->find($email, "email");

            if (!$admin->getId()) {
                throw new \Siberian\Exception("The user doesn't exist.");
            }

            if (!$admin->authenticate($password)) {
                throw new \Siberian\Exception(__("Authentication failed."));
            }

            $token = $admin->getLoginToken();
            $data = [
                "success" => 1,
                "token" => $token,
                "redirect_url" => "{$domain}/admin/api_account/autologin?email={$email}&token={$token}",
            ];

        } catch (\Exception $e) {
            $data = [
                "error" => 1,
                "message" => $e->getMessage()
            ];
        }
        $this->_sendJson($data);
    }

    public function createAction()
    {
        try {
            $request = $this->getRequest();
            $data = $request->getPost();

            Hook::trigger('admin.register', [
                'origin' => 'api',
                'request' => $request
            ]);

            if (!$data) {
                throw new \Siberian\Exception('dqzdqzdzq');
            }

            $admin = new Admin_Model_Admin();
            $email_checker = new Admin_Model_Admin();

            if (!empty($data['user_id'])) {
                throw new \Siberian\Exception(__("Unable to update a user from here."));
            }
            if (empty($data['email'])) {
                throw new \Siberian\Exception(__("The email is required"));
            }

            $email_checker->find($data['email'], 'email');
            if ($email_checker->getId()) {
                throw new \Siberian\Exception(__("This email address is already used"));
            }

            if (!isset($data['password'])) {
                throw new \Siberian\Exception(__('The password is required'));
            }

            $admin->addData($data)
                ->setPassword($data["password"])
                ->save();

            $domain = $this->getRequest()->getBaseUrl();

            $token = $admin->getLoginToken();
            $data = [
                "success" => 1,
                "user_id" => $admin->getId(),
                "token" => $admin->getLoginToken(),
                "redirect_url" => "{$domain}/admin/api_account/autologin?email={$data['email']}&token={$token}",
            ];

            Hook::trigger('admin.register.success', [
                'origin' => 'api',
                'adminId' => $admin->getId(),
                'admin' => $admin,
                'token' => Zend_Session::getId(),
                'request' => $request,
            ]);

        } catch (\Exception $e) {
            $data = [
                'error' => 1,
                'message' => $e->getMessage()
            ];

            Hook::trigger('admin.register.error', [
                'origin' => 'api',
                'message' => $e->getMessage(),
                'request' => $request,
            ]);
        }

        $this->_sendJson($data);
    }

    public function updateAction()
    {

        if ($data = $this->getRequest()->getPost()) {

            try {

                if (isset($data["id"])) {
                    unset($data["id"]);
                }

                $admin = new Admin_Model_Admin();

                if (!empty($data["user_id"])) {

                    $admin->find($data["user_id"]);
                    if (!$admin->getId()) {
                        throw new \Siberian\Exception(__("This admin does not exist"));
                    }

                }

                // Demo protection of default accounts!
                if (__getConfig('is_demo')) {
                    if (in_array($data['email'], ['demo@demo.com', 'client@client.com'])) {
                        throw new \Siberian\Exception(__("This action is not allowed in demo."));
                    }
                }

                if (!empty($data["email"])) {

                    $email_checker = (new Admin_Model_Admin())
                        ->find($data['email'], 'email');

                    if ($email_checker->getId() &&
                        $email_checker->getId() != $admin->getId()) {
                        throw new \Siberian\Exception(
                            __("This email address is already used")
                        );
                    }

                }

                $admin->addData($data);

                if (isset($data['password'])) {
                    $admin->setPassword($data["password"]);
                }

                $admin->save();

                $data = [
                    "success" => 1,
                    "user_id" => $admin->getId(),
                    "token" => $admin->getLoginToken()
                ];

            } catch (\Exception $e) {
                $data = [
                    'error' => 1,
                    'message' => $e->getMessage()
                ];
            }

            $this->_sendJson($data);

        }

    }

    public function forgotpasswordAction()
    {

        try {
            if ($data = $this->getRequest()->getPost()) {
                if (empty($data['email'])) {
                    throw new \Siberian\Exception(__('Please enter your email address'));
                }

                $admin = new Admin_Model_Admin();
                $admin->findByEmail($data['email']);

                if (!$admin->getId()) {
                    throw new \Siberian\Exception(__("This email address does not exist"));
                }

                $password = generate_strong_password(10);

                $admin->setPassword($password)->save();

                $layout = $this->getLayout()->loadEmail('admin', 'forgot_password');
                $subject = __('%s - Your new password');
                $layout->getPartial('content_email')->setPassword($password);

                $content = $layout->render();

                # @version 4.8.7 - SMTP
                $mail = new Siberian_Mail();
                $mail->setBodyHtml($content);
                $mail->addTo($admin->getEmail(), $admin->getName());
                $mail->setSubject($subject, ["_sender_name"]);
                $mail->send();

                $data = [
                    "success" => true
                ];
            }
        } catch (\Exception $e) {
            $data = [
                'error' => 1,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($data);
    }

    public function isloggedinAction()
    {

        if ($data = $this->getRequest()->getPost()) {

            try {
                $data = ["is_logged_in" => $this->getSession()->isLoggedIn()];
            } catch (Exception $e) {
                $data = [
                    "error" => 1,
                    "message" => $e->getMessage()
                ];
            }

            $this->_sendJson($data);

        }

    }

    public function autologinAction()
    {

        if ($email = $this->getRequest()->getParam("email")
            AND $token = $this->getRequest()->getParam("token")) {

            try {

                $admin = new Admin_Model_Admin();
                $admin->find($email, "email");

                if (!$admin->getId()) {
                    throw new Exception(__("The user doesn't exist."));
                }

                if ($admin->getLoginToken() != $token) {
                    throw new Exception(__("Authentication failed"));
                }

                $this->getSession()
                    ->setAdmin($admin);

                $this->_redirect("admin/application/list");

            } catch (Exception $e) {

            }
        }

    }

}
