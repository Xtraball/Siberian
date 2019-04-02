<?php
/**
 * Class Siberian_Form_Element_Radio
 */
class Siberian_Form_Element_Radio extends Zend_Form_Element_Radio {

    /**
     * @var bool
     */
    public $is_form_horizontal = true;

    /**
     * @param $boolean
     */
    public function setIsFormHorizontal($boolean)
    {
        $this->is_form_horizontal = $boolean;
    }

    /**
     * @var string
     */
    public $color = "color-blue";

    /**
     * @param $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

	/**
	 * @throws Zend_Form_Exception
	 */
	public function init()
    {
		$this->setDisableLoadDefaultDecorators(true);
		$this->addPrefixPath('Siberian_Form_Decorator_', 'Siberian/Form/Decorator/', 'decorator');
		$this->addFilters(['StringTrim','StripTags']);
		$this
			->setSeparator('')
			->setDecorators([
		  		'ViewHelper',
	           	[
	           	    'Errors',
                    [
	           		    'placement'=>Zend_Form_Decorator_Abstract::PREPEND,
	           		    'class'=>'alert alert-error form-error'
                    ]
                ],
	            [
	                'Description',
                    [
                        'placement' => Zend_Form_Decorator_Abstract::APPEND,
                        'class' => 'help-block',
                        'escape' => false,
                    ]
                ],
	            [
	                [
	                    'controls' => 'HtmlTag'
                    ],
                    [
                        'tag'   => 'div',
                        'class' => 'controls',
                    ]
                ],
	            [
	                'Label',
                    [
                        'class' => 'control-label',
                        'requiredSuffix' => ' *',
                        'escape' => false,
                        'placement' => Zend_Form_Decorator_Abstract::PREPEND,
                        'disableFor' => true
                    ]
                ],
	            [
	                'ControlGroup'
                ]
            ])
	  		->setAttrib('label_class','pull-left radio-label');
	}

    /**
     * @return mixed
     * @throws Zend_Form_Exception
     */
	public function setNewDesign() {

        if($this->is_form_horizontal) {
            $label_class = "col-sm-3";
            $element_class = "col-sm-7";
        } else {
            $label_class = "";
            $element_class = "";
            $error_class = "";
        }

		return $this
		    ->addClass('sb-form-radio ' . $this->color)
		    ->setAttrib('label_class', 'sb-custom-radio radio-inline')
			->setSeparator("<br />")
			->setAttrib("escape", false)
		    ->setDecorators([
    	  		'ViewHelper',
				[['container'=>'HtmlTag'], [
					'class' => 'sb-radio-container '.$element_class
                ]],
                ['Label', [
                    'class' => 'sb-form-line-title '.$label_class,
                    'requiredSuffix' => ' *',
                    'escape' => false,
                    'placement' => Zend_Form_Decorator_Abstract::PREPEND,
                ]],
               	['Errors', [
               		'placement'=>Zend_Form_Decorator_Abstract::PREPEND,
               		'class'=>'alert alert-error'
                ]],
                [['cb' => 'HtmlTag'], [
                	'class' => 'sb-cb',
                	'placement' => Zend_Form_Decorator_Abstract::APPEND,
                ]],
                ['ControlGroup', [
                	'class' => 'form-group sb-form-line'
                ]]
                ]
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
		$new_options = [];
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