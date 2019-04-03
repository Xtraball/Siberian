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
		$this->addFilters(['StringTrim','StripTags']);
		$this->setDecorators([
	  		'ViewHelper',
           	['Errors', [
           		'placement'=>Zend_Form_Decorator_Abstract::PREPEND,
           		'class'=>'alert alert-error form-error']
            ],
            ['Description', [
                'placement' => Zend_Form_Decorator_Abstract::APPEND,
                'class' => 'help-block',
                'escape' => false,
            ]],
            [['controls' => 'HtmlTag'], [
                'tag'   => 'div',
                'class' => 'controls',
            ]],
            ['Label', [
                'class' => 'control-label',
                'requiredSuffix' => ' *',
                'escape' => false,
                'placement' => Zend_Form_Decorator_Abstract::PREPEND
            ]],
            ['ControlGroup', ['class' => 'control-group']]
        ]);
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

		return $this->setDecorators([
	  		'ViewHelper',
			[['wrapper'=>'HtmlTag'], [
				'class' => ' '.$element_class
            ]],
            ['Description', [
                'placement' => Zend_Form_Decorator_Abstract::APPEND,
                'class' => 'sb-form-line-complement sb-form-description '.$error_class,
                'escape' => false
            ]],
            ['Label', [
                'class' => 'sb-form-line-title '.$label_class,
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
            	'class' => 'form-group sb-form-line',
            	'id'=>'control-group_'.$this->getId()
            ]]
        ]);
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
