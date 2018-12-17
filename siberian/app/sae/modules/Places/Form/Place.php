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
        $this->addNav('nav-places', __("Save"));

        $cms_type = $this->addSimpleHidden('cms_type');
        $cms_type->setValue('places');
        $cms_type->addClass('cms-include');

        $title = $this->addSimpleText('title', __("Title"));
        $title->setRequired(true);
        $title->addClass('cms-include');

        $show_titles = $this->addSimpleCheckbox('show_titles', __("Display title in page"));
        $show_titles->setBelongsTo('metadata');
        $show_titles->addClass('cms-include');

        $subtitle = $this->addSimpleText('content', __("Subtitle"));
        $subtitle->addClass('cms-include');

        $show_subtitle = $this->addSimpleCheckbox('show_subtitle', __("Display subtitle in page"));
        $show_subtitle->setBelongsTo('metadata');
        $show_subtitle->addClass('cms-include');

        $this->addSimpleImage('places_file', __("Add an illustration"), __("Add an illustration"), [
            'width' => 700,
            'height' => 440,
            'cms-include' => true,
        ]);

        $show_image = $this->addSimpleCheckbox('show_image', __("Display illustration in page"));
        $show_image->setBelongsTo('metadata');
        $show_image->addClass('cms-include');

        $this->addSimpleImage('places_thumbnail', __('Add a thumbnail'), __("Add a thumbnail"), [
            'width' => 256,
            'height' => 256,
            'cms-include' => true,
        ]);

        $this->addSimpleImage('places_pin', __('Add a pin'), __("Add a pin"), [
            'width' => 128,
            'height' => 128,
            'cms-include' => true,
        ]);

        //if not available will fallback to defaults Google Maps pin

        $mapIcon = $this->addSimpleSelect(
            'map_icon',
            __('Map icon'),
            [
                "pin" => __("Pin"),
                "thumbnail" => __("Thumbnail"),
                "image" => __("Illustration"),
                "default" => __("Google default pin"),
            ]
        );
        $mapIcon->addClass('cms-include');

        $pinsHintHtml = '
<div class="col-md-7 col-md-offset-3">
    <div class="alert alert-info">' . __("If the map icon is not uploaded, it will fallback to default Google Maps pin.") . '</div>
</div>';

        $pinsHint = $this->addSimpleHtml("super-pins", $pinsHintHtml);



        // Featured places are disabled for now.
        //$isFeatured = $this->addSimpleCheckbox('is_featured', __('Feature this place?'));
        //$isFeatured->addClass('cms-include');


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
        $this->getElement('places_pin')->setValue($values['pin']);
        $this->getElement('show_image')->setValue($page->getMetadata('show_image')->getPayload());
        $this->getElement('show_titles')->setValue($page->getMetadata('show_titles')->getPayload());
        $this->getElement('show_subtitle')->setValue($page->getMetadata('show_subtitle')->getPayload());
        $this->getElement('map_icon')->setValue($values['map_icon']);
        $this->getElement('tags')->setValue($page->getData("tags"));
        $this->getElement('place_categories')->setValue($catValues);

        return parent::populate($values);
    }
}