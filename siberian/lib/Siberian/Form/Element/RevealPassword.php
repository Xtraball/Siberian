<?php
/**
 * Class Siberian_Form_Element_RevealPassword
 *
 * A password input with an eye button to show / hide what was typed, for credentials people want to double check
 * (SMTP, API keys...). Hidden by default, the saved value is kept in the field so saving the form again does not
 * erase it.
 */
class Siberian_Form_Element_RevealPassword extends Siberian_Form_Element_Password
{
    /**
     * @var string
     */
    public $helper = 'formRevealPassword';

    /**
     * The value must come back in the field, otherwise saving the form again would store an empty password.
     *
     * @var bool
     */
    public $renderPassword = true;

    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        $this->getView()->addHelperPath('Siberian/View/Helper/', 'Siberian_View_Helper');
        parent::init();
    }
}
