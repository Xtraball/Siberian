<?php
/**
 * Class Siberian_Form_Element_Text
 */
class Siberian_Form_Element_Text extends Zend_Form_Element_Text {

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
                'class' => 'help-inline'
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
	 * @return Siberian_Form_Element_Text
	 */
	public function setNewDesign(){
		$this->addClass('sb-input-'.$this->getName());
		$this->addClass("input-flat");

		return $this->setDecorators(array(
	  		'ViewHelper',
			array(array('wrapper'=>'HtmlTag'),array(
				'class' => 'col-sm-7'
			)),
            array('Description', array(
                'placement' => Zend_Form_Decorator_Abstract::APPEND,
                'class' => 'sb-form-line-complement',
            	'escape' => false
            )),
            array('Label', array(
                'class' => 'sb-form-line-title col-sm-3',
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
	 * @param string $description
	 * @return Siberian_Form_Element_Text
	 */
	public function setDescription($description){
		$this->addClass('sb-form-float');
		return parent::setDescription($description);
	}

	/**
	 * @param $new
	 * @return Siberian_Form_Element_Text
	 */
	public function addClass($new) {
	    return Siberian_Form_Abstract::addClass($new, $this);
	}

	/**
	 * @param $title
	 * @return Siberian_Form_Element_Text
	 * @throws Zend_Form_Exception
	 */
	public function setTooltip($title){
		$this->addClass('sb-tooltip');
		$this->setAttrib('title', str_replace('"', "'", $title));
		return $this;
	}

	/**
	 * @param $regexPhp
	 * @param $regexJs
	 * @param $error
	 * @param bool $empty
	 * @return Siberian_Form_Element_Text
	 * @throws Zend_Form_Exception
	 */
	public function setRegex($regexPhp, $regexJs, $error, $empty = false){
		$regexValidator = new Zend_Validate_Regex(array('pattern' => $regexPhp));
		
		$this
			->addvalidator($regexValidator)
			->addClass('data-validate-regex')
			->setAttrib('data-regex-pattern', $regexJs)
			->setAttrib('data-regex-error', $error)
		;
		
		if($empty) {
			$this
				->setAttrib('data-regex-empty', 'true')
			;
		}
		
		return $this;
	}
}