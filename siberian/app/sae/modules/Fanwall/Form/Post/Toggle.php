<?php

namespace Fanwall\Form\Post;

use Siberian_Form_Abstract as FormAbstract;
use Zend_Db_Table;

/**
 * Class Toggle
 * @package Fanwall\Form\Post
 */
class Toggle extends FormAbstract
{

    /**
     * @throws \Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/fanwall/application/toggle-post"))
            ->setAttrib("id", "form-fanwall-toggle");

        /** Bind as a delete form */
        self::addClass("toggle", $this);

        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select()
            ->from('fanwall_post')
            ->where('fanwall_post.post_id = :value');

        $postId = $this->addSimpleHidden("post_id", p__("fanwall", "Post"));
        $postId->addValidator("Db_RecordExists", true, $select);
        $postId->setMinimalDecorator();

        $valueId = $this->addSimpleHidden("value_id");
        $valueId
            ->setRequired(true);

        $this->addMiniSubmit(null, "<i class='fa fa-power-off icon icon-power-off'></i>", "<i class='fa fa-check icon icon-ok'></i>");

        $this->defaultToggle($this->mini_submit, p__("fanwall", "Publish post"), p__("fanwall", "Unpublish post"));
    }
}