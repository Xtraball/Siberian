<?php
/**
 * Class Siberian_Form_Element_Password
 */
class Siberian_Form_Element_Password extends Zend_Form_Element_Password {

    /**
     * @var bool
     */
    public $is_form_horizontal = true;

    /**
     * @param $boolean
     */
    public function setIsFormHorizontal($boolean) {
        $this->is_form_horizontal = $boolean;
    }

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
	 * @throws Zend_Form_Exception
	 */
	public function init(){
		$this->addPrefixPath('Siberian_Form_Decorator_', 'Siberian/Form/Decorator/', 'decorator');
		$this->addFilters(array('StringTrim','StripTags'));
		$this->setDecorators(array(
	  		'ViewHelper',
           	array('Errors',array(
           		'placement'=>Zend_Form_Decorator_Abstract::PREPEND,
           		'class'=>'alert alert-error form-error')
          	),
            array('Description', array(
                'placement' => Zend_Form_Decorator_Abstract::APPEND,
                'class' => 'help-block'
            )),
            array(array('controls' => 'HtmlTag'), array(
                'tag'   => 'div',
                'class' => 'controls',
            )),
            array('Label', array(
                'class' => 'control-label',
                'requiredSuffix' => ' *',
                'placement' => Zend_Form_Decorator_Abstract::PREPEND
            )),
            array('ControlGroup')
	  	));
	}

	/**
	 * @return Siberian_Form_Element_Password
	 */
	public function setNewDesign(){
		$this->addClass("input-flat");

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
			array(array('wrapper'=>'HtmlTag'),array(
				'class' => ' '.$element_class
			)),
            array('Description', array(
                'placement' => Zend_Form_Decorator_Abstract::APPEND,
                'class' => 'sb-form-line-complement sb-form-description '.$error_class,
            	'escape' => false
            )),
            array('Label', array(
                'class' => 'sb-form-line-title '.$label_class,
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
	 * @return Siberian_Form_Element_Password
	 */
    public function addClass($new) {
	    return Siberian_Form_Abstract::addClass($new, $this);
	}

	/**
	 * @param $title
	 * @return Siberian_Form_Element_Password
	 * @throws Zend_Form_Exception
	 */
	public function setTooltip($title) {
		$this->addClass('sb-tooltip');
		$this->setAttrib('title', str_replace('"', "'", $title));
		return $this;
	}

}