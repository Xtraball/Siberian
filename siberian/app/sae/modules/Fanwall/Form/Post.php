<?php

namespace Fanwall\Form;

use Siberian_Form_Abstract as FormAbstract;
/**
 * Class Post
 * @package Fanwall\Form
 */
class Post extends FormAbstract
{
    /**
     * @var Siberian_Form_Element_Text
     */
    public $dateField = null;

    /**
     * @var string\null
     */
    public $uniqid = null;

    /**
     * @var string
     */
    public static $imageTemplate = '
<div class="fanwall-image" 
     style="background-image: url(#THUMBNAIL_PATH#);">
    <div class="fanwall-image-handle">
        <i class="fa fa-arrows"></i>
    </div>
    <div class="fanwall-image-delete">
        <i class="fa fa-times"></i>
    </div>
    <div class="fanwall-image-bg"></div>
    <img src="/images/application/placeholder/blank-512.png" 
         class="fanwall-image-unit" />
    <input type="hidden"
           name="images[%UNIQID%][]" 
           value="%IMAGE_PATH%" />
</div>';

    /**
     * @throws \Zend_Exception
     * @throws \Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this->uniqid = uniqid('fw_', false);

        $this
            ->setAction(__path('/fanwall/application/edit-post'))
            ->setAttrib('id', 'form-fanwall-post')
            ->addNav('nav-fanwall-post', p__('fanwall', 'Save'), true, true);

        /** Bind as a create form */
        self::addClass('create', $this);

        $this->addSimpleHidden('post_id');

        $title = $this->addSimpleText('title', p__('fanwall', 'Title'));
        $title->setAttrib('maxlength', 100);

        $this->dateField = $this->addSimpleDatetimepickerv2(
            'date_' . uniqid('sw_', true),
            p__('fanwall','Publication date'),
            false,
            self::DATETIMEPICKER);

        $isScheduled = $this->addSimpleCheckbox('is_scheduled', p__('fanwall', 'Schedule post?'));
        $isScheduled->setDescription(p__('fanwall', "A scheduled post will not be visible before it's publication date."));

        $text = $this->addSimpleTextarea(
            'text',
            p__('fanwall','Post'),
            false,
            ['ckeditor' => 'social_wall']);
        $text
            ->setRichtext();

        // Multiple image upload
        $this->addSimpleHidden('image');

        $picturesUploader = $this->addSimpleFile('image_uploader', p__('fanwall', 'Add pictures'), ['multiple' => true]);
        $picturesUploader->setBelongsTo("images[".$this->uniqid."]");
        $magesContainer = '
<div class="fanwall-images-container"></div>';

        $this->addSimpleHtml('fanwall-images-container', $magesContainer);

        $valueId = $this->addSimpleHidden('value_id');
        $valueId
            ->setRequired(true);

        // Defaults date to NOW() for new Pots
        $this->setDate(time());
    }

    /**
     * @return string\null
     */
    public function getUniqid()
    {
        return $this->uniqid;
    }

    /**
     * @param $postId
     */
    public function setPostId($postId)
    {
        $this
            ->getElement('post_id')
            ->setValue($postId)
            ->setRequired(true);
    }

    /**
     * @param $timestampInSeconds
     */
    public function setDate($timestampInSeconds)
    {
        $this->dateField->setValue($timestampInSeconds * 1000);
    }

    /**
     * @throws \Zend_Exception
     * @throws \Zend_Form_Exception
     */
    public function loadFormSubmit()
    {
        $submit = $this->addSubmit(p__('fanwall', 'Save'), p__('fanwall', 'Save'));
        $submit->addClass('pull-right');
    }

    /**
     * @param $images
     * @return $this
     * @throws \Zend_Form_Exception
     */
    public function loadImages() {

        $imagesHtml = [];
        $images = explode(',', $this->getElement('image')->getValue());
        foreach($images as $image) {
            $tmp = self::$imageTemplate;
            $tmp = str_replace([
                "%UNIQID%",
                "%IMAGE_PATH%",
                "#THUMBNAIL_PATH#",
            ], [
                $this->getUniqid(),
                $image,
                '/images/application' . $image,
            ], $tmp);

            $imagesHtml[] = $tmp;
        }

        $imagesContainer = '
<div class="fanwall-images-container">'.implode_polyfill('', $imagesHtml).'</div>';

        $this->addSimpleHtml('fanwall-images-container', $imagesContainer);

        $formId = $this->getAttrib('id');
        $bindForm = <<<RAW
<script type="text/javascript">
$(document).ready(function () {
    window.bindUploader('#{$formId}');
});
</script>
RAW;

        $this->addMarkup($bindForm);

        return $this;
    }
}
