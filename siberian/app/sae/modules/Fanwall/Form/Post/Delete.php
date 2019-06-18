<?php

namespace Fanwall\Form\Post;

use Siberian_Form_Abstract as FormAbstract;
use Zend_Db_Table;

/**
 * Class Delete
 * @package Fanwall\Form\Post
 */
class Delete extends FormAbstract
{

    /**
     * @throws \Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/fanwall/application/delete-post"))
            ->setAttrib("id", "form-fanwall-delete")
            ->setConfirmText(p__("fanwall", "You are about to remove this Post ! Are you sure ?"));;

        /** Bind as a delete form */
        self::addClass("delete", $this);

        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select()
            ->from("fanwall_post")
            ->where('fanwall_post.post_id = :value');

        $postId = $this->addSimpleHidden("post_id", p__("fanwall", "Post"));
        $postId->addValidator("Db_RecordExists", true, $select);
        $postId->setMinimalDecorator();

        $valueId = $this->addSimpleHidden("value_id");
        $valueId
            ->setRequired(true);

        $this->addMiniSubmit();
    }
}