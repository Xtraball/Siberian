<?php

/**
 * Class Customer_Mobile_VerifmailController
 */
class Customer_Mobile_VerifmailController extends Application_Controller_Mobile_Default
{
    public function sendAction ()
    {
        try {
            // E-Mail back the user!
            $request = $this->getRequest();
            $params = $request->getBodyParams();
            $application = $this->getApplication();
            $appId = $application->getId();
            $appKey = $application->getKey();
            $applicationName = $application->getName();

            if (empty($params['email'])) {
                throw new \Exception(p__('customer', 'Your email is invalid!'));
            }

            $subject = p__('customer', "%s - Confirm your e-mail", $applicationName);

            $baseEmail = $this->baseEmail("send_token", $subject, "", true);

            $baseDomain = __get('main_domain');
            $whitelabel = \Siberian::getWhitelabel();
            if ($whitelabel && $whitelabel->getHost()) {
                $baseDomain = $whitelabel->getHost();
            }

            // clean up
            $requestApps = (new \Customer\Model\RequestApp())
                ->findAll([
                    'email = ?' => $params['email'],
                    'app_id = ?' => $appId,
                ]);
            foreach ($requestApps as $requestApp) {
                $requestApp->delete();
            }

            $requestApp = new \Customer\Model\RequestApp();
            $token = \Siberian\UUID::v4();
            $requestApp
                ->setEmail($params['email'])
                ->setAppId($appId)
                ->setToken($token)
                ->setStatus('pending')
                ->save();

            $url = sprintf("https://%s/%s/customer/mobile_verifmail/verify?token=%s", $baseDomain, $appKey, $token);

            $data = [
                'url' => $url,
                'app' => $applicationName,
            ];
            foreach ($data as $key => $value) {
                $baseEmail->setContentFor('content_email', $key, $value);
            }

            $content = $baseEmail->render();

            $mail = new \Siberian_Mail();
            $mail->setBodyHtml($content);
            $mail->addTo($params['email']);
            $mail->setSubject($subject);
            $mail->send();

            $payload = [
                'success' => true,
                'message' => p__('customer', 'Please check your inbox and validate your e-mail!')
            ];
        } catch (\Exception $e) {
            // Something went wrong with the-mail!
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    public function liveCheckAction()
    {
        try {
            // E-Mail back the user!
            $request = $this->getRequest();
            $params = $request->getBodyParams();
            $application = $this->getApplication();
            $appId = $application->getId();

            if (empty($params['email'])) {
                throw new \Exception(p__('customer', 'Your email is invalid!'));
            }

            $requestApp = (new \Customer\Model\RequestApp())
                ->find([
                    'email' => $params['email'],
                    'app_id' => $appId,
                ]);

            if (!$requestApp || !$requestApp->getId()) {
                throw new \Exception(p__('customer', 'Invalid request!'));
            }

            $payload = [
                'success' => true,
                'status' => $requestApp->getStatus()
            ];
        } catch (\Exception $e) {
            // Something went wrong with the-mail!
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Token-based ACK validation
     */
    public function verifyAction()
    {
        $request = $this->getRequest();
        try {
            $token = $request->getParam('token', null);

            $requestApp = (new \Customer\Model\RequestApp())
                ->find([
                    'token' => $token,
                    'status' => 'pending',
                ]);

            if (!$requestApp || !$requestApp->getId()) {
                throw new Exception(p__('customer', 'This validation is already processed!'));
            }

            $requestApp
                ->setStatus('valid')
                ->save();

            $payload = [
                "success" => true,
                "message" => p__('customer', 'Success'),
            ];
        } catch (\Exception $e) {
            $responseMessage = $e->getMessage();
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        // Alert message & close!
        if (!$request->isXmlHttpRequest()) {
            echo "<script type=\"text/javascript\">
    window.alert(\"" . p__js("customer", $responseMessage) . ", " . p__js("customer", "this window will be closed automatically, please check the application!") . "\");
    window.close();
</script>";
            die;
        }

        $this->_sendJson($payload);
    }

    /**
     * @param $nodeName
     * @param $title
     * @param $message
     * @param $showLegals
     * @return Siberian_Layout|Siberian_Layout_Email
     * @throws Zend_Layout_Exception
     */
    public function baseEmail($nodeName,
                              $title,
                              $message = '',
                              $showLegals = false)
    {
        $layout = new Siberian\Layout();
        $layout = $layout->loadEmail('customer', $nodeName);
        $layout
            ->setContentFor('base', 'email_title', $title)
            ->setContentFor('content_email', 'message', $message)
            ->setContentFor('footer', 'show_legals', $showLegals);

        return $layout;
    }

}
