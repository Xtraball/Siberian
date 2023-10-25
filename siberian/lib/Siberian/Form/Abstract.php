<?php

/**
 * Class Siberian_Form_Abstract
 *
 * @method $this setAction(string $action)
 * @method $this setAttrib(string $key, mixed $value)
 */
abstract class Siberian_Form_Abstract extends Zend_Form
{

    const DATEPICKER = "datepicker";
    const TIMEPICKER = "timepicker";
    const DATETIMEPICKER = "datetimepicker";

    /**
     * @var bool
     * @deprecated
     */
    public $bind_js = false;

    /**
     * @var int
     */
    public $_value_id;

    /**
     * @var Siberian_Form_Element_Button
     */
    public $mini_submit;

    /**
     * @var string
     */
    public $color = "color-blue";

    /**
     * @var bool
     */
    public $is_form_horizontal = true;

    /**
     * @var string
     */
    public $markup = "";

    /**
     * @var string
     */
    public $confirm_text = "";

    /**
     * @param $options
     * @throws Zend_Form_Exception
     */
    public function __construct($options = [])
    {
        if (!is_array($options) || empty($options)) {
            $options = [];
        }
        parent::__construct($options);

        if (array_key_exists("_settings", $options)) {
            $settings = $options["_settings"];
            $this->_value_id = $settings["value_id"] ?? null;
        }

        $this->init();
    }

    /**
     * @param $float
     * @return float|int
     */
    public function getUnit($float)
    {
        $floatingPoint = strlen(substr(strrchr($float, '.'), 1));
        if ($floatingPoint === 0) {
            return 1;
        }
        $multiplier = pow(10, $floatingPoint);
        $divider = $float * $multiplier;
        $unit = $float / $divider;

        return $unit;
    }

    public function init()
    {
        parent::init();

        $this
            ->setMethod(Zend_Form::METHOD_POST)
            ->setAttrib("class", "form sb-form feature-form");

        if ($this->is_form_horizontal) {
            self::addClass("form-horizontal", $this);
        }

        $this->setDecorators(['FormElements', 'Form']);
    }

    /**
     * @param $boolean
     */
    public function setIsFormHorizontal($boolean)
    {
        $this->is_form_horizontal = $boolean;

        return $this;
    }

    /**
     * @param $confirm_text
     */
    public function setConfirmText($confirm_text)
    {
        $this->confirm_text = __($confirm_text);
    }

    /**
     * @param $value
     * @return Siberian_Form_Abstract
     */
    public function setBindJs($value)
    {
        $this->bind_js = $value;

        return $this;
    }

    /**
     * @param $value_id
     * @return $this
     */
    public function setValueId($value_id)
    {
        $this->_value_id = $value_id;
        if (!is_null($this->getElement("value_id"))) {
            $el_value_id = $this->getElement("value_id");
            $el_value_id->setValue($value_id);
        }

        return $this;
    }

    /**
     * @return Application_Model_Application|null
     * @throws Zend_Exception
     */
    public function getApplication()
    {
        $optionValue = (new Application_Model_Option_Value())->find($this->_value_id);
        if ($optionValue && $optionValue->getId()) {
            return (new Application_Model_Application())->find($optionValue->getAppId());
        }
        return null;
    }

    /**
     * @param $name
     * @return null|Zend_Form_DisplayGroup
     * @throws Zend_Form_Exception
     */
    public function addNav($name, $save_text = "OK", $display_back_button = true, $with_label = false)
    {
        $elements = [];

        $back_button = new Siberian_Form_Element_Button("sb-back");
        $back_button->setAttrib("escape", false);
        $back_button->setLabel("<i class=\"fa fa-angle-left \"></i>");
        $back_button->addClass("pull-left feature-back-button default_button");
        $back_button->setColor($this->color);
        $back_button->setBackDesign();

        if ($display_back_button) {
            $elements[] = $back_button;
        }

        $submit_button = new Siberian_Form_Element_Submit(__($save_text));
        $submit_button->addClass("pull-right default_button");
        $submit_button->setColor($this->color);
        $submit_button->setNewDesign();

        if ($with_label) {
            $submit_button->setLabel(__($save_text));
            $submit_button->setValue($name);
        }

        $elements[] = $submit_button;

        $this->addDisplayGroup($elements, $name);

        $nav_group = $this->getDisplayGroup($name);
        $nav_group->removeDecorator('DtDdWrapper');
        $nav_group->setAttrib("class", "sb-nav");

        return $nav_group;
    }

    /**
     * @param $name
     * @return $this
     */
    public function removeNav($name)
    {
        $display_group = $this->getDisplayGroup($name);
        if (is_null($display_group)) {
            log_debug("The nav doesn't exists.");
            return $this;
        }
        foreach ($display_group->getElements() as $element) {
            $this->removeElement($element->getName());
        }
        $this->removeDisplayGroup($name);

        return $this;
    }

    /**
     * @param null $label
     * @return Siberian_Form_Element_Submit
     * @throws Zend_Form_Exception
     */
    public function addSubmit($label = null, $name = null)
    {
        if ($label == null) {
            $label = "Rechercher";
        }

        if ($name == null) {
            $name = "go";
        }
        $submit = new Siberian_Form_Element_Submit($name);
        $this->addElement($submit);
        $submit
            ->setLabel($label)
            ->setAttrib('class', 'btn default_button')
            ->setDecorators([
                'ViewHelper'
            ]);
        $submit->setIsFormHorizontal($this->is_form_horizontal);
        $submit->setColor($this->color);
        $submit->setNewDesign();
        return $submit;
    }

    /**
     * @param null $label
     * @param null $label_off
     * @param null $label_on
     * @return Siberian_Form_Element_Button
     * @throws Zend_Form_Exception
     */
    public function addMiniSubmit($label = null, $label_off = null, $label_on = null)
    {
        if ($label == null) {
            $label = "<i class='fa fa-times icon icon-remove company-manage-delete'></i>";
        }
        $submit = new Siberian_Form_Element_Button($label);
        $this->addElement($submit);
        $submit->setLabel($label);
        $submit->setMiniDeleteDesign();
        $submit->setAttrib("data-toggle-on", $label_on);
        $submit->setAttrib("data-toggle-off", $label_off);

        $this->mini_submit = $submit;

        return $submit;
    }

    /**
     * @param $element
     * @param string $on_text
     * @param string $off_text
     * @return mixed
     */
    public function defaultToggle($element, $on_text = "Enable", $off_text = "Disable")
    {
        $element->setAttrib("data-toggle", "tooltip");
        $element->setAttrib("data-title-on", __($on_text));
        $element->setAttrib("data-title-off", __($off_text));

        self::addClass("display_tooltip", $element);

        return $element;
    }

    /**
     * Default generic toggler
     *
     * @param $state
     * @throws Zend_Form_Exception
     */
    public function setToggleState($state)
    {
        $this->mini_submit->setLabel($state ? $this->mini_submit->getAttrib("data-toggle-off") : $this->mini_submit->getAttrib("data-toggle-on"));
        $this->mini_submit->setAttrib("title", $state ? $this->mini_submit->getAttrib("data-title-off") : $this->mini_submit->getAttrib("data-title-on"));
    }

    /**
     * @param $name
     * @param string $label
     * @param bool $placeholder
     * @return Siberian_Form_Element_Text
     * @throws Zend_Form_Exception
     */
    public function addSimpleText($name, $label = "", $placeholder = false, $placeholderText = false)
    {
        $el = new Siberian_Form_Element_Text($name);
        $this->addElement($el);
        if ($placeholder) {
            if ($placeholderText === false) {
                $el->setAttrib('placeholder', $label);
                $el->setDecorators(['ViewHelper']);
            } else {
                $el->setLabel($label);
                $el->setAttrib('placeholder', $placeholder);
                $el->setDecorators(['ViewHelper', 'Label']);
            }
        } else {
            $el->setLabel($label);
            $el->setDecorators(['ViewHelper', 'Label']);
        }
        $el->setIsFormHorizontal($this->is_form_horizontal);
        $el->setColor($this->color);
        $el->setNewDesign();

        return $el;
    }


    /**
     * @param $name
     * @param string $label
     * @param bool $placeholder
     * @return Siberian_Form_Element_Email
     * @throws Zend_Form_Exception
     */
    public function addSimpleEmail($name, $label = "", $placeholder = false)
    {
        $el = new Siberian_Form_Element_Email($name);
        $this->addElement($el);
        if ($placeholder) {
            $el->setAttrib('placeholder', $label);
            $el->setDecorators(['ViewHelper']);
        } else {
            $el->setLabel($label);
            $el->setDecorators(['ViewHelper', 'Label']);
        }
        $el->setIsFormHorizontal($this->is_form_horizontal);
        $el->setColor($this->color);
        $el->setNewDesign();

        return $el;
    }

    /**
     * @param $name
     * @param string $label
     * @param bool $placeholder
     * @return Siberian_Form_Element_Button
     * @throws Zend_Form_Exception
     */
    public function addSimpleButton($name, $label = "", $placeholder = false)
    {
        $el = new Siberian_Form_Element_Button($name);
        $this->addElement($el);
        if ($placeholder) {
            $el->setAttrib('placeholder', $label);
            $el->setDecorators(['ViewHelper']);
        } else {
            $el->setLabel($label);
            $el->setDecorators(['ViewHelper', 'Label']);
        }
        $el->setIsFormHorizontal($this->is_form_horizontal);
        $el->setColor($this->color);
        $el->setNewDesign();

        return $el;
    }

    /**
     * @param $name
     * @param string $label
     * @param array $options
     * @param bool $with_indicator
     * @return Siberian_Form_Element_Slider
     * @throws Zend_Form_Exception
     */
    public function addSimpleSlider($name, $label = "", $options = [], $with_indicator = true)
    {
        if ($with_indicator) {
            $options["indicator"] = true;
        }
        $el = new Siberian_Form_Element_Slider($name, $options);
        $this->addElement($el);
        $el->setLabel($label);
        $el->setDecorators(['ViewHelper', 'Label']);
        $el->setIsFormHorizontal($this->is_form_horizontal);
        $el->setColor($this->color);
        $el->setNewDesign();
        return $el;
    }

    /**
     * @param $name
     * @param string $label
     * @param bool $placeholder
     * @param string $type
     * @param bool $format
     * @return Siberian_Form_Element_Text
     * @throws Zend_Form_Exception
     */
    public function addSimpleDatetimepicker($name,
                                            $label = "",
                                            $placeholder = false,
                                            $type = self::DATEPICKER,
                                            $format = false)
    {
        $el = new Siberian_Form_Element_Text($name);
        $this->addElement($el);
        if ($placeholder) {
            $el->setAttrib('placeholder', $label);
            $el->setDecorators(['ViewHelper']);
        } else {
            $el->setLabel($label);
            $el->setDecorators(['ViewHelper', 'Label']);
        }
        $el->setAttrib('data-datetimepicker', $type);
        if ($format) {
            $el->setAttrib('data-format', $format);
        }

        $el->setIsFormHorizontal($this->is_form_horizontal);
        $el->setColor($this->color);
        $el->setNewDesign();
        return $el;
    }

    /**
     * @param $name
     * @param string $label
     * @param bool $placeholder
     * @param string $type
     * @return Siberian_Form_Element_Text
     * @throws Zend_Form_Exception
     */
    public function addSimpleDatetimepickerv2($name,
                                              $label = "",
                                              $placeholder = false,
                                              $type = self::DATEPICKER)
    {
        $el = new Siberian_Form_Element_Text($name);
        $this->addElement($el);
        $el->setLabel($label);
        $el->setDecorators(['ViewHelper', 'Label']);
        $el->setIsFormHorizontal($this->is_form_horizontal);
        $el->setAttrib('data-datetimepicker-v2', $type);
        $el->setNewDesign();

        return $el;
    }

    /**
     * @param $name
     * @param string $label
     * @param bool $placeholder
     * @return Siberian_Form_Element_Password
     * @throws Zend_Form_Exception
     */
    public function addSimplePassword($name, $label = "", $placeholder = false)
    {
        $el = new Siberian_Form_Element_Password($name);
        $this->addElement($el);
        if ($placeholder) {
            $el->setAttrib('placeholder', $label);
            $el->setDecorators(['ViewHelper']);
        } else {
            $el->setLabel($label);
            $el->setDecorators(['ViewHelper', 'Label']);
        }

        $el->setIsFormHorizontal($this->is_form_horizontal);
        $el->setColor($this->color);
        $el->setNewDesign();
        return $el;
    }

    /**
     * @param $name
     * @param string $label
     * @return Siberian_Form_Element_Textarea
     * @throws Zend_Form_Exception
     */
    public function addSimpleTextarea($name, $label = "", $placeholder = false, $options = [])
    {
        $el = new Siberian_Form_Element_Textarea($name);
        $this->addElement($el);
        if ($placeholder) {
            $el->setAttrib('placeholder', $label);
            $el->setDecorators(['ViewHelper']);
        } else {
            $el->setLabel($label);
            $el->setDecorators(['ViewHelper', 'Label']);
        }

        if (isset($options["ckeditor"])) {
            $el->setAttrib('ckeditor', $options["ckeditor"]);
        }

        $el->setIsFormHorizontal($this->is_form_horizontal);
        $el->setColor($this->color);
        $el->setNewDesign();
        return $el;
    }

    /**
     * @param $name
     * @param string $label
     * @param $options
     * @return Siberian_Form_Element_Select
     * @throws Zend_Form_Exception
     */
    public function addSimpleSelect($name, $label = "", $options = [])
    {
        $el = new Siberian_Form_Element_Select($name);
        $this->addElement($el);
        $el->setIsFormHorizontal($this->is_form_horizontal);
        $el->setColor($this->color);
        $el
            ->setNewDesign()
            ->setLabel($label)
            ->setMultiOptions($options);
        return $el;
    }

    /**
     * @param $name
     * @param string $label
     * @param $options
     * @return Siberian_Form_Element_Multiselect
     * @throws Zend_Form_Exception
     */
    public function addSimpleMultiSelect($name, $label = "", $options = [])
    {
        $el = new Siberian_Form_Element_Multiselect($name);
        $this->addElement($el);
        $el->setIsFormHorizontal($this->is_form_horizontal);
        $el->setColor($this->color);
        $el
            ->setNewDesign()
            ->setLabel($label)
            ->setMultiOptions($options);
        return $el;
    }

    /**
     * @param $name
     * @param $html
     * @param array $attributes
     * @return Siberian_Form_Element_Html
     * @throws Zend_Form_Exception
     */
    public function addSimpleHtml($name, $html, $attributes = [])
    {
        $el = new Siberian_Form_Element_Html($name, $attributes);
        $this->addElement($el);
        $el->setIsFormHorizontal($this->is_form_horizontal);
        $el
            ->setValue($html)
            ->setNewDesign();
        return $el;
    }

    /**
     * @param $name
     * @param $label
     * @return Siberian_Form_Element_Checkbox
     * @throws Zend_Form_Exception
     */
    public function addSimpleCheckbox($name, $label)
    {
        $el = new Siberian_Form_Element_Checkbox($name);
        $this->addElement($el);
        $el->setIsFormHorizontal($this->is_form_horizontal);
        $el->setColor($this->color);
        $el
            ->setLabel($label)
            ->setNewDesign();

        return $el;
    }

    /**
     * @param $name
     * @param $label
     * @param array $options
     * @return Siberian_Form_Element_Radio
     * @throws Zend_Form_Exception
     */
    public function addSimpleRadio($name, $label, $options = [])
    {
        $el = new Siberian_Form_Element_Radio($name);
        $this->addElement($el);
        $el->setIsFormHorizontal($this->is_form_horizontal);
        $el->setColor($this->color);
        $el
            ->addMultiOptions($options)
            ->setLabel($label)
            ->setNewDesign();
        return $el;
    }

    /**
     * @param $name
     * @param $label
     * @param $options
     * @return Siberian_Form_Element_MultiCheckbox
     * @throws Zend_Form_Exception
     */
    public function addSimpleMultiCheckbox($name, $label, $options = [])
    {
        $el = new Siberian_Form_Element_MultiCheckbox($name);
        $this->addElement($el);
        $el->setIsFormHorizontal($this->is_form_horizontal);
        $el->setColor($this->color);
        $el
            ->setMultiOptions($options)
            ->setLabel($label)
            ->setNewDesign();
        return $el;
    }

    /**
     * @param $name
     * @param string $label
     * @param array $options
     * @return Siberian_Form_Element_Button
     * @throws Zend_Form_Exception
     */
    public function addSimpleImage($name, $label = "", $button_text = "", $options = [])
    {
        /** UID to link elements together */
        $uid = uniqid();
        $labelButton = $label;

        if (is_array($options) && isset($options["width"]) && isset($options["height"])) {
            $labelButton .= " " . $options["width"] . " x " . $options["height"];
        } else {
            $labelButton .= " 320 x 150";
        }

        /** Visual image button */
        $image_button = new Siberian_Form_Element_Button("{$name}_button");
        $this->addElement($image_button);
        $image_button->setLabel($labelButton);
        $image_button->setIsFormHorizontal($this->is_form_horizontal);
        $image_button->setColor($this->color);
        $image_button->setNewDesign();
        $image_button->addClass("feature-upload-button default_button");
        $image_button->addClass("add");
        $image_button->addClass("color-blue");
        $image_button->setAttrib("data-uid", $uid);
        $image_button->setAttrib("data-input", $name);
        $image_button->removeDecorator('DtDdWrapper');


        if (is_array($options) && isset($options["width"]) && isset($options["height"])) {
            $image_button->setAttrib("data-width", $options["width"]);
            $image_button->setAttrib("data-height", $options["height"]);
        } else {
            $image_button->setAttrib("data-width", "320");
            $image_button->setAttrib("data-height", "150");
        }

        /** Fake uploader */
        $image_input = new Siberian_Form_Element_File("{$name}_fake_files", __("uploader"));
        $this->addElement($image_input);
        $image_input->setAttrib("id", "{$name}_fake_files");
        $image_input->setAttrib("data-url", "/template/crop/upload");
        $image_input->setAttrib("style", "display: none;");
        $image_input->setAttrib("name", "files[]");
        $image_input->addClass("feature-upload-input");
        $image_input->setAttrib("data-uid", $uid);
        if (isset($options["data-imagecolor"])) {
            $image_input->setAttrib("data-imagecolor", $options["data-imagecolor"]);
        }
        if (isset($options["data-forcecolor"])) {
            $image_input->setAttrib("data-forcecolor", $options["data-forcecolor"]);
        }


        /** Fake input for cropped image */
        $image_hidden = new Siberian_Form_Element_Hidden($name);
        $this->addElement($image_hidden);
        $image_hidden->setMinimalDecorator();
        $image_hidden->setAttrib("data-uid", $uid);
        $image_hidden->addClass("feature-upload-hidden");

        if (is_array($options) && isset($options["required"])) {
            $image_button->setRequired($options["required"]);
            $image_hidden->setRequired($options["required"]);
        }

        if (isset($options["cms-include"])) {
            $image_button->addClass("cms-include");
            $image_input->addClass("cms-include");
            $image_hidden->addClass("cms-include");
        }

        return $image_button;
    }

    /**
     * @param $name
     * @param string $label
     * @param array $options
     * @return Siberian_Form_Element_Button
     * @throws Zend_Form_Exception
     */
    public function addSimpleFile($name, $label = "", $options = [])
    {
        /** UID to link elements together */
        $uid = uniqid();

        /** Visual image button */
        $button = new Siberian_Form_Element_Button($name);
        $this->addElement($button);
        $button->setLabel($label);
        $button->setIsFormHorizontal($this->is_form_horizontal);
        $button->setColor($this->color);
        $button->setNewDesign();
        $button->addClass("feature-upload-file default_button");
        $button->addClass("add");
        $button->addClass("color-blue");
        $button->setAttrib("data-uid", $uid);
        $button->setAttrib("data-input", $name);
        $button->removeDecorator('DtDdWrapper');

        /** Fake uploader */
        $input_file = new Siberian_Form_Element_File("{$name}_hidden", __("uploader"));
        $this->addElement($input_file);
        if (isset($options["multiple"])) {
            $input_file->setAttrib("multiple", "multiple");
        }
        $input_file->setAttrib("id", "{$name}_hidden");
        $input_file->setAttrib("style", "display: none;");
        $input_file->setAttrib("name", "files[]");
        $input_file->addClass("feature-upload-file");
        $input_file->setAttrib("data-uid", $uid);
        $input_file->setAttrib("data-url", $this->getAction());

        return $button;
    }

    /**
     * @param $name
     * @param $label
     * @param null $min
     * @param null $max
     * @param bool $inclusive
     * @param string $step
     * @return Siberian_Form_Element_Number
     * @throws Zend_Form_Exception
     * @throws Zend_Validate_Exception
     */
    public function addSimpleNumber($name, $label, $min = null, $max = null, $inclusive = true, $step = 'any')
    {
        $el = new Siberian_Form_Element_Number($name);
        $this->addElement($el);
        $el->setIsFormHorizontal($this->is_form_horizontal);
        $el->setColor($this->color);
        $el->setDecorators(['ViewHelper', 'Label'])
            ->setLabel($label)
            ->setNewDesign();

        if (is_numeric($min)) {
            $el->setAttrib('min', $min);
            if ($inclusive) {
                $min = $min - $this->getUnit($min);
            }

            $el->addValidator(new Zend_Validate_GreaterThan($min));
        }

        if (is_numeric($max)) {
            $el->setAttrib("max", $max);
            if ($inclusive) {
                $max = $max + $this->getUnit($max);
            }

            $el->addValidator(new Zend_Validate_LessThan($max));
        }

        $el->setAttrib("step", $step);

        return $el;
    }

    /**
     * @param $name
     * @return Siberian_Form_Element_Hidden
     * @throws Zend_Form_Exception
     */
    public function addSimpleHidden($name)
    {
        $el = new Siberian_Form_Element_Hidden($name);
        $this->addElement($el);
        $el->setDecorators(['ViewHelper']);

        return $el;
    }

    /**
     * @param $name
     * @param $elements
     * @param string $label
     * @param null $options
     * @return Zend_Form
     * @throws Zend_Form_Exception
     */
    public function groupElements($name, $elements, $label = '', $options = null)
    {
        $displayGroup = $this->addDisplayGroup($elements, $name, $options);
        /**
         * @var Zend_Form_Decorator_Fieldset $fieldset
         */
        $this
            ->getDisplayGroup($name)
            ->getDecorator('Fieldset')
            ->setLegend($label)
            ->setOption('class', $options['class']);

        return $displayGroup;
    }

    /**
     * Append various markup along with the form
     *
     * @param $html_js_css
     */
    public function addMarkup($html_js_css)
    {
        $this->markup = $html_js_css;
    }

    /**
     * @param Zend_View_Interface|null $view
     * @return string
     */
    public function render(Zend_View_Interface $view = null)
    {
        if (!empty($this->confirm_text)) {
            $this->setAttrib("data-confirm", __js($this->confirm_text, "'"));
        }

        $content = parent::render($view);

        if (!empty($this->markup)) {
            $content .= $this->markup;
        }

        return $content;
    }

    /**
     * @param array $data
     * @return bool
     * @throws Zend_Form_Exception
     */
    public function isValid($data)
    {
        /** Removing all fake_files elements before validating. */
        $elements = $this->getElements();
        foreach ($elements as $element) {
            if (strpos($element->getName(), "fake_files") !== false) {
                $this->removeElement($element->getName());
            }
        }

        return parent::isValid($data);
    }

    /**
     * @return array|string
     */
    public function getTextErrors($for_javascript = false)
    {
        $errors = array_filter($this->getMessages());

        $text_error = [];
        $javascript_error = [];
        foreach ($errors as $name => $error) {
            /** Translating errors */
            $errs = [];
            foreach ($error as $err) {
                $errs[] = __($err);
            }
            $text_error[] = sprintf("%s: %s", ucfirst(__($name)), implode_polyfill(", ", $errs));
            $javascript_error[$name] = implode_polyfill("<br />", $errs);
        }

        if ($for_javascript) {
            return $javascript_error;
        }

        if (!empty($text_error)) {
            $text_error = "<div>" . __("Form has the following errors") . "<br />" . implode_polyfill("<br />", $text_error) . "</div>";
        } else {
            $text_error = "<div>" . __("Form has the following errors") . "</div>";
        }

        return $text_error;
    }

    /**
     * Helper to append class
     *
     * @param $new
     * @param $element
     * @param bool $isOption
     * @return mixed
     */
    public static function addClass($new, $element, $isOption = false)
    {
        if (is_null($element)) {
            return;
        }

        if ($isOption) {
            $existing = $element->getOption('class');
        } else {
            $existing = $element->getAttrib('class');
        }

        if ($existing == null) {
            $classes = [$new];
        } else {
            if (is_string($existing)) {
                $classes = explode(' ', $existing);
            } elseif (is_array($existing)) {
                $classes = $existing;
            }
            $classes[] = $new;
        }

        $classes = implode_polyfill(' ', $classes);

        if ($isOption) {
            $element->setOption('class', $classes);
        } else {
            $element->setAttrib('class', $classes);
        }

        return $element;
    }

    /**
     * @param $remove
     * @param $element
     * @param bool $isOption
     * @return mixed
     */
    public static function removeClass($remove, $element, $isOption = false)
    {
        if (is_null($element)) {
            return;
        }

        if ($isOption) {
            $existing = $element->getOption('class');
        } else {
            $existing = $element->getAttrib('class');
        }

        if (is_string($existing)) {
            $classes = explode(' ', $existing);
        } elseif (is_array($existing)) {
            $classes = $existing;
        } else {
            return $element;
        }

        foreach ($classes as $i => $class) {
            if ($remove == $class) {
                unset($classes[$i]);
            }
        }

        $classes = implode_polyfill(' ', $classes);

        if ($isOption) {
            $element->setOption('class', $classes);
        } else {
            $element->setAttrib('class', $classes);
        }

        return $element;
    }

    /**
     * Handles picture upload
     *
     * @param $optionValue
     * @param $object
     * @param $key
     * @param $value
     */
    public static function handlePicture($optionValue, $object, $key, $value)
    {
        if ($value == '_delete_') {
            $object->setData($key, '');
        } else if (file_exists(Core_Model_Directory::getBasePathTo('images/application' . $value))) {
            # Nothing changed, skip
        } else {
            $path_banner = Siberian_Feature::moveUploadedFile(
                $optionValue,
                Core_Model_Directory::getTmpDirectory() . '/' . $value,
                $value
            );
            $object->setData($key, $path_banner);
        }
    }

    /**
     * @return bool
     */
    public function getPresets()
    {
        return false;
    }
}