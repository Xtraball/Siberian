<?php

namespace Fanwall\Form\Post;

use Siberian_Form_Abstract as FormAbstract;
use Zend_Db_Table;

/**
 * Class Pin
 * @package Fanwall\Form\Post
 */
class Pin extends FormAbstract
{

    /**
     * @throws \Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/fanwall/application/pin-post"))
            ->setAttrib("id", "form-fanwall-pin");

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

        $this->addMiniSubmit(null, "<i class='icofont icofont-tack-pin' style='color: rgba(43, 179, 25, 1);'></i>", "<i class='icofont icofont-tack-pin' style='color: rgba(200, 0, 0, 1);'></i>");

        $this->defaultToggle($this->mini_submit, p__("fanwall", "Pin post"), p__("fanwall", "Unpin post"));
    }
}