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
    public static function filterFiles ($_files, $session)
    {
        $allowedExtensions = (new \Firewall_Model_Rule())
            ->findAll([
                'type' => \Firewall_Model_Rule::FW_TYPE_UPLOAD
            ]);

        $allowedExtensionsArray = [];
        foreach ($allowedExtensions as $allowedExtension) {
            $allowedExtensionsArray[] = $allowedExtension->getValue();
        }

        $allowedExtensionsArray = array_merge($allowedExtensionsArray, self::$temporaryAllowedExtensions);

        $newFiles = normalizeFiles($_files);

        foreach ($newFiles as $file) {
            $fileParts = pathinfo($file['name']);
            $extension = strtolower($fileParts['extension']);

            if (!array_key_exists('type', $file)) {
                // Wipe files without mime/type!
                unlink($file['tmp_name']);
                self::logAlert('Missing mime/type', $session);
            }

            // Forbidden extensions!
            if (in_array($extension, self::FW_FORBIDDEN_EXTENSIONS)) {
                // Wipe forbidden extensions!
                unlink($file['tmp_name']);
                self::logAlert('Strictly forbidden extension ' . $fileParts['extension'], $session);
            }

            // Regex for php
            if (preg_match("/php/ig", $extension)) {
                // Wipe forbidden extensions!
                unlink($file['tmp_name']);
                self::logAlert('Strictly forbidden extension ' . $fileParts['extension'], $session);
            }

            if (!in_array($extension, $allowedExtensionsArray)) {
                // Wipe files without mime/type!
                unlink($file['tmp_name']);
                self::logAlert('Soft forbidden extension ' . $fileParts['extension'], $session);
            }
        }

        // Second pass will use ClamAV (if available)
        $clamav = new ClamAV();
        if ($clamav->ping()) {
            foreach ($newFiles as $file) {
                if (!$clamav->scan($file['tmp_name'])) {
                    unlink($file['tmp_name']);
                    self::logAlert('ClamAV malicious file ' . $file['name'] . ' was deleted.', $session);
                }
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
            ->setUser($session->getAdminId() || $session->getBackofficeUserId())
            ->save();

        throw new Exception($message, Exception::CODE_FW);
    }
}
