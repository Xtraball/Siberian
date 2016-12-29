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
		$this->addFilters(array('StringTrim','StripTags'));
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

		return $this->setDecorators(array(
	  		'ViewHelper',
            array('Description', array(
                'placement' => Zend_Form_Decorator_Abstract::APPEND,
                'class' => 'sb-form-line-complement sb-form-description '.$error_class,
            	'escape' => false
            )),
            array('Label', array(
                'class' => 'sb-form-line-title',
                'requiredSuffix' => ' *',
                'placement' => Zend_Form_Decorator_Abstract::PREPEND,
            )),
           	array('Errors',array(
           		'placement'=>Zend_Form_Decorator_Abstract::PREPEND,
           		'class'=>'alert alert-error'
          	)),
            array(array('cb' => 'HtmlTag'),array(
            	'class' => 'sb-cb',
            	'placement' => Zend_Form_Decorator_Abstract::APPEND,
            )),
            array('ControlGroup',array(
            	'class' => 'form-group sb-form-line'
            ))
	  	));
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
			return $this->setDecorators(array(
					'ViewHelper',
					array('Errors', array('class' => 'alert alert-error form-error', 'placement' => Zend_Form_Decorator_Abstract::PREPEND))
			));
		} else {
			return $this->setDecorators(array('ViewHelper'));
		}
	}
	
}