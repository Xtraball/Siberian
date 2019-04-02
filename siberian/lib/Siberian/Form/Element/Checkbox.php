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
    public function setIsFormHorizontal($boolean)
    {
        $this->is_form_horizontal = $boolean;
    }

    /**
     * @param $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @param $label
     * @param $input
     * @param $offset
     * @param null $error
     * @return $this
     */
    public function setCols($label, $input, $offset, $error = null)
    {
        $this->label_cols = $label;
        $this->input_cols = $input;
        $this->offset_cols = $offset;
        $this->error_cols = ($error != null) ? $error : $input;

        return $this;
    }

	/**
	 * @throws Zend_Form_Exception
	 */
	public function init()
    {
		$this->addPrefixPath('Siberian_Form_Decorator_', 'Siberian/Form/Decorator/', 'decorator');
		$this->addFilters(['StringTrim', 'StripTags']);
		$this->setDecorators([
	  		'ViewHelper',
            [
				'Description',
				[
					'placement' => Zend_Form_Decorator_Abstract::APPEND,
					'class' => 'checkbox-label',
					'tag' => 'span',
					'escape' => false
                ]
            ],
			[
				['html_label' => 'HtmlTag'],
				[
					'tag' => 'label',
					'class' => 'checkbox',
                    'escape' => false,
					'for' => $this->getName()
                ],
            ],
           	[
				'Errors',
				[
           			'placement'=>Zend_Form_Decorator_Abstract::PREPEND,
           			'class'=>'alert alert-error form-error',
                ]
            ],
            [
				['controls' => 'HtmlTag'],
				[
                	'tag' => 'div',
                ]
            ],
            ['ControlGroup'],
        ]);
	  	
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

		return $this->setDecorators([
	  		'ViewHelper',
			[['style' => 'HtmlTag'], [
				'placement' => Zend_Form_Decorator_Abstract::APPEND,
				'tag'   => 'div',
				'class' => $this->color,
            ]],
			[['wrapper' => 'HtmlTag'], [
				'class' => ' '.$element_class
            ]],
            ['Description', [
                'placement' => Zend_Form_Decorator_Abstract::APPEND,
                'class' => 'sb-form-line-complement sb-form-description '.$error_class,
                'escape' => false
            ]],
			['Label', [
				'class' => 'sb-form-line-title '.$label_class,
				'requiredSuffix' => ' *',
                'escape' => false,
				'placement' => Zend_Form_Decorator_Abstract::PREPEND,
            ]],
			['Errors', [
           		'placement'	=> Zend_Form_Decorator_Abstract::PREPEND,
           		'class'		=> 'alert alert-error'
            ]],
            ['ControlGroup', [
            	'class' => 'form-group sb-form-line form-group-checkbox'
            ]]
        ]);
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
		$this->setDecorators([
			'ViewHelper',
			['Errors', [
				'placement'=>Zend_Form_Decorator_Abstract::PREPEND,
				'class'=>'alert alert-error form-error']
            ],
			['Description', [
				'placement' => Zend_Form_Decorator_Abstract::APPEND,
				'class' => 'help-inline'
            ]],
			[['controls' => 'HtmlTag'], [
				'tag'   => 'div',
				'class' => 'controls',
            ]],
			['Label', [
				'class' => 'control-label control',
				'requiredSuffix' => ' *',
                'escape' => false,
				'placement' => Zend_Form_Decorator_Abstract::PREPEND
            ]],
			['ControlGroup']
        ]);
	}

}