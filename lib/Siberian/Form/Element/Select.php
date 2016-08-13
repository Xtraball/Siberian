<?php
/**
 * Class Siberian_Form_Element_Select
 */
class Siberian_Form_Element_Select extends Zend_Form_Element_Select {
	
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
	public function setNewDesign(){
	  	$this->addClass('sb-select styled-select color-blue');
		return $this->setDecorators(array(
	  		'ViewHelper',
			array(array('wrapper'=>'HtmlTag'),array(
				'class' => 'col-sm-7'
			)),
            array('Description', array(
                'placement' => Zend_Form_Decorator_Abstract::APPEND,
                'class' => 'sb-form-line-complement'
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
            	'class' => 'form-group sb-form-line',
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