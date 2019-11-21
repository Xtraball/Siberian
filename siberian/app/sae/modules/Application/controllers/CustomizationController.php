<?php

use Siberian\Version;
use Siberian\Exception;

/**
 * Class Application_CustomizationController
 */
class Application_CustomizationController extends Application_Controller_Default
{
    /**
     *
     */
    public function indexAction()
    {

        $resource = new Acl_Model_Resource();
        $resources = $resource->findAll([new Zend_Db_Expr('code LIKE \'editor_%\' AND url IS NOT NULL')]);

        foreach ($resources as $resource) {
            if ($this->_canAccess($resource->getCode())) {
                $url = rtrim(trim($resource->getData('url')), '*');
                $this->_redirect($url);
            }
        }

        $this->_redirect('application/customization_design_style/edit');
    }

    /**
     *
     */
    public function checkAction()
    {
        try {
            $request = $this->getRequest();
            if (!$request->isPost()) {
                throw new Exception(p__("application", "Invalid request"));
            }

            $session = $this->getSession();
            $admin = $session->getAdmin();
            $application = $this->getApplication();

            $adminCanPublish = $admin->canPublishThemself();
            $errors = $application->isAvailableForPublishing($adminCanPublish);

            if (!empty($errors)) {
                array_unshift($errors, __("In order to publish your application, we need:"));
                $message = join("<br />- ", $errors);

                throw new Exception($message);
            }

            switch (true) {
                case Version::is("MAE"):
                    $baseEmail = $this->baseEmail(
                        "publish_app",
                        __('Application %s', $application->getName()));

                    $baseEmail->setContentFor('content_email', 'application', $application);
                    $baseEmail->setContentFor('content_email', 'admin', $this->getAdmin());

                    $content = $baseEmail->render();

                    $subject = __('New publication request on your platform %s, for the Application %s.',
                        __get('platform_name'),
                        $application->getName());

                    $mail = new \Siberian_Mail();
                    $mail->setBodyHtml($content);
                    $mail->ccToSender();
                    $mail->setSubject($subject);
                    $mail->send();

                    $payload = [
                        "success" => true,
                        "message" => __("Your app will be published"),
                    ];

                    break;
                case Version::is("PE"):
                    $url = $this->getUrl('subscription/application/create');
                    $payload = [
                        "success" => true,
                        "url" => $url
                    ];

                    break;
                default:
                    throw new Exception(p__("application", "Subscriptions are not available in SAE."));
            }

        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * @param $nodeName
     * @param $title
     * @return Siberian_Layout|Siberian_Layout_Email
     * @throws Zend_Layout_Exception
     */
    public function baseEmail($nodeName,
                              $title)
    {
        $layout = new Siberian\Layout();
        $layout = $layout->loadEmail('application', $nodeName);
        $layout
            ->setContentFor('base', 'email_title', __('Publication request') . ' - ' . $title)
            ->setContentFor('footer', 'show_legals', true)
        ;

        return $layout;
    }
}
