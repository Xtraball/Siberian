<?php

use Weblink\Form\Link;

/**
 * Class Cms_Form_Block_Button
 */
class Cms_Form_Block_Button extends Cms_Form_Block_Abstract
{

    /**
     * @var string
     */
    public $blockType = 'button';

    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAttrib('id', 'form-cms-block-button-' . $this->uniqid);

        # ICON
        $icon_fake = $this->addSimpleImage('icon_fake', __('Custom icon'), __('Custom icon'), [
            'width' => 128,
            'height' => 128,
        ]);

        $icon = $this->addSimpleHidden('icon');
        $icon->setBelongsTo('block[' . $this->uniqid . '][button]');
        $icon->addClass('cms-button-icon');

        # LABEL
        $label = $this->addSimpleText('label', __('Label'));
        $label->setBelongsTo('block[' . $this->uniqid . '][button]');
        $label->addClass('cms-button-label');

        # PHONE
        $phone = $this->addSimpleText('phone', __('Phone'));
        $phone->setBelongsTo('block[' . $this->uniqid . '][button]');
        $phone->addClass('cms-button-input cms-button-phone');

        # EMAIL
        $email = $this->addSimpleText('email', __('Email'));
        $email->setBelongsTo('block[' . $this->uniqid . '][button]');
        $email->addClass('cms-button-input cms-button-email');

        # LINK
        $link = $this->addSimpleText('link', __('Link'));
        $link->setBelongsTo('block[' . $this->uniqid . '][button]');
        $link->addClass('cms-button-input cms-button-link');

        # LINK OPTIONS
        $belongsTo = "block[".$this->uniqid."][button]";
        $classes = "cms-button-input cms-button-link";
        Link::addLinkOptions($this, $belongsTo, $classes);

        # BUTTON TYPE
        $type = $this->addSimpleHidden('type');
        $type->setBelongsTo('block[' . $this->uniqid . '][button]');
        $type->addClass('cms-button-input cms-input-button-type');

        # VALUE ID
        $value_id = $this->addSimpleHidden('value_id');
        $value_id
            ->setRequired(true);
    }

    /**
     * @param Cms_Model_Application_Block $block
     * @return $this
     */
    public function loadBlock(Cms_Model_Application_Block $block): self
    {
        $typeId = $block->getTypeId();

        // Transforming options before populate
        $_options = $block->getObject()->getOptions();
        $options = [
            'global' => [],
            'android' => [],
            'ios' => [],
        ];
        foreach ($_options['global'] as $key => $value) {
            $options['global'][$key] = $value;
        }
        foreach ($_options['android'] as $key => $value) {
            $options['android']["android_{$key}"] = ($value === 'yes');
        }
        foreach ($_options['ios'] as $key => $value) {
            $options['ios']["ios_{$key}"] = ($value === 'yes');
        }

        $blockData = [
            'block' => [
                $this->uniqid => [
                    'button' => [
                        'external_browser' => $block->getExternalBrowser(),
                        'label' => $block->getLabel(),
                        'type' => $typeId,
                        'icon' => $block->getIcon(),
                        'icon_fake' => $block->getIcon(),
                        $typeId => $block->getContent(),
                        'options' => $options
                    ]
                ]
            ],
            'icon_fake' => $block->getIcon()
        ];

        $this->populate($blockData);

        return $this;
    }

}
