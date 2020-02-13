<?php

/**
 * Class Places_Form_Category
 */
class Places_Form_Category extends Siberian_Form_Abstract
{
    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/places/application/edit-category"))
            ->setAttrib("id", "form-edit-category");

        self::addClass('create', $this);

        $this->addNav('nav-categories', __('Save'));

        $this->addSimpleHidden('category_id', __("category_id"));
        $this->addSimpleHidden('value_id', __("value_id"));

        $title = $this->addSimpleText('title', __("Name"));
        $title->setRequired(true);

        $description = $this->addSimpleTextarea('subtitle', __('Description'));
        $description->setAttrib('id', 'subtitle_category_new');
        $description->setRichtext();

        $tagsHintHtml = '
<div class="col-md-7 col-md-offset-3">
    <div class="alert alert-warning text-center">' .
        '<b>512x512px</b> ' . __("PNG icons are recommended for a better display result.") .
        '<br />' .
        '<a href="/app/sae/modules/Places/resources/design/desktop/flat/template/places/application/icon-pack.zip" 
            class="btn color-blue" 
            style="margin-top: 15px;"
            target="_blank">' .
        __("Download icon pack example") . '</a>
    </div>
</div>';

        $tagsHint = $this->addSimpleHtml("super-tags", $tagsHintHtml);

        $this->addSimpleImage('picture', __('Add an image'), __('Add an image'), [
            'width' => 512,
            'height' => 512,
        ]);
    }
}