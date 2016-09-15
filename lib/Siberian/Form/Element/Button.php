<?php
/**
 * Class Siberian_Form_Element_Button
 */
class Siberian_Form_Element_Button extends Zend_Form_Element_Button {

	/**
	 * @throws Zend_Form_Exception
	 */
	public function init(){
		$this
			->setAttrib('class', 'btn')
		;
		$this->addPrefixPath('Siberian_Form_Decorator_', 'Siberian/Form/Decorator/', 'decorator');
		$this->setDecorators(array(
  			'ViewHelper',
			array('HtmlTag',array(
				'class'=>'form-actions'
			))
  		));
	}

	/**
	 * @return Siberian_Form_Element_Button
	 */
	public function setBackDesign(){
		$this->addClass("color-blue");
		return $this->setDecorators(array(
			'ViewHelper',
			array('HtmlTag',array(
				'class' => 'sb-back-button'
			))
		));
	}

	/**
	 * @return Siberian_Form_Element_Button
	 */
	public function setMiniDeleteDesign(){
		$this->setAttrib("class", "");
		$this->setAttrib("type", "submit");
		$this->setAttrib("escape", false);

		return $this->setDecorators(array(
			'ViewHelper',
			array('HtmlTag',array(
				'class' => 'sb-mini-delete'
			))
		));
	}

	/**
	 * @return Siberian_Form_Element_Button
	 */
	public function setNewDesign(){
		$this->addClass("color-blue");

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
	 * Set required flag
	 *
	 * @param  bool $flag Default value is true
	 * @return Zend_Form_Element
	 */
	public function setRequired($flag = true)
	{
		$this->_required = (bool) $flag;

		if($this->_required) {
			$this->addClass("is-required");
		}

		return $this;
	}

	/**
	 * @param $new
	 * @return Siberian_Form_Element_Button
	 */
    public function addClass($new) {
	    return Siberian_Form_Abstract::addClass($new, $this);
	}

}