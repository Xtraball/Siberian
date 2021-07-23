<?php
/**
 * @author Xtraball SAS
 * @version 4.18.5
 */

namespace Fanwall\Form;

use Siberian_Form_Abstract as FormAbstract;

/**
 * Class Settings
 * @package Fanwall\Form
 */
class Settings extends FormAbstract
{
    /**
     * @throws \Zend_Form_Exception
     * @throws \Zend_Validate_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path('/fanwall/application/edit-settings'))
            ->setAttrib('id', 'form-fanwall-settings');
        /** Bind as a create form */
        self::addClass('create', $this);

        $this->addSimpleHidden('fanwall_id');

        $this->addSimpleText(
            'admin_emails',
            p__('fanwall', 'Admin e-mails (reports & moderation)'));

        $radius = $this->addSimpleText('radius', p__('fanwall', 'Near me radius (in km)'));
        $radius
            ->setRequired(true);

        $this->addSimpleSelect('design', p__('fanwall', 'Design'), [
            'list' => p__('fanwall', 'List'),
            'card' => p__('fanwall', 'Card'),
        ]);

        $maxTextBodyLength = $this->addSimpleNumber('max_body_size',
            p__('fanwall', 'Post message preview length'), 0, null, true, 1);
        $maxTextBodyLength->setDescription(p__('fanwall', 'Cut long post messages after N characters, set to 0 for full length posts.'));

        $this->addSimpleCheckbox('enable_nearby', p__('fanwall','Enable nearby'));
        $this->addSimpleCheckbox('enable_map', p__('fanwall','Enable map'));
        $this->addSimpleCheckbox('enable_gallery', p__('fanwall','Enable gallery'));
        $this->addSimpleCheckbox('enable_user_like', p__('fanwall','Enable user likes'));
        $this->addSimpleCheckbox('enable_user_post', p__('fanwall','Enable user posts'));
        $this->addSimpleCheckbox('enable_user_comment', p__('fanwall','Enable user comments'));

        $this->addSimpleSelect('enable_user_share', p__('fanwall', 'Post sharing options'), [
            'none' => p__('fanwall', 'Disabled'),
            'big' => p__('fanwall', 'Below post body'),
            'small' => p__('fanwall', 'Inside contextual menu'),
            'both' => p__('fanwall', 'Inside contextual menu & below post body'),
        ]);

        $postModeration = $this->addSimpleCheckbox('enable_moderation', p__('fanwall','Enable post moderation'));
        $postModeration->setDescription(p__('fanwall', 'When enabled, user posts from the application must be approved by an admin to be published.'));

        $this->groupElements('group_features', [
            'enable_nearby',
            'enable_map',
            'enable_gallery',
            'enable_user_like',
            'enable_user_post',
            'enable_user_comment',
        ], p__('fanwall', 'Features'));

        $this->addSimpleSelect('photo_position', p__('fanwall','Position'), [
            'after' => p__('fanwall', 'After text') . ' ' . p__('fanwall', '(default)'),
            'before' => p__('fanwall', 'Before text'),
        ]);

        $this->addSimpleSelect('photo_mode', p__('fanwall','Display mode'), [
            'thumbnail' => p__('fanwall', 'Thumbnails') . ' ' . p__('fanwall', '(default)'),
            'slider' => p__('fanwall', 'Slider'),
        ]);

        $this->groupElements('group_photo', [
            'photo_position',
            'photo_mode',
        ], p__('fanwall', 'Photo options'));

        $maxImages = $this->addSimpleNumber('max_images', p__('fanwall', 'Max pictures allowed') . ' (0-10)', 0, 10, true, 1);
        $maxImages->setDescription(p__('fanwall', 'Set to 0 to disable image upload from the application.'));

        $helpText = p__('fanwall', 'If you enable any of user likes, posts or comments, the user profile & settings will be automatically added.');
        $help = <<<RAW
<div class="col-md-7 col-md-offset-3">
    <div class="alert alert-info">{$helpText}</div>
</div>
RAW;

        $this->addSimpleHtml('helper_text', $help);

        $icons = [
            'icon_post' => 'Posts',
            'icon_nearby' => 'Nearby',
            'icon_map' => 'Map',
            'icon_gallery' => 'Gallery',
            'icon_new' => 'New post',
            'icon_profile' => 'Profile',
        ];

        foreach ($icons as $column => $label) {
            $this->addSimpleImage($column, p__('fanwall', $label), p__('fanwall', $label), [
                'width' => 64,
                'height' => 64,
            ]);
        }

        $this->groupElements('group_icons', [
            'icon_post_button',
            'icon_nearby_button',
            'icon_map_button',
            'icon_gallery_button',
            'icon_new_button',
            'icon_profile_button',
        ], p__('fanwall', 'Custom icons'));

        $valueId = $this->addSimpleHidden('value_id');
        $valueId
            ->setRequired(true);

        $submit = $this->addSubmit(p__('fanwall', 'Save'), p__('fanwall', 'Save'));
        $submit->addClass('pull-right');
    }

    /**
     * @param $fanwallId
     */
    public function setFanwallId($fanwallId)
    {
        $this
            ->getElement('fanwall_id')
            ->setValue($fanwallId)
            ->setRequired(true);
    }
}
