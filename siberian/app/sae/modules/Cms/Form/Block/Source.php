<?php

/**
 * Class Cms_Form_Block_Source
 */
class Cms_Form_Block_Source extends Cms_Form_Block_Abstract
{

    /**
     * @var string
     */
    public $blockType = 'source';

    /**
     * @throws Zend_Form_Exception
     * @throws Zend_Validate_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path('/template/crop/upload'))
            ->setAttrib('id', 'form-cms-block-source-' . $this->uniqid)
        ;

        $source = $this->addSimpleTextarea('source', __('Source code'), false, ['ckeditor' => 'source']);
        $source->setBelongsTo('block[' . $this->uniqid . '][source]');
        $source->setRichtext();
        $source->setRequired(true);

        $height = $this->addSimpleNumber('height', __('Frame height'), 1, 9999, true, 1);
        $height->setDescription(__('From 1 to 9999.'));
        $height->setBelongsTo('block[' . $this->uniqid . '][source]');
        $height->setValue(20);
        $height->setRequired(true);

        $unit = $this->addSimpleSelect('unit', __('Height unit'), [
            'vh' => __('Percentage (%) of the screen height'),
            'px' => __('Fixed number of pixels'),
        ]);
        $unit->setBelongsTo('block[' . $this->uniqid . '][source]');
        $unit->setValue('vh');
        $unit->setRequired(true);

        $value_id = $this->addSimpleHidden('value_id');
        $value_id
            ->setRequired(true);
    }

    /**
     * @param $block
     * @return $this
     */
    public function loadBlock($block)
    {
        $this->getElement('source')->setValue($block->getObject()->getOriginal());
        $this->getElement('height')->setValue($block->getHeight() ?? 20);
        $this->getElement('unit')->setValue($block->getUnit());

        return $this;
    }

}
