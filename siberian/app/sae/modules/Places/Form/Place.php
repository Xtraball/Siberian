<?php

/**
 * Class Places_Form_Place
 */
class Places_Form_Place extends Cms_Form_Cms
{
    /**
     * @var string
     */
    public $feature_code = 'places';

    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        $this->addNav('nav-places', __('Save'));

        $cms_type = $this->addSimpleHidden('cms_type');
        $cms_type->setValue('places');
        $cms_type->addClass('cms-include');

        $title = $this->addSimpleText('title', __('Title'));
        $title->setRequired(true);
        $title->addClass('cms-include');

        $subtitle = $this->addSimpleText('content', __('Subtitle'));
        $subtitle->addClass('cms-include');

        $this->addSimpleImage('places_file', __('Add an image'), __('Add an image'), [
            'width' => 700,
            'height' => 440,
            'cms-include' => true,
        ]);

        $this->addSimpleImage('places_thumbnail', __('Add a thumbnail'), __('Add a thumbnail'), [
            'width' => 128,
            'height' => 128,
            'cms-include' => true,
        ]);

        $show_image = $this->addSimpleCheckbox('show_image', __('Display image in page'));
        $show_image->setBelongsTo('metadata');
        $show_image->addClass('cms-include');

        $show_titles = $this->addSimpleCheckbox('show_titles', __('Display title and subtitle in page'));
        $show_titles->setBelongsTo('metadata');
        $show_titles->addClass('cms-include');

        $show_picto = $this->addSimpleCheckbox('show_picto', __('Display pictogram instead of image in map'));
        $show_picto->setBelongsTo('metadata');
        $show_picto->addClass('cms-include');

        $isFeatured = $this->addSimpleCheckbox('is_featured', __('Feature this place?'));
        $isFeatured->addClass('cms-include');


        $tags = $this->addSimpleText('tags', __('Tags'));
        $tags->addClass('cms-include');
        $tags->setAttrib('data-role', 'tagsinput');

        $tagsHintHtml = '
<div class="col-md-7 col-md-offset-3">
    <div class="alert alert-info">' . __("Tags are used to improve full-text search.") . '</div>
</div>';

        $tagsHint = $this->addSimpleHtml("super-tags", $tagsHintHtml);


        $categories = $this->addSimpleMultiSelect('place_categories', __("Categories"), []);
        $categories->addClass('cms-include');

        parent::init();
    }

    /**
     * @param $valueId
     * @throws Zend_Exception
     */
    public function fillCategories ($valueId)
    {
        // Categories
        $categories = (new Places_Model_Category())
            ->findAll(['value_id' => $valueId]);

        $categoryOptions = [];
        foreach($categories as $_category) {
            $categoryOptions[$_category->getId()] = $_category->getTitle();
        }

        $this->getElement('place_categories')->setMultiOptions($categoryOptions);
    }

    /**
     * @param Places_Model_Place $page
     * @return Zend_Form
     */
    public function fill($page)
    {
        $values = $page->getData();

        // Categories
        $selectedCategories = (new Places_Model_PageCategory())
            ->findAll(['page_id' => $page->getId()]);

        $catValues = [];
        foreach($selectedCategories as $_selectedCategory) {
            $catValues[] = $_selectedCategory->getCategoryId();
        }

        $this->getElement('places_file')->setValue($values['picture']);
        $this->getElement('places_thumbnail')->setValue($values['thumbnail']);
        $this->getElement('show_image')->setValue($page->getMetadata('show_image')->getPayload());
        $this->getElement('show_titles')->setValue($page->getMetadata('show_titles')->getPayload());
        $this->getElement('show_picto')->setValue($page->getMetadata('show_picto')->getPayload());
        $this->getElement('tags')->setValue($page->getData("tags"));
        $this->getElement('place_categories')->setValue($catValues);

        return parent::populate($values);
    }
}