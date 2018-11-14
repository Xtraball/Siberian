<?php

/**
 * Class Application_Form_GeneralInformation
 */
class Application_Form_GeneralInformation extends Siberian_Form_Abstract
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
            ->setAction(__path('application/customization_publication_infos/save-general-information'))
            ->setAttrib('id', 'form-general-information');

        self::addClass('create', $this);

        $applicationName = $this->addSimpleText('name', __('Application name'));

        $description = $this->addSimpleTextarea('description', __('Description'));
        $description->addValidator(new Zend_Validate_StringLength(['min' => 200]));

        $keywords = $this->addSimpleText('keywords', __('Keywords'));

        $categories = Application_Model_Device_Ionic_Ios::getStoreCategeories();
        $categoriesOption = [];

        foreach ($categories as $category) {
            $categoriesOption[$category->getId()] = __($category->getName());
        }

        $mainCategory = $this->addSimpleSelect(
            'main_category_id',
            __('Main category'),
            $categoriesOption);
        $mainCategory->setRequired(true);

        $secondaryCategory = $this->addSimpleSelect(
            'secondary_category_id',
            __('Secondary category (optional)'),
            $categoriesOption);

        $submit = $this->addSubmit(__('Save'));
        $submit->addClass('pull-right');
    }
}