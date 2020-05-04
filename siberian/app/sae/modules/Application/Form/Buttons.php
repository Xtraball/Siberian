<?php

/**
 * Class Application_Form_Buttons
 */
class Application_Form_Buttons extends Siberian_Form_Abstract
{
    /**
     * @var string
     */
    public $color = 'color-green';

    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("application/customization_publication_app/save-buttons"))
            ->setAttrib("id", "form-general-information-back-button");

        $this->setIsFormHorizontal(false);

        $backButtonClass = $this->addSimpleText('back_button_class', p__('application', 'Back button custom class'));
        $leftToggleClass = $this->addSimpleText('left_toggle_class', p__('application', 'Left toggle menu icon custom class'));
        $rightToggleClass = $this->addSimpleText('right_toggle_class', p__('application', 'Right toggle menu icon custom class'));

        self::addClass("onchange", $this);
    }
}