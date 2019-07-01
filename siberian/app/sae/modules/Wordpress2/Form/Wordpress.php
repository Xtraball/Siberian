<?php

/**
 * Class Wordpress2_Form_Wordpress
 */
class Wordpress2_Form_Wordpress extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path('/wordpress2/application/editwordpress'))
            ->setAttrib('id', 'form-wordpress2-wordpress');

        self::addClass('create', $this);

        $url = $this->addSimpleText('url', __('WordPress URL') . " " . __("(without /wp-json/)"));
        $url->setRequired(true);

        $showTitle = $this->addSimpleCheckbox('show_title', __('Show title & subtitle'));

        $title = $this->addSimpleText('title', __('Title'));
        $subtitle = $this->addSimpleText('subtitle', __('Subtitle'));

        $groupQueries = $this->addSimpleCheckbox('group_queries', __('Group all queries into a single list'));

        $sortType = $this->addSimpleSelect('sort_type', __('Sort by'), [
            'date' => __('Date'),
            'author' => __('Author'),
            'id' => __('ID'),
            'relevance' => __('Relevance'),
            'title' => __('Title'),
        ]);

        $sortOrder = $this->addSimpleSelect('sort_order', __('Sort order'), [
            'desc' => __('Descending'),
            'asc' => __('Ascending'),
        ]);

        $showCover = $this->addSimpleCheckbox('show_cover', __('Show cover'));
        $picture = $this->addSimpleImage(
            'picture',
            __('Cover'),
            __('Import a cover image'),
            [
                'width' => 960,
                'height' => 600,
                'required' => true
            ]);
        $picture
            ->addClass('default_button')
            ->addClass('form_button');

        $credentialsHelp = '
<div class="alert alert-info">
    ' . __('If your WordPress is secured by a login/password, and/or you want to display private posts you can set here a user to be used to retrieve posts.') . '
</div>
        ';

        $this->addSimpleHtml('credentials_help', $credentialsHelp, [
            'class' => 'col-sm-12'
        ]);
        $login = $this->addSimpleText('login', __('Login'));
        $password = $this->addSimplePassword('password', __('Password'));

        $this->addSimpleHidden('wordpress2_id');
        $valueId = $this->addSimpleHidden('value_id');


        $this->addSubmit(__('Save'))
            ->addClass('default_button')
            ->addClass('pull-right')
            ->addClass('submit_button');
    }
}