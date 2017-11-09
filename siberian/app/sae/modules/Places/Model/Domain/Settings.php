<?php

/**
 * Class Places_Model_Domain_Settings
 *
 * Saves the search settings.
 * A setting has a name for instance `text`, `address`, etc.
 * A setting has a show value which answers the question: `Should the feature allow search by the setting`
 * A setting has other subfield like `label` and `radius`
 */
class Places_Model_Domain_Settings
{
    protected $value = null;
    protected $metadata;
    protected $controller;
    protected $validators;
    /*
     * Each setting has a label and possible other subfields
     * This descibes the validator, and null value for each
     */
    protected $meta_settings = array(
        'text' => array(
            'label' => array('validator' => 'Zend_Validate_Alnum', 'null_value' => "")
        ),
        'type' => array(
            'label' => array('validator' => 'Zend_Validate_Alnum', 'null_value' => "")
        ),
        'address' => array(
            'label' => array('validator' => 'Zend_Validate_Alnum', 'null_value' => "")
        ),
        'aroundyou' => array(
            'label' => array('validator' => 'Zend_Validate_Alnum', 'null_value' => ""),
            'radius' => array('validator' => 'Zend_Validate_Float', 'null_value' => 0.0)
        )
    );

    /*
     * The metadata are saved as strings, but has types to which they are casted once they are retrieved and manipulated.
     */
    protected $setting_types = array(
        'text' => array(
            'show' => 'boolean',
            'label' => 'string'
        ),
        'type' => array(
            'show' => 'boolean',
            'label' => 'string'
        ),
        'address' => array(
            'show' => 'boolean',
            'label' => 'string'
        ),
        'aroundyou' => array(
            'show' => 'boolean',
            'label' => 'string',
            'radius' => 'float'
        )
    );

    /**
     * Places_Model_Domain_Settings constructor.
     * Starts by finding the correspondant feature, otherwize throws an exception
     *
     * @param $value_id
     * @param $controller
     * @throws Exception
     */
    public function __construct($value_id, $controller)
    {
        $this->controller = $controller;
        $option_value = new Application_Model_Option_Value();
        $option_value->find($value_id);
        /*
         * If the feature is not found then throw an Exception
         */
        if (!$option_value->getId()) {
            throw new Exception($this->controller->_('Feature not found.'));
        }
        /*
         * Set the corresponding Feature (i.e. Option_Value)
         */
        $this->value = $option_value;
        /*
         * Find its corresponding Metadata
         */
        $this->metadata = $this->value->getMetadatas();
        /*
         * Setup validators
         */
        $this->validators = array(
            'Zend_Validate_Alnum' => new Zend_Validate_Alnum(array('allowWhiteSpace' => true)),
            'Zend_Validate_Float' => new Zend_Validate_Float()
        );
    }

    /**
     * Takes care of setting values, validating, or disactivating old settings.
     * If a setting is not checked it takes care of disactivating it.
     * If a setting is checked it validates its label and other values.
     *
     * Example:
     *   When
     *
     * @param $settings
     */
    public function setup($settings)
    {
        foreach ($settings as $name => $setting) {
            /*
             * Only accept settings defined in meta settings
             */
            if (array_key_exists($name, $this->meta_settings)) {
                /*
                 * If the checkbox wasn't checked then desactivate the setting
                 * Otherwize validate the setting
                 */
                if (!array_key_exists('show', $setting)) {
                    $this->_desactivateSetting($name);
                } else {
                    $this->_validateSetting($name, $setting);
                    $this->_setSetting($name, $setting);
                }
            }
        }
    }

    /**
     * Saves all the metadata defining the current settings
     *
     * @return $this
     */
    public function save()
    {
        foreach ($this->metadata as $metadatum) {
            $metadatum->save();
        }
        return $this;
    }

    /**
     * Disactivates a setting. This consists of:
     * 1 - Setting the corresponding show property to false (i.e. the checkbox).
     * 2 - Setting all the subfields (label, radius, etc.) to null values.
     *
     * @param $name
     * @return $this
     */
    private function _desactivateSetting($name)
    {
        $this->_setMetadatum('search_' . $name . '_show', 'false', $name, 'show');
        $setting_names = $this->_buildSettingNames($name);
        foreach ($setting_names as $field_name => $setting_name) {
            $this->_setMetadatum($setting_name, $this->meta_settings[$name][$field_name]['null_value'], $name, $field_name);
        }
        return $this;
    }

    /**
     * Either create or overrides the metadatum having $meta_name as name.
     *
     * @param $meta_name
     * @param $value
     * @return $this
     */
    private function _setMetadatum($meta_name, $value, $name, $field)
    {
        $metadatum = array_key_exists($meta_name, $this->metadata) ?
            $this->metadata[$meta_name] :
            new Application_Model_Option_Value_Metadata();
        $metadatum->setPayload($value);
        $metadatum->setCode($meta_name);
        $metadatum->setValueId($this->value->getValueId());
        $metadatum->setType($this->setting_types[$name][$field]);
        $this->metadata[$meta_name] = $metadatum;
        return $this;
    }

    /**
     * Finds or creates the metadata defining the setting.
     *
     * @param $name
     * @param $setting
     * @return $this
     */
    protected function _setSetting($name, $setting)
    {
        $this->_setMetadatum('search_' . $name . '_show', 'true', $name, 'show');
        $setting_names = $this->_buildSettingNames($name);
        foreach ($setting_names as $field_name => $setting_name) {
            $this->_setMetadatum($setting_name, $setting[$field_name], $name, $field_name);
        }
        return $this;
    }

    /**
     * Validates every setting's subfields, for instance checks if the label is not empty, the radius is a float, etc.
     *
     * @param $name
     * @param $setting
     * @throws Exception
     */
    protected function _validateSetting($name, $setting)
    {
        foreach ($this->meta_settings[$name] as $field_name => $field_descriptor) {
            /*
             * In order to avoid confusion:
             * $name refers to 'text', 'type', 'address' or 'aroundyou'
             * $field_name refers to 'label' or 'radius'
             */
            if (!$this->validators[$field_descriptor['validator']]->isValid($setting[$field_name])) {
                throw new Exception(
                    $this->controller->_('Please verify the ') .
                    ucfirst($field_name) . $this->controller->_(' of the ') .
                    ucfirst($name) . $this->controller->_(' setting.')
                );
            }
        }
    }

    /**
     * Builds an array of setting IDs for the setting having $name as name (e.g. text, address, etc.)
     *
     * @param $name
     * @return array
     */
    protected function _buildSettingNames($name)
    {
        $names = array();
        foreach ($this->meta_settings[$name] as $field_name => $descriptor) {
            /*
             * This yields for instance 'search_text_label' or 'search_aroundyou_radius', etc.
             */
            $names[$field_name] = 'search_' . $name . '_' . $field_name;
        }
        return $names;
    }
}