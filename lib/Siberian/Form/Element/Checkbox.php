<?php
/**
 * Class Siberian_Form_Element_Checkbox
 */
class Siberian_Form_Element_Checkbox extends Zend_Form_Element_Checkbox {

	/**
	 * @throws Zend_Form_Exception
	 */
	public function init() {
		$this->addPrefixPath('Siberian_Form_Decorator_', 'Siberian/Form/Decorator/', 'decorator');
		$this->addFilters(array('StringTrim', 'StripTags'));
		$this->setDecorators(array(
	  		'ViewHelper',
            array(
				'Description',
				array(
					'placement' => Zend_Form_Decorator_Abstract::APPEND,
					'class' => 'checkbox-label',
					'tag' => 'span',
					'escape' => false
            	)
			),
			array(
				array('html_label' => 'HtmlTag'),
				array(
					'tag' => 'label',
					'class' => 'checkbox',
					'for' => $this->getName()
            	),
			),
           	array(
				'Errors',
				array(
           			'placement'=>Zend_Form_Decorator_Abstract::PREPEND,
           			'class'=>'alert alert-error form-error',
				)
          	),
            array(
				array('controls' => 'HtmlTag'),
				array(
                	'tag' => 'div',
            	)
			),
            array('ControlGroup'),
	  	));
	  	
	}

	/**
	 * @return Zend_Form_Element
	 */
	public function setNewDesign() {
		$this->addClass('sb-form-checkbox');
		return $this->setDecorators(array(
	  		'ViewHelper',
			array(array('style' => 'HtmlTag'), array(
				'placement' => Zend_Form_Decorator_Abstract::APPEND,
				'tag'   => 'div',
				'class' => 'color-blue'
			)),
			array(array('wrapper' => 'HtmlTag'),array(
				'class' => 'col-sm-7'
			)),
			array('Description',array(
				'tag' 		=> 'span',
				'placement' => Zend_Form_Decorator_Abstract::APPEND,
            	'escape' 	=> false
			)),
			array('Label', array(
				'class' => 'sb-form-line-title col-sm-3',
				'requiredSuffix' => ' *',
				'placement' => Zend_Form_Decorator_Abstract::PREPEND,
			)),
			array('Errors',array(
           		'placement'	=> Zend_Form_Decorator_Abstract::PREPEND,
           		'class'		=> 'alert alert-error'
          	)),
            array('ControlGroup',array(
            	'class' => 'form-group sb-form-line'
            ))
		));
	}

	/**
	 * @param $new
	 * @return Siberian_Form_Element_Checkbox
	 */
	public function addClass($new) {
		Siberian_Form_Abstract::addClass($new, $this);
	    
	    return $this;
	}

	/**
	 * @param $title
	 * @return Siberian_Form_Element_Checkbox
	 */
	public function setTooltip($title){
		$decorator = $this->getDecorator('ControlGroup');
		Siberian_Form_Abstract::addClass('sb-tooltip', $decorator, true);
		$decorator->setOption('title', str_replace('"', "'", $title));
		return $this;
	}

	/**
	 *
	 */
	public function simpleDesign(){
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
			array(array('style' => 'HtmlTag'), array(
				'placement' => Zend_Form_Decorator_Abstract::APPEND,
				'tag'   => 'div',
				'class' => 'control__indicator'
			)),
			array(array('controls' => 'HtmlTag'), array(
				'tag'   => 'div',
				'class' => 'controls',
			)),
			array('Label', array(
				'class' => 'control-label control control--checkbox',
				'requiredSuffix' => ' *',
				'placement' => Zend_Form_Decorator_Abstract::PREPEND
			)),
			array('ControlGroup')
		));
	}

}