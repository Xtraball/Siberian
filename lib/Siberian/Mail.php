<?php
/**
 *
 */
class Siberian_Mail extends Zend_Mail {

    /**
     * @var string
     */
    public $_sender_name = "";

    /**
     * @var string
     */
    public $_sender_email = "";

    /**
     * Whether or not sender have been explicitly set.
     *
     * @var bool
     */
    public $_custom_from = false;

    /**
     * Whether or not to send a copy to the sender
     *
     * @var bool
     */
    public $_cc_to_sender = false;

    /**
     * @var bool
     */
    public $_is_default_mailer = true;

    /**
     * @var bool
     */
    public $_reply_to_set = false;

    /**
     * @var bool
     */
    public $_is_application = true;

    /**
     * Siberian_Mail constructor.
     * @param string $charset
     */
    public function __construct($charset = "UTF-8") {
        parent::__construct($charset);

        $configure = false;

        //$application = Siberian::getApplication();
        $whitelabel = Siberian::getWhitelabel();

        // Name & E-mails, enable & test
        /**if($application !== false) { // 1. Application standalone settings!
            $values = Siberian_Json::decode($application->getData("smtp_credentials"));
            $smtp_credentials = new Core_Model_Default();
            $smtp_credentials->setData($values);

            $sender_name = $smtp_credentials->getSenderName();
            if(!empty($sender_name)) {
                $this->_sender_name = $sender_name;
            }

            $sender_email = $smtp_credentials->getSenderEmail();
            if(!empty($sender_email)) {
                $this->_sender_email = $sender_email;
            }

            $this->_is_application = true;
        } else */

        if($whitelabel !== false) { // 2. Whitelabel!
            $values = Siberian_Json::decode($whitelabel->getData("smtp_credentials"));
            $smtp_credentials = new Core_Model_Default();
            $smtp_credentials->setData($values);

            $sender_name = $smtp_credentials->getSenderName();
            if(!empty($sender_name)) {
                $this->_sender_name = $sender_name;
            }

            $sender_email = $smtp_credentials->getSenderEmail();
            if(!empty($sender_email)) {
                $this->_sender_email = $sender_email;
            }
        } else {
            $sender_name = System_Model_Config::getValueFor("support_name");
            if(!empty($sender_name)) {
                $this->_sender_name = $sender_name;
            }

            $sender_email = System_Model_Config::getValueFor("support_email");
            if(!empty($sender_email)) {
                $this->_sender_email = $sender_email;
            }
        }

        // Custom SMTP
        /**if($application->getEnableCustomSmtp()) {
            $values = Siberian_Json::decode($application->getData("smtp_credentials"));
            $smtp_credentials = new Core_Model_Default();
            $smtp_credentials->setData($values);

            $configure = true;
        } else */
        if(($whitelabel !== false) && $whitelabel->getEnableCustomSmtp()) {
            $values = Siberian_Json::decode($whitelabel->getData("smtp_credentials"));
            $smtp_credentials = new Core_Model_Default();
            $smtp_credentials->setData($values);

            $configure = true;
        } else if(System_Model_Config::getValueFor("enable_custom_smtp") == "1") {
            $api_model = new Api_Model_Key();
            $smtp_credentials = $api_model::findKeysFor("smtp_credentials");

            $configure = true;
        }

        # Default sender_email/sender_name from backoffice configuration
        if(empty($this->_sender_name)) {
            $this->_sender_name = System_Model_Config::getValueFor("support_name");
        }

        if(empty($this->_sender_email)) {
            $this->_sender_email = System_Model_Config::getValueFor("support_email");
        }

        # Last chance to have a default e-mail. Product owner
        if(empty($this->_sender_email)) {
            $user = new Backoffice_Model_User();
            $backoffice_user = $user->findAll(
                array(),
                "user_id ASC",
                array(
                    "limit" => 1
                )
            )->current();

            if($backoffice_user) {
                $this->_sender_email = $backoffice_user->getEmail();
            }
        }

        if($configure) {
            $this->_is_default_mailer = false;

            $config = array(
                "auth"      => $smtp_credentials->getAuth(),
                "username"  => $smtp_credentials->getUsername(),
                "password"  => $smtp_credentials->getPassword()
            );

            $ssl = $smtp_credentials->getSsl();
            $port = $smtp_credentials->getPort();
            if(!empty($ssl)) {
                $config["ssl"] = $ssl;
            }

            if(!empty($port)) {
                $config["port"] = $port;
            }

            $transport = new Zend_Mail_Transport_Smtp(
                $smtp_credentials->getServer(),
                $config
            );

            self::setDefaultTransport($transport);
        }
    }

    /**
     * @param string $subject
     * @param array $params
     * @return Zend_Mail
     */
    public function setSubject($subject, $params = array()) {
        # Write into sprintf(able) subject, params must be declared in the good order
        foreach($params as $param) {
            if(isset($this->$param)) {
                $subject = sprintf($subject, $this->$param);
            }
        }

        return parent::setSubject($subject);
    }

    /**
     * @param string $email
     * @param null $name
     * @return Zend_Mail
     */
    public function setFrom($email, $name = null) {
        if(!$this->sameExpeditor($email, $this->_sender_email) && !$this->_reply_to_set) {
            $this->_reply_to_set = true;

            return $this->setReplyTo($email, $name);
        } else {
            $this->_custom_from = true;

            return parent::setFrom($email, $name);
        }
    }

    /**
     * Send a copy to the sender.
     *
     * @return $this
     */
    public function ccToSender() {
        $this->_cc_to_sender = true;

        return $this;
    }

    /**
     * @param null $transport
     * @return Zend_Mail
     */
    public function send($transport = null) {
        // Enable & Test after my holidays @Anders
        //if($this->_is_application && !$this->_custom_from) {
        //    $this->_sender_name = Siberian::getApplication()->getName();
        //}

        # Set default sender if not custom.
        if(!$this->_custom_from) {
            $this->setFrom($this->_sender_email, $this->_sender_name);
        }

        # Sending to the sender.
        if($this->_cc_to_sender) {
            $this->addTo($this->_sender_email, $this->_sender_name);
        }

        try {
            return parent::send($transport);
        } catch(Exception $e) {
            log_err("[Siberian_Mail] an error occurred while sending the following e-mail.");
            log_err(__("[Siberian_Mail::Error] %s.", $e->getMessage()));
        }

    }

    /**
     * Send test e-mail
     *
     * @return Zend_Mail
     */
    public function test() {
        # Set default sender if not custom.
        if(!$this->_custom_from) {
            $this->setFrom($this->_sender_email, $this->_sender_name);
        }

        # Sending to the sender.
        if($this->_cc_to_sender) {
            $this->addTo($this->_sender_email, $this->_sender_name);
        }

        return parent::send(null);
    }

    /**
     * @param $module
     * @param $template
     * @param $subject
     * @param array $recipients
     * @param array $values
     * @param string $sender
     * @param string $sender_name
     * @throws Exception
     */
    public function simpleEmail($module, $template, $subject, $recipients = array(), $values = array(), $sender = "", $sender_name = "") {
        $layout = new Siberian_Layout();
        $layout = $layout->loadEmail($module, $template);
        $layout
            ->getPartial("content_email")
            ->setData($values)
        ;

        $content = $layout->render();

        $this
            ->setBodyHtml($content)
            ->setBodyText(strip_tags($content));

        if(!empty($sender)) {
            $this->setFrom($sender, $sender_name);
        }

        # send to sender a.k.a. platform owner if no recipient is specified.
        if(empty($recipients)) {
            $this->ccToSender();
        } else {
            foreach($recipients as $recipient) {
                switch(get_class($recipient)) {
                    case "Backoffice_Model_User":
                            $this->addTo($recipient->getEmail());
                        break;
                    case "Admin_Model_Admin":
                            $this->addTo($recipient->getEmail(),
                                sprintf("%s %s",
                                    $recipient->getFirstname(),
                                    $recipient->getLastname()));
                        break;
                }

            }
        }

        $this
            ->setSubject($subject);
    }

    /**
     * Checks if a sender is authorized by the smtp
     *
     * @param $from
     * @param $sender
     * @return bool
     */
    public function sameExpeditor($from, $sender) {
        # Assume it's authorized if no SMTP is configured.
        if($this->_is_default_mailer || empty($sender)) {
            return true;
        }

        $from_parts = explode("@", $from);
        $sender_parts = explode("@", $sender);

        $from_domain = $from_parts[1];
        $sender_domain = $sender_parts[1];

        return (strpos($from_domain, $sender_domain) !== false);
    }

}