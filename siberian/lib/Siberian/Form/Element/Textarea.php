<?php

/**
 * Class Siberian_Form_Element_Textarea
 */
class Siberian_Form_Element_Textarea extends Zend_Form_Element_Textarea
{

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
        $this->addPrefixPath('Siberian_Form_Decorator_', 'Siberian/Form/Decorator/', 'decorator');
        $this->addFilters(['StringTrim', 'StripTags']);
        $this
            ->setDecorators([
                'ViewHelper',
                ['Errors', [
                    'placement' => Zend_Form_Decorator_Abstract::PREPEND,
                    'class' => 'alert alert-error form-error']
                ],
                ['Description', [
                    'placement' => Zend_Form_Decorator_Abstract::APPEND,
                    'class' => 'help-block',
                    'escape' => false,
                ]],
                [['controls' => 'HtmlTag'], [
                    'tag' => 'div',
                    'class' => 'controls',
                ]],
                ['Label', [
                    'class' => 'control-label',
                    'requiredSuffix' => ' *',
                    'escape' => false,
                    'placement' => Zend_Form_Decorator_Abstract::PREPEND
                ]],
                ['ControlGroup']
            ])
            ->setAttrib('COLS', '')
            ->setAttrib('ROWS', '5')
            ->setAttrib('class', 'input-xlarge');
    }

    /**
     * @return Siberian_Form_Element_Textarea
     * @throws Zend_Form_Exception
     */
    public function setNewDesign()
    {
        $this->setAttrib('class', '');
        $this->addClass("input-flat");

        if ($this->is_form_horizontal) {
            $label_class = "col-sm-3";
            $element_class = "col-sm-7";
        } else {
            $label_class = "";
            $element_class = "";
            $error_class = "";
        }

        return $this->setDecorators([
            'ViewHelper',
            [['wrapper' => 'HtmlTag'], [
                'class' => ' ' . $element_class
            ]],
            ['Description', [
                'placement' => Zend_Form_Decorator_Abstract::APPEND,
                'class' => 'sb-form-line-complement',
                'escape' => false,
            ]],
            ['Label', [
                'class' => 'sb-form-line-title ' . $label_class,
                'requiredSuffix' => ' *',
                'escape' => false,
                'placement' => Zend_Form_Decorator_Abstract::PREPEND,
            ]],
            ['Errors', [
                'placement' => Zend_Form_Decorator_Abstract::PREPEND,
                'class' => 'alert alert-error'
            ]],
            [['cb' => 'HtmlTag'], [
                'class' => 'sb-cb',
                'placement' => Zend_Form_Decorator_Abstract::APPEND,
            ]],
            ['ControlGroup', [
                'class' => 'form-group sb-form-line'
            ]]
        ]);

    }

    /**
     * @return Siberian_Form_Element_Textarea
     * @throws Zend_Form_Exception
     */
    public function setNewDesignLarge()
    {
        $this->setAttrib('class', '');
        $this->addClass("input-flat");

        return $this->setDecorators([
            'ViewHelper',
            [['wrapper' => 'HtmlTag'], [
                'class' => 'col-sm-12'
            ]],
            ['Description', [
                'placement' => Zend_Form_Decorator_Abstract::APPEND,
                'class' => 'sb-form-line-complement',
                'escape' => false,
            ]],
            ['Label', [
                'class' => 'sb-form-line-title col-sm-12',
                'requiredSuffix' => ' *',
                'escape' => false,
                'placement' => Zend_Form_Decorator_Abstract::PREPEND,
            ]],
            ['Errors', [
                'placement' => Zend_Form_Decorator_Abstract::PREPEND,
                'class' => 'alert alert-error'
            ]],
            [['cb' => 'HtmlTag'], [
                'class' => 'sb-cb',
                'placement' => Zend_Form_Decorator_Abstract::APPEND,
            ]],
            ['ControlGroup', [
                'class' => 'form-group sb-form-line'
            ]]
        ]);

    }

    /**
     * @param string $description
     * @return Siberian_Form_Element_Textarea
     */
    public function setDescription($description)
    {
        $this->addClass('sb-form-float');
        return parent::setDescription($description);
    }

    /**
     * @return $this
     * @throws Zend_Form_Exception
     */
    public function setRichtext($large = true)
    {
        if ($large) {
            $this->setNewDesignLarge();
        } else {
            $this->setNewDesign();
        }

        $this->addClass('richtext');
        $this->removeFilter('StripTags');

        return $this;
    }

    /**
     * @param $new
     * @return Siberian_Form_Element_Textarea
     */
    public function addClass($new)
    {
        return Siberian_Form_Abstract::addClass($new, $this);
    }

}