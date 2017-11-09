<?php
/**
 * Class Siberian_Form_Element_Checkbox
 */
class Siberian_Form_Element_Checkbox extends Zend_Form_Element_Checkbox {

    /**
     * @var bool
     */
    public $is_form_horizontal = true;

    /**
     * @var string
     */
    public $color = "color-blue";

    /**
     * @var string
     */
    protected $label_cols = "col-md-3";

    /**
     * @var string
     */
    protected $input_cols = "col-md-7";

    /**
     * @var string
     */
    protected $offset_cols = "col-md-offset-3";

    /**
     * @var string
     */
    protected $error_cols = "col-md-7";

    /**
     * @param $boolean
     */
    public function setIsFormHorizontal($boolean) {
        $this->is_form_horizontal = $boolean;
    }

    /**
     * @param $color
     */
    public function setColor($color) {
        $this->color = $color;
    }

    /**
     * @param $label
     * @param $input
     * @param $offset
     * @param null $error
     * @return $this
     */
    public function setCols($label, $input, $offset, $error = null) {
        $this->label_cols = $label;
        $this->input_cols = $input;
        $this->offset_cols = $offset;
        $this->error_cols = ($error != null) ? $error : $input;

        return $this;
    }

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
		$this->addClass($this->color);

        if($this->is_form_horizontal) {
            $label_class = "{$this->label_cols}";
            $element_class = "{$this->input_cols}";
            $error_class = "{$this->error_cols} {$this->offset_cols}";
        } else {
            $label_class = "";
            $element_class = "";
            $error_class = "";
        }

		return $this->setDecorators(array(
	  		'ViewHelper',
			array(array('style' => 'HtmlTag'), array(
				'placement' => Zend_Form_Decorator_Abstract::APPEND,
				'tag'   => 'div',
				'class' => $this->color,
			)),
			array(array('wrapper' => 'HtmlTag'),array(
				'class' => ' '.$element_class
			)),
            array('Description', array(
                'placement' => Zend_Form_Decorator_Abstract::APPEND,
                'class' => 'sb-form-line-complement sb-form-description '.$error_class,
                'escape' => false
            )),
			array('Label', array(
				'class' => 'sb-form-line-title '.$label_class,
				'requiredSuffix' => ' *',
				'placement' => Zend_Form_Decorator_Abstract::PREPEND,
			)),
			array('Errors',array(
           		'placement'	=> Zend_Form_Decorator_Abstract::PREPEND,
           		'class'		=> 'alert alert-error'
          	)),
            array('ControlGroup',array(
            	'class' => 'form-group sb-form-line form-group-checkbox'
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
			array(array('controls' => 'HtmlTag'), array(
				'tag'   => 'div',
				'class' => 'controls',
			)),
			array('Label', array(
				'class' => 'control-label control',
				'requiredSuffix' => ' *',
				'placement' => Zend_Form_Decorator_Abstract::PREPEND
			)),
			array('ControlGroup')
		));
	}

}