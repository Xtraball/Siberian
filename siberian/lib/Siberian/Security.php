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
                    self::logAlert('Suspicious file detected ' . $file['name'] . ' was deleted.', $session);
                }
            }
        }
    }

    /**
     * @param $_post
     * @param $session
     * @throws Exception
     * @throws \Zend_Exception
     */
    public static function filterGet ($_get, $session)
    {
        $values = array_flat($_get, 'get');
        $tmpDir = \Core_Model_Directory::getTmpDirectory(true);

        foreach ($values as $key => $value) {
            if (strpos($value, 'base64') !== false) {
                try {
                    $content = base64_decode(explode(',', $value)[1]);
                } catch (\Exception $e) {
                    // Nope base64_decode failed!
                    self::logAlert('Uploaded base64 data is invalid.', $session);
                }

                if (strpos($content, '<?php') !== false) {
                    self::logAlert('#G-001: Suspicious upload detected.', $session);
                }
                $tmpFilename = $tmpDir . '/' . uniqid();

                file_put_contents($tmpFilename, $content);
                chmod($tmpFilename, 0644);
                // Second pass will use ClamAV (if available)
                $clamav = new ClamAV();
                if ($clamav->ping() && !$clamav->scan($tmpFilename)) {
                    unlink($tmpFilename);
                    self::logAlert('#G-002: Suspicious upload detected.', $session);
                }
                unlink($tmpFilename);
            }
        }
    }

    /**
     * @param $_post
     * @param $session
     * @throws Exception
     * @throws \Zend_Exception
     */
    public static function filterPost ($_post, $session)
    {
        $values = array_flat($_post, 'post');
        $tmpDir = \Core_Model_Directory::getTmpDirectory(true);

        foreach ($values as $key => $value) {
            if (strpos($value, 'base64') !== false) {
                try {
                    $content = base64_decode(explode(',', $value)[1]);
                } catch (\Exception $e) {
                    // Nope base64_decode failed!
                    self::logAlert('Uploaded base64 data is invalid.', $session);
                }

                if (strpos($content, '<?php') !== false) {
                    self::logAlert('#P-001: Suspicious upload detected.', $session);
                }
                $tmpFilename = $tmpDir . '/' . uniqid();

                file_put_contents($tmpFilename, $content);
                chmod($tmpFilename, 0644);
                // Second pass will use ClamAV (if available)
                $clamav = new ClamAV();
                if ($clamav->ping() && !$clamav->scan($tmpFilename)) {
                    unlink($tmpFilename);
                    self::logAlert('#P-002: Suspicious upload detected.', $session);
                }
                unlink($tmpFilename);
            }
        }
    }

    /**
     * @param $_bodyParams
     * @param $session
     * @throws Exception
     * @throws \Zend_Exception
     */
    public static function filterBodyParams ($_bodyParams, $session)
    {
        $values = array_flat($_bodyParams, 'body_params');
        $tmpDir = \Core_Model_Directory::getTmpDirectory(true);

        foreach ($values as $key => $value) {
            if (strpos($value, 'base64') !== false) {
                try {
                    $content = base64_decode(explode(',', $value)[1]);
                } catch (\Exception $e) {
                    self::logAlert('Uploaded base64 data is invalid.', $session);
                }

                if (strpos($content, '<?php') !== false) {
                    self::logAlert('#B-001: Suspicious upload detected.', $session);
                }
                $tmpFilename = $tmpDir . '/' . uniqid();

                file_put_contents($tmpFilename, $content);
                chmod($tmpFilename, 0644);
                // Second pass will use ClamAV (if available)
                $clamav = new ClamAV();
                if ($clamav->ping() && !$clamav->scan($tmpFilename)) {
                    unlink($tmpFilename);
                    self::logAlert('#B-002: Suspicious upload detected.', $session);
                }
                unlink($tmpFilename);
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
        $namespace = $session->getNamespace();
        switch ($namespace) {
            case 'front':
                    $userId = $session->getAdminId();
                    $userClass = 'Admin_Model_Admin';
                break;
            case 'backoffice':
                    $userId = $session->getBackofficeUserId();
                    $userClass = 'Backoffice_Model_User';
                break;
            default:
                    $userId = $session->getCustomerId();
                    $userClass = 'Customer_Model_Customer';
                break;
        }

        if (empty($userId)) {
            $userId = 'undetected userId';
        }

        if (empty($userClass)) {
            $userClass = 'undetected userClass';
        }

        $fwLog = (new \Firewall_Model_Log());
        $fwLog
            ->setType(\Firewall_Model_Rule::FW_TYPE_UPLOAD)
            ->setMessage($message)
            ->setUserId($userId)
            ->setUserClass($userClass)
            ->save();

        throw new Exception($message, Exception::CODE_FW);
    }
}
