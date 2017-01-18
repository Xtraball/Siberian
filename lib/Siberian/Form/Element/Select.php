<?php
/**
 * Class Siberian_Form_Element_Select
 */
class Siberian_Form_Element_Select extends Zend_Form_Element_Select {

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
     * @var string
     */
    protected $label_cols = "col-md-3";

    /**
     * @var string
     */
    protected $input_cols = "col-md-7";

    /**
     * @var string
     */
    protected $offset_cols = "col-md-offset-3";

    /**
     * @var string
     */
    protected $error_cols = "col-md-7";

    /**
     * @param $color
     */
    public function setColor($color) {
        $this->color = $color;
    }

    /**
     * @param $label
     * @param $input
     * @param $offset
     * @param null $error
     * @return $this
     */
    public function setCols($label, $input, $offset, $error = null) {
        $this->label_cols = $label;
        $this->input_cols = $input;
        $this->offset_cols = $offset;
        $this->error_cols = ($error != null) ? $error : $input;

        return $this;
    }

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
            array('ControlGroup',array('class' => 'control-group'))
	  	));
	}
	
	/**
	 * @return Siberian_Form_Element_Select
	 */
	public function setNewDesign($class = ""){
	  	$this->addClass('sb-select styled-select '.$this->color.' form-control no-dk');

        if($this->is_form_horizontal) {
            $label_class = "{$this->label_cols}";
            $element_class = "{$this->input_cols}";
            $error_class = "{$this->error_cols} {$this->offset_cols}";
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
                'class' => 'sb-form-line-complement sb-form-description '.$error_class
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
            	'class' => 'form-group sb-form-line '.$class,
            	'id'=>'control-group_'.$this->getId()
            ))
	  	));
	}

	/**
	 * @param $width
	 * @return Siberian_Form_Element_Select
	 */
	public function setSelectWidth($width){
		$wrapper = $this->getDecorator('wrapper');
		$wrapper->setOption('class', 'sb-select-container sb-form-float '.$width);
		return $this;
	}

	/**
	 * @return Siberian_Form_Element_Select
	 */
	public function setSmall() {
		$this->setSelectWidth('sb-small');
		return $this;
	}

	/**
	 * @param $new
	 * @return Siberian_Form_Element_Select
	 */
    public function addClass($new) {
	    return Siberian_Form_Abstract::addClass($new, $this);
	}

}