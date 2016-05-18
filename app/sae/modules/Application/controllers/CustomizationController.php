<?php

class Application_CustomizationController extends Application_Controller_Default {

    public function indexAction() {

        $resource = new Acl_Model_Resource();
        $resources = $resource->findAll(array(new Zend_Db_Expr("code LIKE 'editor_%'")));
        foreach($resources as $resource) {
            if($this->_canAccess($resource->getCode())) {
                $this->_redirect($resource->getData("url"));
            }
        }

        $this->_redirect('application/customization_design_style/edit');
    }

    public function checkAction() {

        if($this->getRequest()->isPost()) {

            $admin_can_publish = $this->getSession()->getAdmin()->canPublishThemself();
            $errors = $this->getApplication()->isAvailableForPublishing($admin_can_publish);

            if(!empty($errors)) {
                $message = $this->_('In order to publish your application, we need:<br />- ');
                $message .= join('<br />- ', $errors);

                $html = array(
                    'message' => $message,
                    'message_button' => 1,
                    'message_loader' => 1
                );
            } else {

                if(Siberian_Version::TYPE == "MAE") {

                    $backoffice_email = null;
                    $system = new System_Model_Config();
                    if($system->getValueFor("support_email")) {
                        $backoffice_email = $system->getValueFor("support_email");
                    } else {
                        $user = new Backoffice_Model_User();
                        $backoffice_user = $user->findAll(array(), "user_id ASC", array("limit" => 1))->current();

                        if($backoffice_user) {
                            $backoffice_email = $backoffice_user->getEmail();
                        }

                    }

                    $layout = $this->getLayout()->loadEmail('application', 'publish_app');
                    $layout->getPartial('content_email')->setApp($this->getApplication())->setAdmin($this->getAdmin())->setBackofficeEmail($backoffice_email);
                    $content = $layout->render();

                    $sender = $backoffice_email;

                    $mail = new Zend_Mail('UTF-8');
                    $mail->setBodyHtml($content);
                    $mail->setFrom($sender);
                    $mail->addTo($backoffice_email);
                    $mail->setSubject($this->_('%s – Publication request', $this->getApplication()->getName()));
                    $mail->send();

                    $html = array(
                        'success_message' => $this->_("Your app will be published"),
                        'message_button' => 0,
                        'message_loader' => 0,
                        'message_timeout' => 3
                    );

                } else if(Siberian_Version::TYPE == "PE") {

                    $url = $this->getUrl('subscription/application/create');
                    $html = array('url' => $url);
                }
            }

            $this->getResponse()->setBody(Zend_Json::encode($html))->sendResponse();
            die;
        }

    }
}
