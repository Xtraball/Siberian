<?php
/**
 */
class Siberian_Mail extends Zend_Mail {

    public static $transport = null;

    public function __construct($charset = "UTF-8") {
        parent::__construct($charset);

        if(System_Model_Config::getValueFor("use_custom_smtp")) {
            /** @todo custom stmp */
        }
    }

    public function setCustomSmtp() {
        /** @todo custom stmp */
    }

    public function simpleEmail($module, $template, $subject, $recipients = array(), $values = array(), $sender = "", $sender_name = "") {
        $layout = Siberian_Layout();
        $layout->loadEmail($module, $template);
        $layout
            ->getPartial('content_email')
            ->setData($values)
        ;

        $content = $layout->render();

        if(empty($sender)) {
            $sender = System_Model_Config::getValueFor("support_email");
        }
        if(empty($sender_name)) {
            $sender_name = System_Model_Config::getValueFor("support_name");
        }

        if(empty($sender)) {
            throw new Exception("#324-01: Unable to send e-mail, please set 'Support email address' in your backoffice.");
        }

        $this
            ->setBodyHtml($content)
            ->setBodyText(strip_tags($content))
            ->setFrom($sender, $sender_name)
            ->addTo($recipients)
            ->setSubject($subject)
        ;
    }

}