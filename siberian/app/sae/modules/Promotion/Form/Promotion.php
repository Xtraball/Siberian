<?php
/**
 * Class Promotion_Form_Promotion
 */
class Promotion_Form_Promotion extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $db = Zend_Db_Table::getDefaultAdapter();

        $this
            ->setAction(__path('/promotion/application/editpost'))
            ->setAttrib('id', 'form-promotion')
            ->addNav('promotion-nav')
        ;

        // Bind as a create form!
        self::addClass('create', $this);

        $this->addSimpleHtml('spacer_br', '<br />');

        $this->addSimpleHidden('promotion_id');

        $picture = $this->addSimpleImage(
            'picture',
            __('Picture'),
            __('Import a picture'),
            ['width' => 960, 'height' => 450]
        );

        $banner = $this->addSimpleImage(
            'thumbnail',
            __('Thumbnail'),
            __('Import a thumbnail'),
            ['width' => 256, 'height' => 256]
        );

        $title = $this->addSimpleText('title', __('Title'));
        $title
            ->setRequired(true)
        ;

        $description = $this->addSimpleTextarea('description', __('Description'));
        $description
            ->setRequired(true)
            ->setNewDesignLarge()
            ->setRichtext()
        ;

        $conditions = $this->addSimpleText('conditions', __('Conditions'));
        $conditions
            ->setRequired(true)
        ;

        $use_only_once = $this->addSimpleCheckbox(
            'use_only_once',
            __('Use only once?')
        );

        $end_at = $this->addSimpleDatetimepicker(
            "end_at",
            __("End at"),
            false,
            Siberian_Form_Abstract::DATEPICKER,
            'yy-mm-dd'
        );

        $end_at->addValidator(new Siberian_Form_Validate_DateGreaterThanToday(), true);
        $end_at->setAttrib('id', 'end_at_' . uniqid());

        $unlimited = $this->addSimpleCheckbox(
            'unlimited',
            __('or unlimited?')
        );
        $unlimited->setDescription(__('(You\'ll be able to remove this promotion later)'));

        $unlock_by = $this->addSimpleHidden('unlock_by');
        $unlock_code = $this->addSimpleHidden('unlock_code');

        $value_id = $this->addSimpleHidden('value_id');
        $value_id
            ->setRequired(true)
        ;
    }

    /**
     * @param $promotion_id
     */
    public function setPromotionId($promotion_id) {
        $this
            ->getElement('promotion_id')
            ->setValue($promotion_id)
            ->setRequired(true);
    }

    /**
     * @param $qrCodeUuid
     */
    public function addQrCode ($qrCodeUuid) {
        if ($this->getElement('unlock_by')->getValue() === 'qrcode') {
            $this->getElement('unlock_code')->setValue($qrCodeUuid);
            $qrCodeUrl = __path('/promotion/application/generateqrcode?code=' . $qrCodeUuid);
            $html = '
<div class="col-md-3">' . __('Your QRCode') . '</div>
<div class="col-md-7">
    <img src="' . $qrCodeUrl . '" 
         alt="qrcode_promotion_' . $qrCodeUuid . '" 
         style="margin-top: -10px; margin-left: -10px;" />
</div>
';
            $this->addSimpleHtml('qrcode_placeholder', $html);
        }

    }
}