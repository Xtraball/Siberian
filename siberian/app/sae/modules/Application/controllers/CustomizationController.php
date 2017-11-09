<?php

class Application_CustomizationController extends Application_Controller_Default {

    public function indexAction() {

        $resource = new Acl_Model_Resource();
        $resources = $resource->findAll(array(new Zend_Db_Expr("code LIKE 'editor_%' AND url IS NOT NULL")));
        foreach($resources as $resource) {
            if($this->_canAccess($resource->getCode())) {
                $url = rtrim(trim($resource->getData("url")), "*");
                $this->_redirect($url);
            }
        }

        $this->_redirect('application/customization_design_style/edit');
    }

    public function checkAction() {

        if($this->getRequest()->isPost()) {

            $admin_can_publish = $this->getSession()->getAdmin()->canPublishThemself();
            $errors = $this->getApplication()->isAvailableForPublishing($admin_can_publish);

            if(!empty($errors)) {
                $message = __('In order to publish your application, we need:<br />- ');
                $message .= join('<br />- ', $errors);

                $html = array(
                    'message' => $message,
                    'message_button' => 1,
                    'message_loader' => 1
                );
            } else {

                if(Siberian_Version::TYPE == "MAE") {

                    $layout = $this->getLayout()->loadEmail('application', 'publish_app');
                    $layout->getPartial('content_email')->setApp($this->getApplication())->setAdmin($this->getAdmin());
                    $content = $layout->render();

                    # @version 4.8.7 - SMTP
                    $mail = new Siberian_Mail();
                    $mail->setBodyHtml($content);
                    $mail->ccToSender();
                    $mail->setSubject(__("%s – Publication request", $this->getApplication()->getName()));
                    $mail->send();

                    $html = array(
                        'success_message' => __("Your app will be published"),
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
