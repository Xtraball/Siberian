<?php
/**
 * Class Siberian_Form_Element_Hidden
 */
class Siberian_Form_Element_Hidden extends Zend_Form_Element_Hidden {

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
	public function init() {
		$this->addFilters(array('StringTrim','StripTags'));
	}

	/**
	 * @param $title
	 * @return Siberian_Form_Element_Hidden
	 */
	public function setTooltip($title) {
		$decorator = $this->getDecorator('ControlGroup');
		Siberian_Form_Abstract::addClass('sb-tooltip', $decorator, true);
		$decorator->setOption('title', str_replace('"', "'", $title));
		return $this;
	}

	/**
	 * @return Siberian_Form_Element_Hidden
	 */
	public function setNewDesign() {
		return $this->setDecorators(array(
	  		'ViewHelper',
            array('Description', array(
                'placement' => Zend_Form_Decorator_Abstract::APPEND,
                'class' => 'sb-form-line-complement',
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