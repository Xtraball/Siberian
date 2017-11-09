<?php
/**
 * Class Siberian_Form_Element_Html
 */
class Siberian_Form_Element_Html extends Zend_Form_Element_Xhtml {

    public $helper = "formHtml";

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
     * @param $color
     */
    public function setColor($color) {
        $this->color = $color;
    }

	/**
	 * @throws Zend_Form_Exception
	 */
	public function init(){
        $this->getView()->addHelperPath('Siberian/View/Helper/', 'Siberian_View_Helper');
		$this->addPrefixPath('Siberian_Form_Decorator_', 'Siberian/Form/Decorator/', 'decorator');
		$this->setDecorators(array(
	  		'ViewHelper',
            array(array('controls' => 'HtmlTag'), array(
                'tag'   => 'div',
                'class' => 'controls',
            )),
            array('ControlGroup')
	  	));
	}
	
	/**
	 * @return Siberian_Form_Element_Text
	 */
	public function setNewDesign($class = ""){
		$this->addClass('sb-form-html');

		return $this->setDecorators(array(
	  		'ViewHelper',
			array(array('wrapper'=>'HtmlTag'),array(
				'class' => ""
			)),
            array('ControlGroup',array(
            	'class' => 'form-group sb-form-line '.$class
            ))
	  	));
	  	
	}

	/**
	 * @param $new
	 * @return Siberian_Form_Element_Text
	 */
	public function addClass($new) {
	    return Siberian_Form_Abstract::addClass($new, $this);
	}
}