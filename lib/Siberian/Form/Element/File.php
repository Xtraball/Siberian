<?php
/**
 * Class Siberian_Form_Element_File
 */
class Siberian_Form_Element_File extends Zend_Form_Element_File {

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
		return $this->setDecorators(array(
	  		'ViewHelper',
            array('Description', array(
                'placement' => Zend_Form_Decorator_Abstract::APPEND,
                'class' => 'sb-form-line-complement col-sm-7 col-sm-offset-3 sb-form-description',
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