<?php

class Wordpress_View_Mobile_L1_Wordpress extends Core_View_Mobile_Default {

    public function drawCategories($category, $parent = null) {
        return $this->getLayout()->addPartial('category_'.$category->getId(), get_class($this), 'wordpress/l1/view/categories.phtml')
            ->setCurrentCategory($category)
            ->setParentCategory($parent)
            ->toHtml()
        ;
    }

    public function drawRow($category, $parent) {
        return $this->getLayout()->addPartial('category_'.$category->getId(), get_class($this), 'wordpress/l1/view/categories/row.phtml')
            ->setCurrentCategory($category)
            ->setParentCategory($parent)
            ->toHtml()
        ;
    }

    public function drawPost($post, $parent) {
        return $this->getLayout()->addPartial('category_'.$parent->getId(), get_class($this), 'wordpress/l1/view/categories/post.phtml')
            ->setCurrentPost($post)
            ->setParentCategory($parent)
            ->toHtml()
        ;
    }

}