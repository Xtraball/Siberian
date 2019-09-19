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
        "php",
        "js",
        "ico",
    ];

    const TRIGGERS = [
        "(<\?php|&lt;\?php|<\?|<script|&lt;script)",
        "(INSERT\s+INTO)",
        "(UPDATE|DELETE|TRUNCATE|DROP)\s+",
        "(src|onclick|onerror)\s*=",
        "(self|top|parent)\s*[",
        "=\s*(self|top|parent)",
        "document\.cookie",
        "\\\x[0-9]+",
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
                return self::logAlert('Missing mime/type', $session);
            }

            // Forbidden extensions!
            if (in_array($extension, self::FW_FORBIDDEN_EXTENSIONS)) {
                // Wipe forbidden extensions!
                unlink($file['tmp_name']);
                return self::logAlert('Strictly forbidden extension ' . $fileParts['extension'], $session);
            }

            // Regex for php
            if (preg_match("/php/ig", $extension)) {
                // Wipe forbidden extensions!
                unlink($file['tmp_name']);
                return self::logAlert('Strictly forbidden extension ' . $fileParts['extension'], $session);
            }

            if (!in_array($extension, $allowedExtensionsArray)) {
                // Wipe files without mime/type!
                unlink($file['tmp_name']);
                return self::logAlert('Soft forbidden extension ' . $fileParts['extension'], $session);
            }
        }

        // Second pass will use ClamAV (if available)
        $clamav = new ClamAV();
        if ($clamav->ping()) {
            foreach ($newFiles as $file) {
                if (!$clamav->scan($file['tmp_name'])) {
                    unlink($file['tmp_name']);
                    return self::logAlert('Suspicious file detected ' . $file['name'] . ' was deleted.', $session);
                }
            }
        }
    }

    /**
     * @param $_get
     * @param $session
     * @throws Exception
     * @throws \Zend_Exception
     */
    public static function filterGet ($_get, $session)
    {
        $values = array_flat($_get, "get");
        foreach ($values as $key => $value) {
            self::checkKeyValue("GET", $key, $value, $session);
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
        $values = array_flat($_post, "post");

        foreach ($values as $key => $value) {
            self::checkKeyValue("POST", $key, $value, $session);
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
        $values = array_flat($_bodyParams, "body_params");
        foreach ($values as $key => $value) {
            self::checkKeyValue("BODY", $key, $value, $session);
        }
    }

    public static function checkKeyValue ($origin, $key, $value, $session)
    {
        $tmpDir = tmp(true);
        if (strpos($value, "base64") !== false) {
            try {
                $content = base64_decode(explode(',', $value)[1]);
            } catch (\Exception $e) {
                // Nope base64_decode failed!
                return self::logAlert('Uploaded base64 data is invalid.', $session);
            }
        } else {
            $content = $value;
        }

        foreach (self::TRIGGERS as $trigger) {
            if (preg_match("~$trigger~im", $key) === 1) {
                dbg($trigger, $key);
                return self::logAlert("#$origin-001-1: Suspicious data detected.", $session);
            }
        }

        foreach (self::TRIGGERS as $trigger) {
            if (preg_match("~$trigger~im", $value) === 1) {
                return self::logAlert("#$origin-001-2: Suspicious data detected.", $session);
            }
        }

        $tmpFilename = $tmpDir . '/' . uniqid();

        File::putContents($tmpFilename, $content);
        chmod($tmpFilename, 0644);
        // Second pass will use ClamAV (if available)
        $clamav = new ClamAV();
        if ($clamav->ping() && !$clamav->scan($tmpFilename)) {
            unlink($tmpFilename);
            return self::logAlert("#$origin-002: Suspicious data detected.", $session);
        }
        unlink($tmpFilename);
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

        // Slack notifications!
        $slackIsEnabled = (boolean) __get('fw_slack_is_enabled');
        if ($slackIsEnabled) {
            $slack = new \Siberian\Notification\Slack();

            $user = $fwLog->getUser();
            $userData = [
                'id' => '-',
                'email' => '-',
            ];

            if ($user) {
                $userData = [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                ];
            }

            $slackMessage = sprintf(
                "%s - %s - %s - %s",
                $fwLog->getType(),
                $fwLog->getMessage(),
                $userData['id'],
                $userData['email']);

            $slack->send($slackMessage);
        }

        throw new Exception(p__("firewall", "This request was blocked for security reasons, please try again later."), Exception::CODE_FW);
    }
}
