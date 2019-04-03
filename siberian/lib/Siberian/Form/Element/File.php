<?php
/**
 * Class Siberian_Form_Element_File
 */
class Siberian_Form_Element_File extends Zend_Form_Element_File {

    /**
     * @var bool
     */
    public $is_form_horizontal = true;

    /**
     * @var string
     */
    public $color = "color-blue";

    /**
     * @param $color
     */
    public function setColor($color) {
        $this->color = $color;
    }

    /**
     * @param $boolean
     */
    public function setIsFormHorizontal($boolean) {
        $this->is_form_horizontal = $boolean;
    }

	/**
	 * @throws Zend_Form_Exception
	 */
	public function init() {
		$this->addFilters(['StringTrim','StripTags']);
	}

	/**
	 * @return Siberian_Form_Element_File
	 */
	public function setNewDesign() {
        if($this->is_form_horizontal) {
            $label_class = "col-sm-3";
            $element_class = "col-sm-7";
            $error_class = "col-sm-7 col-sm-offset-3";
        } else {
            $label_class = "";
            $element_class = "";
            $error_class = "";
        }

		return $this->setDecorators([
	  		'ViewHelper',
            ['Description', [
                'placement' => Zend_Form_Decorator_Abstract::APPEND,
                'class' => 'sb-form-line-complement sb-form-description '.$error_class,
            	'escape' => false
            ]],
            ['Label', [
                'class' => 'sb-form-line-title',
                'requiredSuffix' => ' *',
                'escape' => false,
                'placement' => Zend_Form_Decorator_Abstract::PREPEND,
            ]],
           	['Errors', [
           		'placement'=>Zend_Form_Decorator_Abstract::PREPEND,
           		'class'=>'alert alert-error'
            ]],
            [['cb' => 'HtmlTag'], [
            	'class' => 'sb-cb',
            	'placement' => Zend_Form_Decorator_Abstract::APPEND,
            ]],
            ['ControlGroup', [
            	'class' => 'form-group sb-form-line'
            ]]
        ]);
	}

	/**
	 * @param $new
	 * @return mixed
	 */
	public function addClass($new) {
		return Siberian_Form_Abstract::addClass($new, $this);
	}

	/**
	 * @param bool $withError
	 * @return Zend_Form_Element
	 */
	public function setMinimalDecorator($withError = false) {
		if($withError) {
			return $this->setDecorators([
					'ViewHelper',
					['Errors', ['class' => 'alert alert-error form-error', 'placement' => Zend_Form_Decorator_Abstract::PREPEND]]
            ]);
		} else {
			return $this->setDecorators(['ViewHelper']);
		}
	}
	
}