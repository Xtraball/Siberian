<?php

/**
 * Class Firewall_Model_Rule
 *
 * @method string getType()
 * @method string getValue()
 * @method $this setType(string $type)
 * @method $this setValue(string $value)
 * @method Firewall_Model_Rule[] findAll($values = [], $order = null, $params = [])
 */
class Firewall_Model_Rule extends Core_Model_Default
{
    const FW_TYPE_UPLOAD = 'fw_type_upload';

    /**
     * Firewal_Model_Rule constructor.
     * @param array $params
     * @throws Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Firewall_Model_Db_Table_Rule';
        return $this;
    }

    /**
     * @param string|null $extension
     * @return void
     * @throws \Siberian\Exception
     */
    public static function allowExtension(string $extension = null)
    {
        $extension = self::checkRules($extension);

        $fwRule = (new self())->find([
            'type' => self::FW_TYPE_UPLOAD,
            'value' => $extension
        ]);

        if (!$fwRule->getId()) {
            $fwRule
                ->setType(self::FW_TYPE_UPLOAD)
                ->setValue($extension)
                ->save();
        }
    }

    /**
     * @param string|null $extension
     * @return void
     * @throws Zend_Exception
     * @throws \Siberian\Exception
     */
    public static function disallowExtension(string $extension = null)
    {
        $extension = self::checkRules($extension);

        $fwRule = (new self())->find([
            'type' => self::FW_TYPE_UPLOAD,
            'value' => $extension
        ]);

        if ($fwRule->getId()) {
            $fwRule->delete();
        }
    }

    /**
     * @return array
     */
    public static function listExtensions()
    {
        $fwRules = (new self())->findAll([
            'type' => self::FW_TYPE_UPLOAD,
        ]);

        $extensions = [];
        foreach ($fwRules as $fwRule) {
            $extensions[] = $fwRule->getValue();
        }

        return $extensions;
    }

    /**
     * @param string|null $extension
     * @return string
     * @throws Zend_Exception
     * @throws \Siberian\Exception
     */
    public static function checkRules(string $extension = null)
    {
        if (__getConfig('is_demo')) {
            // Demo version
            throw new Exception(__("You cannot change Firewall settings, it's a demo version."));
        }

        if (empty($extension)) {
            throw new \Siberian\Exception(__('Missing value'));
        }

        $extension = trim($extension);
        if (empty($extension)) {
            throw new \Siberian\Exception(__("Extension can't be empty."));
        }

        if (in_array($extension, ['php', 'js', 'ico'])) {
            throw new \Siberian\Exception(__("Extension %s is strictly forbidden.", $extension));
        }

        return $extension;
    }

}
