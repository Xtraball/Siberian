<?php
/**
 * Class Siberian_Form_Abstract
 */
abstract class Siberian_Form_Abstract extends Zend_Form {

    public $bind_js = false;

    public function init() {
        parent::init();

        $this
            ->setMethod(Zend_Form::METHOD_POST)
            ->setAttrib("class", "form form-horizontal sb-form feature-form")
        ;

        $this->setDecorators(array('FormElements','Form'));
    }

    /**
     * @param $value
     * @return $this
     */
    public function setBindJs($value) {
        $this->bind_js = $value;

        return $this;
    }

    /**
     * @param $value_id
     */
    public function setValueId($value_id) {
        if(!is_null($this->getElement("value_id"))) {
            $el_value_id = $this->getElement("value_id");
            $el_value_id->setValue($value_id);
        }
    }

    /**
     * @param $name
     * @return null|Zend_Form_DisplayGroup
     * @throws Zend_Form_Exception
     */
    public function addNav($name) {

        $back_button = new Siberian_Form_Element_Button("sb-back");
        $back_button->setAttrib("escape", false);
        $back_button->setLabel("<i class=\"fa fa-chevron-left\"></i>");
        $back_button->addClass("pull-left feature-back-button");
        $back_button->setBackDesign();

        $submit_button = new Siberian_Form_Element_Submit(__("OK"));
        $submit_button->addClass("pull-right");
        $submit_button->setNewDesign();

        $this->addDisplayGroup(array($back_button, $submit_button), $name);

        $nav_group = $this->getDisplayGroup($name);
        $nav_group->removeDecorator('DtDdWrapper');
        $nav_group->setAttrib("class", "sb-nav");

        return $nav_group;
    }

    /**
     * @param null $label
     * @return Siberian_Form_Element_Submit
     * @throws Zend_Form_Exception
     */
    public function addSubmit($label = null) {
        if ($label == null) $label = 'Rechercher';
        $submit = new Siberian_Form_Element_Submit('go');
        $this->addElement($submit);
        $submit
            ->setLabel($label)
            ->setAttrib('class', 'btn')
            ->setDecorators(array(
                'ViewHelper'
            ))
        ;
        $submit->setNewDesign();
        return $submit;
    }

    /**
     * @param null $label
     * @return Siberian_Form_Element_Submit
     * @throws Zend_Form_Exception
     */
    public function addMiniSubmit($label = null) {
        if ($label == null) {
            $label = "<i class='fa fa-times company-manage-delete'></i>";
        }
        $submit = new Siberian_Form_Element_Button($label);
        $this->addElement($submit);
        $submit->setLabel($label);
        $submit->setMiniDeleteDesign();

        return $submit;
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
        $el->setNewDesign();
        return $el;
    }

    /**
     * @param $name
     * @param string $label
     * @return Siberian_Form_Element_Textarea
     * @throws Zend_Form_Exception
     */
    public function addSimpleTextarea($name, $label = "", $placeholder = false) {
        $el = new Siberian_Form_Element_Textarea($name);
        $this->addElement($el);
        if ($placeholder) {
            $el->setAttrib('placeholder', $label);
            $el->setDecorators(array('ViewHelper'));
        } else {
            $el->setLabel($label);
            $el->setDecorators(array('ViewHelper', 'Label'));
        }
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
    public function addSimpleSelect($name, $label = "", $options) {
        $el = new Siberian_Form_Element_Select($name);
        $this->addElement($el);
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

        /** Visual image button */
        $image_button = new Siberian_Form_Element_Button($button_text);
        $this->addElement($image_button);
        $image_button->setLabel($label);
        $image_button->setNewDesign();
        $image_button->addClass("feature-upload-button");
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
     * @param Zend_View_Interface|null $view
     * @return string
     */
    public function render(Zend_View_Interface $view = null)
    {
        $content = parent::render($view);

        /** Append JS Binder to the end */
        if($this->bind_js) {
            $js_binder = "
<script type=\"text/javascript\">
    if(typeof bindForms == \"function\") {
        bindForms();
    }
</script>";
        }

        $content .= $js_binder;

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
    public function getTextErrors() {
        $errors = array_filter($this->getErrors());

        $text_error = array();
        foreach($errors as $name => $error) {
            /** Translating errors */
            $errs = array();
            foreach($error as $err) {
                $errs[] = __($err);
            }
            $text_error[] = __("Field '%s': %s", __($name), implode(", ", $errs));
        }
        if(!empty($text_error)) {
            $text_error = "<div>".__("Form has errors")."<br />".implode("<br />", $text_error)."</div>";
        } else {
            $text_error = "<div>".__("Form has errors")."</div>";
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
}