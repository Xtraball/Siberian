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
     * @throws \Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/fanwall/application/edit-post"))
            ->setAttrib("id", "form-fanwall-post")
            ->addNav("nav-fanwall-post", p__("fanwall", "Save"), true, true);

        /** Bind as a create form */
        self::addClass("create", $this);

        $this->addSimpleHidden("post_id");

        $title = $this->addSimpleText("title", p__("fanwall","Title"));
        $title
            ->setRequired(true);

        $this->addSimpleText("subtitle", p__("fanwall","Subtitle"));

        $this->addSimpleImage("image", p__("fanwall","Add a picture"), p__("fanwall","Add a picture"), [
            "width" => 1000,
            "height" => 640,
        ]);

        $this->addSimpleDatetimepicker("date", p__("fanwall","Publication date"), false, self::DATETIMEPICKER);

        $text = $this->addSimpleTextarea("text", p__("fanwall","Post"));
        $text
            ->setRichtext()
            ->setRequired(true);

        $valueId = $this->addSimpleHidden("value_id");
        $valueId
            ->setRequired(true);
    }

    /**
     * @param $postId
     */
    public function setPostId($postId)
    {
        $this
            ->getElement("post_id")
            ->setValue($postId)
            ->setRequired(true);
    }

    /**
     * @throws \Zend_Form_Exception
     */
    public function loadFormSubmit()
    {
        $submit = $this->addSubmit(p__("fanwall", "Save"), p__("fanwall", "Save"));
        $submit->addClass("pull-right");
    }
}