<?php
/**
 * Class Siberian_Form_Element_Textarea
 */
class Siberian_Form_Element_Textarea extends Zend_Form_Element_Textarea {

	/**
	 * @throws Zend_Form_Exception
	 */
	public function init(){
		$this->addPrefixPath('Siberian_Form_Decorator_', 'Siberian/Form/Decorator/', 'decorator');
		$this->addFilters(array('StringTrim','StripTags'));
		$this
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
	                'placement' => Zend_Form_Decorator_Abstract::PREPEND
	            )),
	            array('ControlGroup')
		  	))
		  	->setAttrib('COLS', '')
		  	->setAttrib('ROWS', '5')
		  	->setAttrib('class', 'input-xlarge')
		  	;
	}

	/**
	 * @return Siberian_Form_Element_Textarea
	 * @throws Zend_Form_Exception
	 */
	public function setNewDesign(){
		$this->setAttrib('class', '');
		$this->addClass("input-flat");

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
            	'class' => 'form-group sb-form-line'
            ))
	  	));
	  	
	}

	/**
	 * @return Siberian_Form_Element_Textarea
	 * @throws Zend_Form_Exception
	 */
	public function setNewDesignLarge(){
		$this->setAttrib('class', '');
		$this->addClass("input-flat");

		return $this->setDecorators(array(
			'ViewHelper',
			array(array('wrapper'=>'HtmlTag'),array(
				'class' => 'col-sm-12'
			)),
			array('Description', array(
				'placement' => Zend_Form_Decorator_Abstract::APPEND,
				'class' => 'sb-form-line-complement'
			)),
			array('Label', array(
				'class' => 'sb-form-line-title col-sm-12',
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
	 * @return Siberian_Form_Element_Textarea
	 */
	public function setDescription($description){
		$this->addClass('sb-form-float');
		return parent::setDescription($description);
	}

	/**
	 * @return $this
	 */
	public function setRichtext() {
		$this->setNewDesignLarge();
		$this->addClass("richtext");
		$this->removeFilter("StripTags");

		return $this;
	}

	/**
	 * @param $new
	 * @return Siberian_Form_Element_Textarea
	 */
    public function addClass($new) {
	    return Siberian_Form_Abstract::addClass($new, $this);
	}

}