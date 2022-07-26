<?php

/**
 * Class Mail_Model_Log
 */
class Mail_Model_Log extends Core_Model_Default
{
    /**
     * Mail_Model_Log constructor.
     * @param array $params
     * @throws Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Mail_Model_Db_Table_Log';
        return $this;
    }

    /**
     * @param \Siberian_Mail $email
     * @return Mail_Model_Log
     * @throws Zend_Exception
     */
    static public function logEmail($email)
    {
        $logLine = new self();
        $logLine
            ->setTitle($email->_original_subject)
            ->setFrom($email->getFrom())
            ->setRecipients(join(',', $email->getRecipients()))
            ->setBodyHtml($email->getBodyHtml(true))
            ->setBodyText($email->getBodyText(true))
            ->setIsApplication($email->_is_application)
            ->setAppId($email->_is_application ? $email->_application->getId() : null)
            ->setIsWhitelabel($email->_is_whitelabel)
            ->setWhitelabelId($email->_is_whitelabel ? $email->_whitelabel->getId() : null)
            ->save();

        return $logLine;
    }
}
