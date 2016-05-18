<?php

class Template_View_Application_Design_List extends Admin_View_Default {

    public function getCategoryId($design) {

        $cat_class = array();
        foreach ($design->getCategoryIds() as $category_id) {
            $cat_class[] = $category_id;
            //pour le moment on ne récupere qu'une catégorie si il y en a plusieurs
            break;
        }

        return join("|", $cat_class);

    }

    public function getClass($design) {

        $cat_class = array();
        foreach ($design->getCategoryIds() as $category_id) {
            $cat_class[] = "cat" . $category_id;
        }

        return join(" ", $cat_class);

    }

}