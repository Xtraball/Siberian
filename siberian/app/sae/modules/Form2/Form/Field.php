<?php

namespace Cabride\Form;

use Siberian_Form_Abstract as FormAbstract;

/**
 * Class Field
 * @package Cabride\Form
 */
class Field extends FormAbstract
{
    /**
     * @var array
     */
    public static $types = [
        'divider' => 'Title (divider)',
        'spacer' => 'White space (spacer)',
        'number' => 'Number',
        'select' => 'Dropdown select',
        'checkbox' => 'Checkbox',
        'password' => 'Password',
        'text' => 'Text',
        'textarea' => 'Textarea',
        'date' => 'Date',
        'datetime' => 'Date & time',
    ];

    public static $dateFormats = [
        'MM/DD/YYYY' => 'MM/DD/YYYY',
        'DD/MM/YYYY' => 'DD/MM/YYYY',
        'MM DD YYYY' => 'MM DD YYYY',
        'DD MM YYYY' => 'DD MM YYYY',
        'YYYY-MM-DD' => 'YYYY-MM-DD',
        'YYYY MM DD' => 'YYYY MM DD',
    ];

    public static $datetimeFormats = [
        'MM/DD/YYYY HH:mm' => 'MM/DD/YYYY HH:mm',
        'DD/MM/YYYY HH:mm' => 'DD/MM/YYYY HH:mm',
        'MM DD YYYY HH:mm' => 'MM DD YYYY HH:mm',
        'DD MM YYYY HH:mm' => 'DD MM YYYY HH:mm',
        'YYYY-MM-DD HH:mm' => 'YYYY-MM-DD HH:mm',
        'YYYY MM DD HH:mm' => 'YYYY MM DD HH:mm',
    ];

    /**
     * @throws \Zend_Form_Exception
     * @throws \Zend_Validate_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path('/cabride/field/edit'))
            ->setAttrib('id', 'form-edit-field');

        self::addClass('create', $this);
        self::addClass('callback', $this);

        $this->addSimpleHidden('field_id');
        $this->addSimpleHidden('value_id');
        $this->addSimpleHidden('position');

        $fieldTypes = [];
        foreach (self::$types as $key => $label) {
            $fieldTypes[$key] = p__('cabride', $label);
        }

        $type = $this->addSimpleSelect('field_type', p__('cabride', 'Type'), $fieldTypes);
        $type->setRequired(true);

        $label = $this->addSimpleText('label', p__('cabride', 'Label'));
        $label->setRequired(true);

        // Number
        $this->addSimpleNumber('number_min', p__('cabride', 'Min. value'));
        $this->addSimpleNumber('number_max', p__('cabride', 'Max. value'));
        $this->addSimpleNumber('number_step', p__('cabride', 'Step'));

        $this->groupElements('group_number', ['number_min', 'number_max', 'number_step'], p__('cabride', 'Number options'));

        // Select options
        $this->addSimpleHidden('select_options');
        $this->groupElements('group_select', ['select_options'], p__('cabride', 'Select options'));

        // Date
        $this->addSimpleSelect('date_format', p__('cabride', 'Date format'), self::$dateFormats);

        $this->groupElements('group_date', ['date_format'], p__('cabride', 'Date options'));

        // Datetime
        $this->addSimpleSelect('datetime_format', p__('cabride', 'Date & time format'), self::$datetimeFormats);

        $this->groupElements('group_datetime', ['datetime_format'], p__('cabride', 'Date & time options'));

        // Default
        $this->addSimpleText('default_value', p__('cabride', 'Default value'));

        // Required
        $this->addSimpleCheckbox('is_required', p__('cabride', 'Required?'));

        $submit = $this->addSubmit(p__('cabride', 'Save'));
        $submit->addClass('pull-right');
    }

    /**
     * @param bool $raw
     * @param null $index
     * @param string $label
     * @param string $value
     * @return mixed|string
     */
    public static function getSelectTemplate($raw = true, $index = null, $label = "Label", $value = "Value")
    {
        $option = p__("cabride", "Option");
        $label = p__("cabride", $label);
        $value = p__js("cabride", $value, '"');
        $selectTemplate = <<<RAW
<div class="form-group sb-form-line select_option_index select-container" 
     rel="#INDEX#">
    <label class="select_option select-handle sb-form-line-title col-sm-3 optional">
        <i class="fa fa-sort"></i>
        {$option} #<span class="option_index">#INDEX#</span>
    </label>
    <div class="col-sm-3">
        <input type="text" 
               name="select_options[#INDEX#][label]" 
               placeholder="{$label}"
               value="#LABEL#"
               class="sb-input-default_value input-flat" />
    </div>
    <div class="col-sm-3">
        <input type="text" 
               name="select_options[#INDEX#][value]" 
               placeholder="{$value}"
               value="#VALUE#"
               class="sb-input-default_value input-flat" />
    </div>
    <div class="col-sm-1">
        <button class="remove_option btn btn-sm color-red" 
                rel="#INDEX#">
            <i class="icon ion-trash-a"></i>
        </button>
    </div>
    <div class="sb-cb"></div>
</div>
RAW;
        return $raw ? $selectTemplate :
            str_replace(["#INDEX#", "#LABEL#", "#VALUE#"], [$index, $label, $value], $selectTemplate);
    }

    /**
     * @param $formId
     */
    public function binderField($formId, $selectOptions = [])
    {
        $type = $this->getElement("field_type")->getValue();

        $options = "";
        $index = 1;
        foreach ($selectOptions as $selectOption) {
            $index++;
            $label = $selectOption["label"];
            $value = $selectOption["value"];
            $options .= "window.addOption('{$formId}', '{$index}', '{$label}', '{$value}');\n";
        }

        $js = <<<JS
<script type="text/javascript">
$(document).ready(function () {
    window.binderFormField("{$formId}");
    window.toggleGroups("{$formId}", "{$type}");
    
    $("#{$formId}").data("callback", function () { 
        setTimeout(function () {
            location.reload();
        }, 1900); 
    });

    window.initSelect("{$formId}");
    window.bindAddOption("{$formId}");
    
    {$options}
    
    window.reIndex("{$formId}");
});
</script>
JS;

        $this->addMarkup($js);
    }

    /**
     * @param array $data
     * @return bool
     * @throws \Zend_Form_Exception
     */
    public function isValid($data)
    {
        switch ($data["field_type"]) {
            case "number":
                $this->getElement("number_min")->setRequired(true);
                $this->getElement("number_max")->setRequired(true);
                $this->getElement("number_step")->setRequired(true);
                break;
            case "date":
                $this->getElement("date_format")->setRequired(true);
                break;
            case "datetime":
                $this->getElement("datetime_format")->setRequired(true);
                break;
        }

        return parent::isValid($data);
    }
}