<?php

namespace Form2\Form;

use Siberian_Form_Abstract as FormAbstract;

/**
 * Class Field
 * @package Form2\Form
 */
class Field extends FormAbstract
{
    /**
     * @var array
     */
    public static $types = [
        'Formatting elements' => [
            'divider' => 'Title (section, divider)',
            'spacer' => 'White space (spacer)',
            'illustration' => 'Illustration (image)',
            'richtext' => 'Richtext (block)',
            'clickwrap' => 'Clickwrap (action, agreement)',
        ],
        'Input elements' => [
            'number' => 'Number',
            'select' => 'Dropdown select',
            'radio' => 'Radio choice',
            'checkbox' => 'Checkbox',
            'password' => 'Password',
            'text' => 'Text input',
            'textarea' => 'Textarea',
            'image' => 'Pictures (image)',
            'date' => 'Date',
            'datetime' => 'Date & time',
            'geolocation' => 'Geolocation (GPS, georeverse)',
        ],
    ];

    /**
     * @var array
     */
    public static $_types = [
        'divider' => 'Title (section, divider)',
        'spacer' => 'White space (spacer)',
        'illustration' => 'Illustration (image)',
        'richtext' => 'Richtext (block)',
        'clickwrap' => 'Clickwrap (action, agreement)',
        'number' => 'Number',
        'select' => 'Dropdown select',
        'radio' => 'Radio choice',
        'checkbox' => 'Checkbox',
        'password' => 'Password',
        'text' => 'Text input',
        'textarea' => 'Textarea',
        'image' => 'Pictures (image)',
        'date' => 'Date',
        'datetime' => 'Date & time',
        'geolocation' => 'Geolocation (GPS, georeverse)',
    ];

    public static $dateFormats = [
        'MM/DD' => 'MM/DD',
        'DD/MM' => 'DD/MM',
        'MM DD' => 'MM DD',
        'DD MM' => 'DD MM',
        'MM-DD' => 'MM-DD',
        'DD-MM' => 'DD-MM',
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
            ->setAction(__path('/form2/field/edit'))
            ->setAttrib('id', 'form-edit-field');

        self::addClass('create', $this);

        $this->addSimpleHidden('field_id');
        $this->addSimpleHidden('value_id');
        $this->addSimpleHidden('position');

        $fieldTypes = [];
        foreach (self::$types as $groupLabel => $groupValues) {
            $groupLabel = p__('form2', $groupLabel);
            $fieldTypes[$groupLabel] = [];
            foreach ($groupValues as $value => $label) {
                $fieldTypes[$groupLabel][$value] = p__('form2', $label);
            }
        }

        $type = $this->addSimpleSelect('field_type', p__('form2', 'Type'), $fieldTypes);
        $type->setRequired(true);

        $label = $this->addSimpleText('label', p__('form2', 'Label, identifier'));
        $label->setRequired(true);

        // Number
        $numberMin = $this->addSimpleNumber('number_min', p__('form2', 'Min. value'));
        $numberMax = $this->addSimpleNumber('number_max', p__('form2', 'Max. value'));
        $numberStep = $this->addSimpleNumber('number_step', p__('form2', 'Step'));
        $numberStep->setValue(0);
        $numberStep->setDescription(p__('form2', 'Set to 0 to ignore the increment/step validation.'));

        $numberMin->setRequired(true);
        $numberMax->setRequired(true);
        $numberStep->setRequired(true);

        $this->groupElements('group_number', ['number_min', 'number_max', 'number_step'], p__('form2', 'Number options'));

        // Select options
        $this->addSimpleHidden('select_options');
        $this->groupElements('group_select', ['select_options'], p__('form2', 'Select options'));

        // Illustration
        $imageText = p__('form2', 'Illustration');
        $this->addSimpleImage('image', $imageText, $imageText, [
            'width' => 1000,
            'height' => 400,
        ]);

        $this->groupElements('group_illustration', ['image', 'image_button'], p__('form2', 'Illustration options'));

        // Images
        $limit = $this->addSimpleNumber('limit', p__('form2', 'Max pictures allowed') . ' (1-10)', 1, 10, true, 1);
        $limit->setValue(1);
        $limit->setRequired(true);

        $this->addSimpleText('image_addpicture', p__('form2', 'Text for `Add a picture`'));
        $this->addSimpleText('image_addanotherpicture', p__('form2', 'Text for `Add another picture`'));

        $this->groupElements('group_image',
            ['limit', 'image_addpicture', 'image_addanotherpicture'],
            p__('form2', 'Picture options'));

        // Clickwrap
        $clickwrap = $this->addSimpleSelect('clickwrap', p__('form2', 'Action'), [
            'privacy-policy' => p__('form2', 'Display app privacy policy'),
            'richtext' => p__('form2', 'Display custom richtext'),
        ]);

        // Clickwrap richtext
        $this->addSimpleText('clickwrap_modaltitle', p__('form2', 'Modal title'));

        $clickwrapRichText = $this->addSimpleTextarea('clickwrap_richtext', p__('form2', 'Richtext'));
        $clickwrapRichText->setAttrib('ckeditor', 'form');
        $clickwrapRichText->setRichtext();

        $this->groupElements('group_clickwrap', ['clickwrap_modaltitle', 'clickwrap', 'clickwrap_richtext'], p__('form2', 'Clickwrap options'));

        // Richtext
        $richText = $this->addSimpleTextarea('richtext', p__('form2', 'Richtext'));
        $richText->setAttrib('ckeditor', 'form');
        $richText->setRichtext();

        $this->groupElements('group_richtext', ['richtext'], p__('form2', 'Richtext options'));

        // Week days
        $weekDays = [
            1 => p__('datepicker', 'Monday'),
            2 => p__('datepicker', 'Tuesday'),
            3 => p__('datepicker', 'Wednesday'),
            4 => p__('datepicker', 'Thursday'),
            5 => p__('datepicker', 'Friday'),
            6 => p__('datepicker', 'Saturday'),
            0 => p__('datepicker', 'Sunday'),
        ];

        // Available date days
        $dateDays = $this->addSimpleMultiCheckbox('date_days', p__('form2', 'Available weekdays'), $weekDays);
        $dateDays->setValue([0, 1, 2, 3, 4, 5, 6]);

        // Date
        $dateFormat = $this->addSimpleSelect('date_format', p__('form2', 'Date format'), self::$dateFormats);
        $this->groupElements('group_date', ['date_format', 'date_days'], p__('form2', 'Date options'));

        $dateDays->setRequired(true);
        $dateFormat->setRequired(true);

        // Available datetime days
        $datetimeDays = $this->addSimpleMultiCheckbox('datetime_days', p__('form2', 'Available weekdays'), $weekDays);
        $datetimeDays->setValue([0, 1, 2, 3, 4, 5, 6]);

        // Datetime
        $datetimeFormat = $this->addSimpleSelect('datetime_format', p__('form2', 'Date & time format'), self::$datetimeFormats);
        $this->groupElements('group_datetime', ['datetime_format', 'datetime_days'], p__('form2', 'Date & time options'));

        $datetimeDays->setRequired(true);
        $datetimeFormat->setRequired(true);

        // Default
        $this->addSimpleText('default_value', p__('form2', 'Default value'));

        // Required
        $this->addSimpleCheckbox('is_required', p__('form2', 'Required?'));

        $submit = $this->addSubmit(p__('form2', 'Save'));
        $submit->addClass('pull-right');
    }

    /**
     * @param bool $raw
     * @param null $index
     * @param string $label
     * @param string $value
     * @return mixed|string
     */
    public static function getSelectTemplate($raw = true, $index = null, $label = 'Label', $value = 'Value')
    {
        $option = p__('form2', 'Option');
        $label = p__('form2', $label);
        $value = p__js('form2', $value, '"');
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
            str_replace(['#INDEX#', '#LABEL#', '#VALUE#'], [$index, $label, $value], $selectTemplate);
    }

    /**
     * @param $formId
     * @param array $selectOptions
     */
    public function binderField($formId, $selectOptions = [])
    {
        $type = $this->getElement('field_type')->getValue();
        $clickwrap = $this->getElement('clickwrap')->getValue();

        $options = '';
        $index = 1;
        foreach ($selectOptions as $selectOption) {
            $index++;
            $label = $selectOption['label'];
            $value = $selectOption['value'];
            $options .= "window.addOption('{$formId}', '{$index}', '{$value}', '{$label}');\n";
        }

        $js = <<<JS
<script type="text/javascript">
$(document).ready(function () {
    window.binderFormField("{$formId}");
    window.toggleGroups("{$formId}", "{$type}");
    window.toggleClickwrap("{$formId}", "{$clickwrap}");
    window.initSelect("{$formId}");
    window.bindAddOption("{$formId}");
    
    {$options}
    
    window.reIndex("{$formId}");
    window.reloadOverviewFormv2();
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
        $elements = [
            'number_min',
            'number_max',
            'number_step',
            'date_format',
            'date_days',
            'datetime_format',
            'datetime_days',
            'limit',
        ];
        // Reset following elements!
        foreach ($elements as $element) {
            $this->getElement($element)->setRequired(false);
        }

        switch ($data['field_type']) {
            case 'number':
                $this->getElement('number_min')->setRequired(true);
                $this->getElement('number_max')->setRequired(true);
                $this->getElement('number_step')->setRequired(true);
                break;
            case 'date':
                $this->getElement('date_format')->setRequired(true);
                $this->getElement('date_days')->setRequired(true);
                break;
            case 'datetime':
                $this->getElement('datetime_format')->setRequired(true);
                $this->getElement('datetime_days')->setRequired(true);
                break;
            case 'image':
                $this->getElement('limit')->setRequired(true);
                break;
        }

        return parent::isValid($data);
    }
}
