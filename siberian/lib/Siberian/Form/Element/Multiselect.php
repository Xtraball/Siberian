<?php
/**
 * Class Siberian_Form_Element_Multiselect
 */
class Siberian_Form_Element_Multiselect extends Zend_Form_Element_Multiselect {

    /**
     * @var bool
     */
    public $is_form_horizontal = true;

    /**
     * @var string
     */
    public $color = "color-blue";

    /**
     * @param $boolean
     */
    public function setIsFormHorizontal($boolean) {
        $this->is_form_horizontal = $boolean;
    }

    /**
     * @param $color
     */
    public function setColor($color) {
        $this->color = $color;
    }

	/**
	 * @throws Zend_Form_Exception
	 */
	public function init() {
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
            array('ControlGroup',array('class' => 'control-group'))
	  	));
	}
	
	/**
	 * @return Siberian_Form_Element_Multiselect
	 */
	public function setNewDesign(){
	  	$this->addClass('sb-select');
	  	$this->addClass('input-flat');
	  	$this->addClass('form-control no-dk');

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
            	'class' => 'form-group sb-form-line',
            	'id'=>'control-group_'.$this->getId()
            ))
	  	));
	}

	/**
	 * @param $width
	 * @return Siberian_Form_Element_Multiselect
	 */
	public function setSelectWidth($width){
		$wrapper = $this->getDecorator('wrapper');
		$wrapper->setOption('class', 'sb-select-container sb-form-float '.$width);
		return $this;
	}

	/**
	 * @return Siberian_Form_Element_Multiselect
	 */
	public function setSmall() {
		$this->setSelectWidth('sb-small');
		return $this;
	}

	/**
	 * @param $new
	 * @return Siberian_Form_Element_Multiselect
	 */
    public function addClass($new) {
	    return Siberian_Form_Abstract::addClass($new, $this);
	}

}
