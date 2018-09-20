<?php

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
     * @throws Zend_Layout_Exception
     * @throws Zend_Session_Exception
     */
    public function checkAction()
    {
        if ($this->getRequest()->isPost()) {
            $adminCanPublish = $this->getSession()
                ->getAdmin()
                ->canPublishThemself();

            $errors = $this->getApplication()
                ->isAvailableForPublishing($adminCanPublish);

            if (!empty($errors)) {
                array_unshift($errors, __('In order to publish your application, we need:'));
                $message = join('<br />- ', $errors);

                $html = [
                    'message' => $message,
                    'message_button' => 1,
                    'message_loader' => 1
                ];
            } else {
                if (Siberian_Version::is('MAE')) {
                    $application = $this->getApplication();

                    $baseEmail = $this->baseEmail(
                        'publish_app',
                        __('Application %s', $application->getName()));

                    $baseEmail->setContentFor('content_email', 'application', $application);
                    $baseEmail->setContentFor('content_email', 'admin', $this->getAdmin());

                    $content = $baseEmail->render();

                    $subject = __('New publication request on your platform %s, for the Applicaiton %s.',
                        __get('platform_name'),
                        $application->getName());

                    $mail = new \Siberian_Mail();
                    $mail->setBodyHtml($content);
                    $mail->ccToSender();
                    $mail->setSubject($subject);
                    $mail->send();

                    $html = [
                        'success_message' => __('Your app will be published'),
                        'message_button' => 0,
                        'message_loader' => 0,
                        'message_timeout' => 3
                    ];
                } else if (Siberian_Version::TYPE === 'PE') {
                    $url = $this->getUrl('subscription/application/create');
                    $html = [
                        'url' => $url
                    ];
                }
            }

            $this->getResponse()->setBody(Zend_Json::encode($html))->sendResponse();
            die;
        }
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
        $layout = new \Siberian_Layout();
        $layout = $layout->loadEmail('application', $nodeName);
        $layout
            ->setContentFor('base', 'email_title', __('Publication request') . ' - ' . $title)
            ->setContentFor('footer', 'show_legals', true)
        ;

        return $layout;
    }
}
