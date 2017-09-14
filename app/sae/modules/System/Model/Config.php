<?php

class System_Model_Config extends Rss_Model_Feed_Abstract {

    const IMAGE_PATH = "/images/site";

    protected static $_values = array();

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'System_Model_Db_Table_Config';
        return $this;
    }

    public static function getValueFor($code) {

        if(!isset(self::$_values[$code])) {
            $config = new self();
            $config->find($code, 'code');
            self::$_values[$code] = $config->getValue();
        }

        return self::$_values[$code];

    }

    public static function setValueFor($code, $value) {
        $config = new self();
        $config->find($code, "code");
        $config->setCode($code);
        $config->setValue($value)->save();
        return $config;
    }

    public function save() {

        $value_changed = $this->getValue() != $this->getOrigValue();

        $code = $this->getCode();
        if(empty($code)) {
            return $this;
        }

        if(stripos($this->getValue(), "image/png;base64") !== false) {

            $data = substr($this->getValue(),strpos($this->getValue(),",")+1);
            $data = str_replace(' ', '+', $data);
            $data = base64_decode($data);
            $ext = $this->getCode() == "favicon" ? ".ico" : ".png";
            $filename = $this->getCode().$ext;
            $filepath = Core_Model_Directory::getBasePathTo("images/default");

            if(!is_dir($filepath)) {
                mkdir($filepath, 0777, true);
            }

            if(!is_writable($filepath)) {
                throw new Exception(__("The folder /images/default is not writable."));
            }

            file_put_contents("$filepath/$filename", $data);

            $this->setValue("/images/default/{$this->getCode()}.$ext");

        }

        parent::save();

        if($this->getCode() == "system_timezone" && $value_changed) {

            $config = new self();
            $config->find("system_territory", "code");
            $value = $this->getValue();

            $territories = Zend_Registry::get("Zend_Locale")->getTranslationList('TerritoryToTimezone');
            $territory = $value && !empty($territories[$value]) ? $territories[$value] : null;

            $data = array(
                "code" => "system_territory",
                "value" => $territory
            );

            $config->addData($data)->save();

        }
    }


}
