<?php
/**
 * Class Siberian_Form_Element_Submit
 */
class Siberian_Form_Element_Submit extends Zend_Form_Element_Submit {

	/**
	 * @throws Zend_Form_Exception
	 */
	public function init(){
		$this
			->setAttrib('class', 'btn')
			->setAttrib('data-loading-text', __("Patientez ..."))
		;
		$this->setDecorators(array(
  			'ViewHelper',
			array('HtmlTag',array(
				'class'=>'form-actions'
			))
  		));
	}

	/**
	 * @return Siberian_Form_Element_Submit
	 */
	public function setNewDesign(){
		$this->addClass("color-blue");
		return $this->setDecorators(array(
  			'ViewHelper',
			array('HtmlTag',array(
				'class' => 'sb-save-info-button'
			))
  		));
	}

	/**
	 * @param $new
	 * @return Siberian_Form_Element_Submit
	 */
    public function addClass($new) {
	    return Siberian_Form_Abstract::addClass($new, $this);
	}

}