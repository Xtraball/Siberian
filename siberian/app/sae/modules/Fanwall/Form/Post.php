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
     * @throws \Zend_Exception
     * @throws \Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

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
            ->setRichtext()
            ->setRequired(true);

        $this->addSimpleImage('image', p__('fanwall','Add a picture'), p__('fanwall','Add a picture'), [
            'width' => 1000,
            'height' => 640,
        ]);

        $valueId = $this->addSimpleHidden('value_id');
        $valueId
            ->setRequired(true);

        // Defaults date to NOW() for new Pots
        $this->setDate(time());
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
}
