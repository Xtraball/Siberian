<?php

/**
 * Class Application_Form_Advanced
 */
class Application_Form_Advanced extends Siberian_Form_Abstract
{
    /**
     * @throws Zend_Exception
     * @throws Zend_Form_Exception
     * @throws Zend_Validate_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path('/application/settings_advanced/save'))
            ->setAttrib('id', 'form-application-advanced');

        // Bind as a onchange form!
        self::addClass('onchange', $this);

        $this->addSimpleNumber('fidelity_rate',
            p__('application', 'Fidelity points rate'), 0, 10000, true, 0.01);

        $this->groupElements('fidelity', ['fidelity_rate'],
            p__('application', 'Fidelity points'));
    }
}
