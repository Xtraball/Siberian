<?php

/**
 * Class Application_Form_GeneralInformationSources
 */
class Application_Form_GeneralInformationSources extends Siberian_Form_Abstract
{
    /**
     * @var string
     */
    public $color = 'color-green';

    /**
     * Application_Form_GeneralInformationSources constructor.
     * @param null $options
     */
    public function __construct($options = null)
    {
        $this->color = $options['color'] ?? 'color-green';

        parent::__construct($options);
    }

    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path('application/customization_publication_infos/save-general-information-sources'))
            ->setAttrib('id', 'form-general-information-sources');

        self::addClass('create', $this);

        $applicationName = $this->addSimpleText('name', __('Application name'));

        $bundleId = $this->addSimpleText('bundle_id', __('Bundle Id'));

        $packageName = $this->addSimpleText('package_name', __('Package Name'));

        $submit = $this->addSubmit(__('Save'));
        $submit->addClass('pull-right');
    }

    /**
     * @return $this
     */
    public function removeBundles (): self
    {
        $this->removeElement('bundle_id');
        $this->removeElement('package_name');

        return $this;
    }
}