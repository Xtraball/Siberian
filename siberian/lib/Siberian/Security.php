<?php

namespace Siberian;

/**
 * Class Security
 * @package Siberian
 * @author Xtraball SAS <dev@xtraball.com>
 */
class Security
{
    const FW_FORBIDDEN_EXTENSIONS = [
        'php',
        'js',
        'ico',
    ];

    /**
     * @var array
     */
    public static $temporaryAllowedExtensions = [];

    /**
     * @param $extension
     */
    public static function allowExtension ($extension)
    {
        self::$temporaryAllowedExtensions[] = $extension;
    }

    /**
     * @param $_files
     * @param $session
     * @throws Exception
     * @throws \Zend_Exception
     */
    public static function filterFiles (&$_files, $session)
    {
        $allowedExtensions = (new \Firewall_Model_Rule())
            ->findAll([
                'type' => \Firewall_Model_Rule::FW_TYPE_UPLOAD
            ]);

        $allowedExtensionsArray = [];
        foreach ($allowedExtensions as $allowedExtension) {
            $allowedExtensionsArray[] = $allowedExtension->getValue();
        }

        foreach ($_files as $key => $file) {
            $tmpFile = $_files[$key];
            $fileParts = pathinfo($tmpFile['name'][0]);

            if (!array_key_exists('type', $file)) {
                // Wipe files without mime/type!
                unlink($tmpFile['tmp_name'][0]);
                self::logAlert('Missing mime/type', $session);
            }

            // Forbidden extensions!
            if (in_array($fileParts['extension'], self::FW_FORBIDDEN_EXTENSIONS)) {
                // Wipe forbidden extensions!
                unlink($tmpFile['tmp_name'][0]);
                self::logAlert('Strictly forbidden extension ' . $fileParts['extension'], $session);
            }

            if (!in_array($fileParts['extension'], $allowedExtensionsArray)) {
                // Wipe files without mime/type!
                unlink($tmpFile['tmp_name'][0]);
                self::logAlert('Soft forbidden extension ' . $fileParts['extension'], $session);
            }
        }

        // Second pass will use ClamAV (if available)
        $clamav = new ClamAV();

        foreach ($_files as $key => $file) {
            $tmpFile = $_files[$key];
            if (!$clamav->scan($file['tmp_name'])) {
                unlink($tmpFile['tmp_name'][0]);
                self::logAlert('ClamAV malicious file ' . $tmpFile['name'][0] . ' was deleted.', $session);
            }
        }
    }

    /**
     * @param $message
     * @throws Exception
     * @throws \Zend_Exception
     */
    public static function logAlert ($message, \Core_Model_Session $session)
    {
        $fwLog = (new \Firewall_Model_Log());
        $fwLog
            ->setType(\Firewall_Model_Rule::FW_TYPE_UPLOAD)
            ->setMessage($message)
            ->setUser($session->getAdminId())
            ->save();

        throw new Exception($message);
    }
}
