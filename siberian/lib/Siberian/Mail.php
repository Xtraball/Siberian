<?php

namespace Siberian;

use Api_Model_Key;
use Backoffice_Model_User;
use Core_Model_Default;
use Mail_Model_Log;
use Zend_Mail;
use Zend_Mail_Transport_Smtp;

/**
 * Class Mail
 * @package Siberian
 * @version 4.20.37
 */
class Mail extends Zend_Mail
{

    /**
     * @var string
     */
    public $_sender_name = '';

    /**
     * @var string
     */
    public $_sender_email = '';

    /**
     * @var string
     */
    public $_original_subject = '';

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
    public $_is_application = false;

    /**
     * @var bool
     */
    public $_application = null;

    /**
     * @var bool
     */
    public $_is_whitelabel = false;

    /**
     * @var bool
     */
    public $_whitelabel = null;

    /**
     * Mail constructor.
     * @param string $charset
     * @param array $options
     */
    public function __construct($charset = 'UTF-8', $options = [])
    {
        parent::__construct($charset);

        $configure = false;

        $this->_application = \Siberian::getApplication();
        $this->_whitelabel = \Siberian::getWhitelabel();

        // Name & E-mails, enable & test
        if ($this->_application !== false) { // 1. Application standalone settings!
            $values = Json::decode($this->_application->getData('smtp_credentials'));
            $smtpCredentials = new Core_Model_Default();
            $smtpCredentials->setData($values);

            $sender_name = $smtpCredentials->getSenderName();
            if (!empty($sender_name)) {
                $this->_sender_name = $sender_name;
            }

            $sender_email = $smtpCredentials->getSenderEmail();
            if (!empty($sender_email)) {
                $this->_sender_email = $sender_email;
            }

            $this->_is_application = true;
        } else if (($this->_whitelabel !== false) && $this->_whitelabel->getEnableCustomSmtp()) { // 2. Whitelabel!
            $values = Json::decode($this->_whitelabel->getData('smtp_credentials'));
            $smtpCredentials = new Core_Model_Default();
            $smtpCredentials->setData($values);

            $sender_name = $smtpCredentials->getSenderName();
            if (!empty($sender_name)) {
                $this->_sender_name = $sender_name;
            }

            $sender_email = $smtpCredentials->getSenderEmail();
            if (!empty($sender_email)) {
                $this->_sender_email = $sender_email;
            }

            $this->_is_whitelabel = true;
        } else { // 3. Platform Wide!
            $sender_name = __get('support_name');
            if (!empty($sender_name)) {
                $this->_sender_name = $sender_name;
            }

            $sender_email = __get('support_email');
            if (!empty($sender_email)) {
                $this->_sender_email = $sender_email;
            }
        }

        // Custom SMTP
        if (($this->_application !== false) && $this->_application->getEnableCustomSmtp()) {
            $values = Json::decode($this->_application->getData("smtp_credentials"));
            $smtpCredentials = new Core_Model_Default();
            $smtpCredentials->setData($values);

            $configure = true;
        } else if (($this->_whitelabel !== false) && $this->_whitelabel->getEnableCustomSmtp()) {
            $values = Json::decode($this->_whitelabel->getData("smtp_credentials"));
            $smtpCredentials = new Core_Model_Default();
            $smtpCredentials->setData($values);

            $configure = true;
        } else if (__get('enable_custom_smtp') == '1') {
            $api_model = new Api_Model_Key();
            $smtpCredentials = $api_model::findKeysFor('smtp_credentials');

            $configure = true;
        }

        # Default sender_email/sender_name from backoffice configuration
        if (empty($this->_sender_name)) {
            $this->_sender_name = __get('support_name');
        }

        if (empty($this->_sender_email)) {
            $this->_sender_email = __get('support_email');
        }

        # Last chance to have a default e-mail. Product owner
        if (empty($this->_sender_email)) {
            $user = new Backoffice_Model_User();
            $backoffice_user = $user->findAll(
                [],
                'user_id ASC',
                [
                    'limit' => 1
                ]
            )->current();

            if ($backoffice_user) {
                $this->_sender_email = $backoffice_user->getEmail();
            }
        }

        if ($configure) {
            $this->_is_default_mailer = false;

            $config = [
                'auth' => $smtpCredentials->getAuth(),
                'username' => $smtpCredentials->getUsername(),
                'password' => $smtpCredentials->getPassword()
            ];

            $ssl = $smtpCredentials->getSsl();
            $port = $smtpCredentials->getPort();
            if (!empty($ssl)) {
                $config['ssl'] = $ssl;
            }

            if (!empty($port)) {
                $config['port'] = $port;
            }

            $transport = new Zend_Mail_Transport_Smtp(
                $smtpCredentials->getServer(),
                $config
            );

            self::setDefaultTransport($transport);
        }
    }

    /**
     * @param string $subject
     * @param array $params
     * @return Zend_Mail
     * @throws \Zend_Mail_Exception
     */
    public function setSubject($subject, $params = []): Zend_Mail
    {
        # Write into sprintf(able) subject, params must be declared in the good order
        foreach ($params as $param) {
            if (isset($this->$param)) {
                $subject = sprintf($subject, $this->$param);
            }
        }

        $this->_original_subject = $subject;

        return parent::setSubject($subject);
    }

    /**
     * @param string $email
     * @param null $name
     * @return Zend_Mail
     * @throws \Zend_Mail_Exception
     */
    public function setFrom($email, $name = null): Zend_Mail
    {
        if (!$this->sameExpeditor($email, $this->_sender_email) && !$this->_reply_to_set) {
            $this->_reply_to_set = true;

            return $this->setReplyTo($email, $name);
        }
        $this->_custom_from = true;

        return parent::setFrom($email, $name);
    }

    /**
     * Send a copy to the sender.
     *
     * @return $this
     */
    public function ccToSender()
    {
        $this->_cc_to_sender = true;

        return $this;
    }

    /**
     * @param null $transport
     * @return Zend_Mail
     * @throws \Zend_Exception
     */
    public function send($transport = null)
    {
        // Enable & Test after my holidays @Anders
        //if($this->_is_application && !$this->_custom_from) {
        //    $this->_sender_name = Siberian::getApplication()->getName();
        //}

        # Set default sender if not custom.
        if (!$this->_custom_from) {
            $this->setFrom($this->_sender_email, $this->_sender_name);
        }

        # Sending to the sender.
        if ($this->_cc_to_sender) {
            $this->addTo($this->_sender_email, $this->_sender_name);
        }

        $logInstance = Mail_Model_Log::logEmail($this);
        try {
            $result = parent::send($transport);

            /**
             * @var $transport Zend_Mail_Transport_Smtp|Zend_Mail_Transport_Sendmail
             */
            $transport = self::getDefaultTransport();

            /**
             * @var $connection Zend_Mail_Protocol_Smtp
             */
            if ($transport instanceof Zend_Mail_Transport_Smtp) {
                $connection = $transport->getConnection();
                $log = $connection->getLog();
                $connection->resetLog();

                $logInstance
                    ->setRawSmtpLog($log)
                    ->save();
            }
            // Do something with the results

            return $result;
        } catch (\Exception $e) {
            log_err("[Siberian_Mail] an error occurred while sending the following e-mail.");
            log_err(__("[Siberian_Mail::Error] %s.", $e->getMessage()));

            // Update log error!
            $logInstance
                ->setTextError($e->getMessage())
                ->save();
        }

    }

    /**
     * @return Zend_Mail
     * @throws \Zend_Mail_Exception
     */
    public function test()
    {
        # Set default sender if not custom.
        if (!$this->_custom_from) {
            $this->setFrom($this->_sender_email, $this->_sender_name);
        }

        # Sending to the sender.
        if ($this->_cc_to_sender) {
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
     * @return Zend_Mail
     * @throws \Zend_Exception
     * @throws \Zend_Filter_Exception
     * @throws \Zend_Layout_Exception
     * @throws \Zend_Mail_Exception
     */
    public function simpleEmail($module, $template, $subject, $recipients = [], $values = [], $sender = "",
                                $sender_name = "")
    {
        $layout = new Layout();
        $layout = $layout->loadEmail($module, $template);
        $layout
            ->getPartial("content_email")
            ->setData($values);

        $content = $layout->render();

        $this
            ->setBodyHtml($content)
            ->setBodyText(strip_tags($content));

        if (!empty($sender)) {
            $this->setFrom($sender, $sender_name);
        }

        # send to sender a.k.a. platform owner if no recipient is specified.
        if (empty($recipients)) {
            $this->ccToSender();
        } else {
            foreach ($recipients as $recipient) {
                switch (get_class($recipient)) {
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

        return $this
            ->setSubject($subject);
    }

    /**
     * Checks if a sender is authorized by the smtp
     *
     * @param $from
     * @param $sender
     * @return bool
     */
    public function sameExpeditor($from, $sender)
    {
        # Assume it's authorized if no SMTP is configured.
        if ($this->_is_default_mailer || empty($sender)) {
            return true;
        }

        $from_parts = explode("@", $from);
        $sender_parts = explode("@", $sender);

        $from_domain = $from_parts[1];
        $sender_domain = $sender_parts[1];

        return (strpos($from_domain, $sender_domain) !== false);
    }

}
