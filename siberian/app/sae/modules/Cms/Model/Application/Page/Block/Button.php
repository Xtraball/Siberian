<?php

use Siberian\Json;

/**
 * Class Cms_Model_Application_Page_Block_Button
 */
class Cms_Model_Application_Page_Block_Button extends Cms_Model_Application_Page_Block_Abstract
{
    /**
     * @var string
     */
    protected $_db_table = Cms_Model_Db_Table_Application_Page_Block_Button::class;

    /**
     * @return bool|mixed
     */
    public function isValid()
    {
        if ($this->getContent()) {
            return true;
        }

        return false;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options): self
    {
        return $this->setData('options', Json::encode($options));
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        try {
            $options = Json::decode($this->getData('options'));
        } catch (\Exception $e) {
            $options = [];
        }

        return $options;
    }

    /**
     * @param array $data
     * @return $this|Cms_Model_Application_Page_Block_Abstract
     * @throws \Siberian\Exception
     */
    public function populate($data = [])
    {
        $this->setTypeId($data['type']);
        $this->setLabel($data['label']);

        // Options
        $options = [
            'global' => [],
            'android' => [],
            'ios' => [],
        ];
        $optionsGlobal = $data['options']['global'];
        foreach ($optionsGlobal as $key => $value) {
            $options['global'][$key] = $value;
        }
        $optionsAndroid = $data['options']['android'];
        foreach ($optionsAndroid as $key => $value) {
            $options['android'][str_replace('android_', '', $key)] = ($value) ? 'yes' : 'no';
        }
        $optionsIos = $data['options']['ios'];
        foreach ($optionsIos as $key => $value) {
            $options['ios'][str_replace('ios_', '', $key)] = ($value) ? 'yes' : 'no';
        }
        $this->setOptions($options);

        $icon = Siberian_Feature::saveImageForOptionDelete($this->option_value, $data['icon']);
        $this->setIcon($icon);

        switch ($data['type']) {
            case 'phone':
                $this->setContent($data['phone']);
                break;
            case 'link':
                $this->setContent($data['link']);
                break;
            case 'email':
                $this->setContent($data['email']);
                break;
        }

        return $this;
    }

}