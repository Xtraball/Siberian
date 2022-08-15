<?php

/**
 * Class Places_Form_Settings
 */
class Places_Form_Settings extends Siberian_Form_Abstract
{
    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path('/places/application/edit-settings'))
            ->setAttrib('id', 'form-edit-settings');

        self::addClass('create', $this);

        $this->addSimpleHidden('value_id');

        $pages = [
            'places' => __('All places'),
            'categories' => __('Categories'),
            'map' => __('Map'),
        ];

        $defaultPage = $this->addSimpleSelect('default_page', __('Default page'), $pages);

        $layout = [
            'place-100' => __('List'),
            'place-50' => __('Two columns'),
            'place-33' => __('Three columns'),
        ];

        $defaultLayout = $this->addSimpleSelect('default_layout', __('Default layout'), $layout);

        $distance = [
            'km' => __('Kilometers'),
            'mi' => __('Miles'),
        ];

        $distanceUnit = $this->addSimpleSelect('distance_unit', __('Distance unit'), $distance);

        $imagePriority = [
            'thumbnail' => __('Thumbnail > Illustration'),
            'image' => __('Illustration > Thumbnail'),
        ];

        $listImagePriority = $this->addSimpleSelect(
            'listImagePriority',
            __('Places image priority in list'),
            $imagePriority);

        $defaultPins = [
            'pin' => __('Pin'),
            'thumbnail' => __('Thumbnail'),
            'image' => __('Illustration'),
            'default' => __('Google default pin'),
        ];

        $defaultPin = $this->addSimpleSelect(
            'defaultPin',
            __('Default pin for new places'),
            $defaultPins);

        $applyText1 = __('Apply default pin to all existing places.');
        $applyButton1 = __('Apply');
        $pinApplyHtml = <<<RAW
<div class="col-md-7 col-md-offset-3">
    <div class="alert alert-warning">
        {$applyText1}
        <a class="btn color-blue apply-default-pin">{$applyButton1}</a>
    </div>
</div>
RAW;

        $pinApply = $this->addSimpleHtml(uniqid('pin_apply_', true), $pinApplyHtml);

        /**
         * Google maps pin action
         * $mapActions = [
         * 'infoWindow' => __('Show info popup (default)'),
         * 'gotoPlace' => __('Open place directly'),
         * ];
         *
         * $mapAction = $this->addSimpleSelect(
         * 'mapAction',
         * __('Action on map pin click'),
         * $mapActions);
         */

        $levels = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20];
        $combined = array_combine($levels, $levels);

        $defaultMapZoom = $this->addSimpleSelect(
            'defaultMapZoom',
            p__('places', 'Default level of zoom on map'),
            $combined);
        $defaultMapZoom->setValue(8);

        $defaultCenterZoom = $this->addSimpleSelect(
            'defaultCenterZoom',
            p__('places', 'Default level of zoom with user location'),
            $combined);
        $defaultCenterZoom->setValue(8);

        // Featured places are disabled for now.

        //$showFeatured = $this->addSimpleCheckbox("show_featured", __("Show featured labels"));
        //$featuredLabel = $this->addSimpleText("featured_label", __("Featured label"));

        //$showNonFeatured = $this->addSimpleCheckbox("show_non_featured", __("Show non-featured labels"));
        //$nonFeaturedLabel = $this->addSimpleText("non_featured_label", __("Non-featured label"));


        $center = $this->addSimpleText('address', p__('places', 'City'));
        $center->setDescription(p__('places', 'Default map center to use as fallback.'));

        $lat = $this->addSimpleText('lat', p__('places', 'Latitude'));
        $lat->setAttrib('readonly', 'readonly');
        $lng = $this->addSimpleText('lng', p__('places', 'Longitude'));
        $lng->setAttrib('readonly', 'readonly');

        $this->addSimpleCheckbox("notesAreEnabled", p__("places", "Allow users to write personal notes on places"));

        $submit = $this->addSubmit(__('Save'), __('Save'));
        $submit->addClass('pull-right');
    }
}
