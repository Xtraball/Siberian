<?php

/**
 * Class Wordpress2_Form_Settings
 */
class Wordpress2_Form_Settings extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path('/wordpress2/application/editsettings'))
            ->setAttrib('id', 'form-wordpress2-category');

        // Bind as a create form!
        self::addClass('onchange', $this);

        $this->addSimpleCheckbox('card_design', __('Use card design'));

        $stripShortcode = $this->addSimpleCheckbox('strip_shortcode', __('Strip shortcodes'));

        $this->addSimpleHtml('note_cache_refresh',
            '<div class="alert alert-info">' .
            __('Note: cache is automatically refreshed on any user pull to refresh action whatever the lifetime set.') .
            '</div>',
            [
                'class' => 'col-sm-12'
            ]
        );

        $cacheLifetime = $this->addSimpleSelect('cache_lifetime', __('Cache lifetime (excluding pull to refresh)'), [
            '0' => __('No cache'),
            '60' => __('1 minute'),
            '900' => __('15 minutes'),
            '1800' => __('30 minutes'),
            '3600' => __('1 hour'),
            '10800' => __('3 hours'),
            '21600' => __('6 hours'),
            '43200' => __('12 hours'),
            '86400' => __('1 day'),
            'null' => __('Unlimited (until any pull to refresh)'),
        ]);

        $this->addSimpleHtml('note_no_cache',
            '<div class="alert alert-danger">' .
            __('Disabling cache can have significant performance impacts.') .
            '</div>',
            [
                'class' => 'col-sm-12',
                'style' => 'display: none;'
            ]
        );

        $this->addSimpleHidden('value_id');
    }
}