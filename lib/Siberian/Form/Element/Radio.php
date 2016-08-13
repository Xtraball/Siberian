<?php
/**
 * Class Siberian_Form_Element_Radio
 */
class Siberian_Form_Element_Radio extends Zend_Form_Element_Radio {

	/**
	 * @throws Zend_Form_Exception
	 */
	public function init() {
		$this->setDisableLoadDefaultDecorators(true);
		$this->addPrefixPath('Siberian_Form_Decorator_', 'Siberian/Form/Decorator/', 'decorator');
		$this->addFilters(array('StringTrim','StripTags'));
		$this
			->setSeparator('')
			->setDecorators(array(
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
	                'placement' => Zend_Form_Decorator_Abstract::PREPEND,
	            	'disableFor' => true
	            )),
	            array('ControlGroup')
	  		))
	  		->setAttrib('label_class','pull-left radio-label')
  		;

	}

	/**
	 * @return Siberian_Form_Element_Radio
	 */
	public function setNewDesign() {
		return $this
		    ->addClass('sb-form-radio')
		    ->setAttrib('label_class', 'sb-custom-radio')
			->setSeparator("<br />")
			->setAttrib("escape", false)
		    ->setDecorators(array(
    	  		'ViewHelper',
				array(array('container'=>'HtmlTag'),array(
					'class' => 'sb-radio-container col-sm-7'
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
    	  	)
	  	);
	}

	/**
	 * Remove separator
	 */
	public function setIsInline() {
		return $this->setSeparator("");
	}

	/**
	 * @param array $options
	 * @return Zend_Form_Element_Multi
	 */
	public function addMultiOptions(array $options) {
		$new_options = array();
		foreach($options as $value => $label) {
			$new_options[$value] = '<span class="sb-radio-label">'.$label.'</span>';
		}

		return parent::addMultiOptions($new_options);
	}

	/**
	 * @param $new
	 * @return Siberian_Form_Element_Radio
	 */
    public function addClass($new) {
	    return Siberian_Form_Abstract::addClass($new, $this);
	}

}