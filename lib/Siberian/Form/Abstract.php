<?php
/**
 * Class Siberian_Form_Abstract
 */
abstract class Siberian_Form_Abstract extends Zend_Form {

    const DATEPICKER = "datepicker";
    const TIMEPICKER = "timepicker";
    const DATETIMEPICKER = "datetimepicker";

    /**
     * @var bool
     * @deprecated
     */
    public $bind_js = false;

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

    public function init() {
        parent::init();

        $this
            ->setMethod(Zend_Form::METHOD_POST)
            ->setAttrib("class", "form sb-form feature-form")
        ;

        if($this->is_form_horizontal) {
            self::addClass("form-horizontal", $this);
        }

        $this->setDecorators(array('FormElements','Form'));
    }

    /**
     * @param $boolean
     */
    public function setIsFormHorizontal($boolean) {
        $this->is_form_horizontal = $boolean;

        return $this;
    }

    /**
     * @param $delete_text
     */
    public function setConfirmText($confirm_text) {
        $this->confirm_text = __($confirm_text);
    }

    /**
     * @param $value
     * @return Siberian_Form_Abstract
     */
    public function setBindJs($value) {
        $this->bind_js = $value;

        return $this;
    }

    /**
     * @param $value_id
     * @return $this
     */
    public function setValueId($value_id) {
        if(!is_null($this->getElement("value_id"))) {
            $el_value_id = $this->getElement("value_id");
            $el_value_id->setValue($value_id);
        }

        return $this;
    }

    /**
     * @param $name
     * @return null|Zend_Form_DisplayGroup
     * @throws Zend_Form_Exception
     */
    public function addNav($name, $save_text = "OK", $display_back_button = true, $with_label = false) {

        $elements = array();

        $back_button = new Siberian_Form_Element_Button("sb-back");
        $back_button->setAttrib("escape", false);
        $back_button->setLabel("<i class=\"fa fa-chevron-left icon icon-chevron-left\"></i>");
        $back_button->addClass("pull-left feature-back-button default_button");
        $back_button->setColor($this->color);
        $back_button->setBackDesign();

        if($display_back_button) {
            $elements[] = $back_button;
        }

        $submit_button = new Siberian_Form_Element_Submit(__($save_text));
        $submit_button->addClass("pull-right default_button");
        $submit_button->setColor($this->color);
        $submit_button->setNewDesign();

        if($with_label) {
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
     */
    public function removeNav($name) {
        $display_group = $this->getDisplayGroup($name);
        foreach($display_group->getElements() as $element) {
            $this->removeElement($element->getName());
        }
        $this->removeDisplayGroup($name);
    }

    /**
     * @param null $label
     * @return Siberian_Form_Element_Submit
     * @throws Zend_Form_Exception
     */
    public function addSubmit($label = null, $name = null) {
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
            ->setDecorators(array(
                'ViewHelper'
            ))
        ;
        $submit->setIsFormHorizontal($this->is_form_horizontal);
        $submit->setColor($this->color);
        $submit->setNewDesign();
        return $submit;
    }

    /**
     * @param null $label
     * @return Siberian_Form_Element_Submit
     * @throws Zend_Form_Exception
     */
    public function addMiniSubmit($label = null, $label_off = null, $label_on = null) {
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
     * Default generic toggle state
     *
     * @param $element
     * @return mixed
     */
    public function defaultToggle($element, $on_text = "Enable", $off_text = "Disable") {
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
    public function setToggleState($state) {
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
    public function addSimpleText($name, $label = "", $placeholder = false) {
        $el = new Siberian_Form_Element_Text($name);
        $this->addElement($el);
        if ($placeholder) {
            $el->setAttrib('placeholder', $label);
            $el->setDecorators(array('ViewHelper'));
        } else {
            $el->setLabel($label);
            $el->setDecorators(array('ViewHelper', 'Label'));
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
     */
    public function addSimpleButton($name, $label = "", $placeholder = false) {
        $el = new Siberian_Form_Element_Button($name);
        $this->addElement($el);
        if ($placeholder) {
            $el->setAttrib('placeholder', $label);
            $el->setDecorators(array('ViewHelper'));
        } else {
            $el->setLabel($label);
            $el->setDecorators(array('ViewHelper', 'Label'));
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
     * @return Siberian_Form_Element_Slider
     */
    public function addSimpleSlider($name, $label = "", $options = array(), $with_indicator = true) {
        if($with_indicator) {
            $options["indicator"] = true;
        }
        $el = new Siberian_Form_Element_Slider($name, $options);
        $this->addElement($el);
        $el->setLabel($label);
        $el->setDecorators(array('ViewHelper', 'Label'));
        $el->setIsFormHorizontal($this->is_form_horizontal);
        $el->setColor($this->color);
        $el->setNewDesign();
        return $el;
    }

    /**
     * @param $name
     * @param string $label
     * @param bool $placeholder
     * @return Siberian_Form_Element_Text
     * @throws Zend_Form_Exception
     */
    public function addSimpleDatetimepicker($name, $label = "", $placeholder = false, $type = self::DATEPICKER, $format = false) {
        $el = new Siberian_Form_Element_Text($name);
        $this->addElement($el);
        if ($placeholder) {
            $el->setAttrib('placeholder', $label);
            $el->setDecorators(array('ViewHelper'));
        } else {
            $el->setLabel($label);
            $el->setDecorators(array('ViewHelper', 'Label'));
        }
        $el->setAttrib('data-datetimepicker', $type);
        if($format) {
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
     * @return Siberian_Form_Element_Text
     * @throws Zend_Form_Exception
     */
    public function addSimplePassword($name, $label = "", $placeholder = false) {
        $el = new Siberian_Form_Element_Password($name);
        $this->addElement($el);
        if ($placeholder) {
            $el->setAttrib('placeholder', $label);
            $el->setDecorators(array('ViewHelper'));
        } else {
            $el->setLabel($label);
            $el->setDecorators(array('ViewHelper', 'Label'));
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
    public function addSimpleTextarea($name, $label = "", $placeholder = false, $options = array()) {
        $el = new Siberian_Form_Element_Textarea($name);
        $this->addElement($el);
        if ($placeholder) {
            $el->setAttrib('placeholder', $label);
            $el->setDecorators(array('ViewHelper'));
        } else {
            $el->setLabel($label);
            $el->setDecorators(array('ViewHelper', 'Label'));
        }

        if(isset($options["ckeditor"])) {
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
    public function addSimpleSelect($name, $label = "", $options = array()) {
        $el = new Siberian_Form_Element_Select($name);
        $this->addElement($el);
        $el->setIsFormHorizontal($this->is_form_horizontal);
        $el->setColor($this->color);
        $el
            ->setNewDesign()
            ->setLabel($label)
            ->setMultiOptions($options)
        ;
        return $el;
    }

    /**
     * @param $name
     * @param string $label
     * @param $options
     * @return Siberian_Form_Element_Multiselect
     * @throws Zend_Form_Exception
     */
    public function addSimpleMultiSelect($name, $label = "", $options) {
        $el = new Siberian_Form_Element_Multiselect($name);
        $this->addElement($el);
        $el->setIsFormHorizontal($this->is_form_horizontal);
        $el->setColor($this->color);
        $el
            ->setNewDesign()
            ->setLabel($label)
            ->setMultiOptions($options)

        ;
        return $el;
    }

    /**
     * @param $name
     * @param $label
     * @return Siberian_Form_Element_Checkbox
     * @throws Zend_Form_Exception
     */
    public function addSimpleCheckbox($name, $label) {
        $el = new Siberian_Form_Element_Checkbox($name);
        $this->addElement($el);
        $el->setIsFormHorizontal($this->is_form_horizontal);
        $el->setColor($this->color);
        $el
            ->setLabel($label)
            ->setNewDesign()
        ;

        return $el;
    }

    /**
     * @param $name
     * @param $label
     * @param array $options
     * @return Siberian_Form_Element_Radio
     * @throws Zend_Form_Exception
     */
    public function addSimpleRadio($name, $label, $options = array()) {
        $el = new Siberian_Form_Element_Radio($name);
        $this->addElement($el);
        $el->setIsFormHorizontal($this->is_form_horizontal);
        $el->setColor($this->color);
        $el
            ->addMultiOptions($options)
            ->setLabel($label)
            ->setNewDesign()
        ;
        return $el;
    }

    /**
     * @param $name
     * @param $label
     * @param $options
     * @return Siberian_Form_Element_MultiCheckbox
     * @throws Zend_Form_Exception
     */
    public function addSimpleMultiCheckbox($name, $label, $options) {
        $el = new Siberian_Form_Element_MultiCheckbox($name);
        $this->addElement($el);
        $el->setIsFormHorizontal($this->is_form_horizontal);
        $el->setColor($this->color);
        $el
            ->setMultiOptions($options)
            ->setLabel($label)
            ->setNewDesign()
        ;
        return $el;
    }

    /**
     * @param $name
     * @param string $label
     * @param array $options
     * @return Siberian_Form_Element_Button
     * @throws Zend_Form_Exception
     */
    public function addSimpleImage($name, $label = "", $button_text = "", $options = array()) {
        /** UID to link elements together */
        $uid = uniqid();

        $button_text = !empty($button_text) ? $button_text : __("Add a picture");

        if(is_array($options) && isset($options["width"]) && isset($options["height"])) {
            $button_text .= " (".$options["width"]."x".$options["height"].")";
        } else {
            $button_text .= " (320x150)";
        }

        /** Visual image button */
        $image_button = new Siberian_Form_Element_Button($button_text);
        $this->addElement($image_button);
        $image_button->setLabel($label);
        $image_button->setIsFormHorizontal($this->is_form_horizontal);
        $image_button->setColor($this->color);
        $image_button->setNewDesign();
        $image_button->addClass("feature-upload-button default_button");
        $image_button->addClass("add");
        $image_button->addClass("color-blue");
        $image_button->setAttrib("data-uid", $uid);
        $image_button->setAttrib("data-input", $name);
        $image_button->removeDecorator('DtDdWrapper');

        if(is_array($options) && isset($options["width"]) && isset($options["height"])) {
            $image_button->setAttrib("data-width", $options["width"]);
            $image_button->setAttrib("data-height", $options["height"]);
        } else {
            $image_button->setAttrib("data-width", "320");
            $image_button->setAttrib("data-height", "150");
        }

        /** Fake uploader */
        $image_input = new Siberian_Form_Element_File("{$name}_fake_files", __("uploader"));
        $this->addElement($image_input);
        $image_input->setAttrib("data-url", "/template/crop/upload");
        $image_input->setAttrib("style", "display: none;");
        $image_input->setAttrib("name", "files[]");
        $image_input->addClass("feature-upload-input");
        $image_input->setAttrib("data-uid", $uid);


        /** Fake input for cropped image */
        $image_hidden = new Siberian_Form_Element_Hidden($name);
        $this->addElement($image_hidden);
        $image_hidden->setMinimalDecorator();
        $image_hidden->setAttrib("data-uid", $uid);
        $image_hidden->addClass("feature-upload-hidden");

        if(is_array($options) && isset($options["required"])) {
            $image_button->setRequired($options["required"]);
            $image_hidden->setRequired($options["required"]);
        }

        return $image_button;
    }

    /**
     * @param $name
     * @param string $label
     * @param array $options
     * @return Siberian_Form_Element_Button
     */
    public function addSimpleFile($name, $label = "", $options = array()) {
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
        $input_file->setAttrib("style", "display: none;");
        $input_file->setAttrib("name", "files[]");
        $input_file->addClass("feature-upload-file");
        $input_file->setAttrib("data-uid", $uid);
        $input_file->setAttrib("data-url", $this->getAction());

        return $button;
    }

    public function addSimpleNumber($name, $label, $min = null, $max = null, $inclusive = true, $step = "any") {
        $el = new Siberian_Form_Element_Number($name);
        $this->addElement($el);
        $el->setIsFormHorizontal($this->is_form_horizontal);
        $el->setColor($this->color);
        $el->setDecorators(array('ViewHelper', 'Label'))
            ->setLabel($label)
            ->setNewDesign()
            ;

        if(is_numeric($min)) {
            if(!$inclusive)
                $min++;

            $el->setAttrib("min", $min);
            $el->addValidator(new Zend_Validate_GreaterThan($min));
        }

        if(is_numeric($max)) {
            if(!$inclusive)
                $max++;

            $el->setAttrib("max", $max);
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
    public function addSimpleHidden($name){
        $el = new Siberian_Form_Element_Hidden($name);
        $this->addElement($el);
        $el->setDecorators(array('ViewHelper'));

        return $el;
    }

    /**
     * @param $name
     * @param $elements
     * @param $label
     * @return Zend_Form
     * @throws Zend_Form_Exception
     */
    public function groupElements($name, $elements, $label = "") {
        $display_group = $this->addDisplayGroup($elements, $name);
        $fieldset = $this->getDisplayGroup($name)->getDecorator("Fieldset")
            ->setLegend($label)
        ;

        return $display_group;
    }

    /**
     * Append various markup along with the form
     *
     * @param $html_js_css
     */
    public function addMarkup($html_js_css) {
        $this->markup = $html_js_css;
    }

    /**
     * @param Zend_View_Interface|null $view
     * @return string
     */
    public function render(Zend_View_Interface $view = null) {
        if(!empty($this->confirm_text)) {
            $this->setAttrib("data-confirm", __js($this->confirm_text, "'"));
        }

        $content = parent::render($view);

        if(!empty($this->markup)) {
            $content .= $this->markup;
        }

        return $content;
    }

    /**
     * @param array $data
     * @return bool
     * @throws Zend_Form_Exception
     */
    public function isValid($data) {
        /** Removing all fake_files elements before validating. */
        $elements = $this->getElements();
        foreach($elements as $element) {
            if(strpos($element->getName(), "fake_files") !== false) {
                $this->removeElement($element->getName());
            }
        }

        return parent::isValid($data);
    }

    /**
     * @return array|string
     */
    public function  getTextErrors($for_javascript = false) {
        $errors = array_filter($this->getMessages());

        $text_error = array();
        $javascript_error = array();
        foreach($errors as $name => $error) {
            /** Translating errors */
            $errs = array();
            foreach($error as $err) {
                $errs[] = __($err);
            }
            $text_error[] = sprintf("%s: %s", ucfirst(__($name)), implode(", ", $errs));
            $javascript_error[$name] = implode("<br />", $errs);
        }

        if($for_javascript) {
            return $javascript_error;
        }

        if(!empty($text_error)) {
            $text_error = "<div>".__("Form has the following errors")."<br />".implode("<br />", $text_error)."</div>";
        } else {
            $text_error = "<div>".__("Form has the following errors")."</div>";
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
    public static function addClass($new, $element, $isOption = false) {
        if(is_null($element)) {
            return;
        }

        if($isOption) {
            $existing = $element->getOption('class');
        } else {
            $existing = $element->getAttrib('class');
        }

        if($existing == null) {
            $classes = array($new);
        } else {
            if(is_string($existing)) {
                $classes = explode(' ', $existing);
            } elseif(is_array($existing)) {
                $classes = $existing;
            }
            $classes[] = $new;
        }

        $classes = implode(' ', $classes);

        if($isOption) {
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
    public static function removeClass($remove, $element, $isOption = false) {
        if(is_null($element)) {
            return;
        }

        if($isOption) {
            $existing = $element->getOption('class');
        } else {
            $existing = $element->getAttrib('class');
        }

        if(is_string($existing)) {
            $classes = explode(' ', $existing);
        } elseif(is_array($existing)) {
            $classes = $existing;
        } else {
            return $element;
        }

        foreach($classes as $i => $class) {
            if($remove == $class) {
                unset($classes[$i]);
            }
        }

        $classes = implode(' ', $classes);

        if($isOption) {
            $element->setOption('class', $classes);
        } else {
            $element->setAttrib('class', $classes);
        }

        return $element;
    }

    /**
     * @return bool
     */
    public function getPresets() {
        return false;
    }
}