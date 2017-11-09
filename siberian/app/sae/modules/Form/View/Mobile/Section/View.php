<?php

class Form_View_Mobile_Section_View extends Core_View_Mobile_Default {

    public function drawCategories($categories, $parent = null) {

        $html = '';
        $children_html = '';
        $products_html = '';

        if($parent) {
            $html .= '<div id="category_'.$parent->getId().'" rel="'.$parent->getId().'" class="list scrollview" style="display: none;">';
            $html .= '<ul>';
            $html .= '<li class="category border align-center"><a href="javascript:void(0)" onclick="goBack(\'category_'.$parent->getId().'\');">'.$parent->getName().'</a></li>';
        }
        else {
            $html .= '<div id="categories_scrollview" class="scrollview list">';
            $html .= '<ul id="categories_list">';
        }

        foreach($categories as $category) {
            $html .= '<li class="category border">';
            $html .= '<a href="javascript:void(0);" id="category_name_'.$category->getId().'" onclick="goForth(\'category_'.$category->getId().'\')">'.$category->getName().'</a>';
            if($category->getChildren()->count() > 0) {
                $children_html .= $this->drawCategories($category->getChildren(), $category);
            }
            else {
                $products = $category->getActiveProducts();
                if(count($products) > 0) {
                    $products_html .= $this->drawProducts($category->getActiveProducts(), $category);
                }
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
        $html .= '</div>';


        $html .= $children_html;
        $html .= $products_html;

        return $html;

    }

    public function drawProducts($parent) {

        $products = array();
        foreach($parent->getProducts() as $product) {
            $products[$product->getPosition()] = $product;
        }
        foreach($parent->getChildren() as $category) {
            foreach($category->getProducts() as $product) $products[$product->getPosition()] = $product;
        }
        ksort($products);
        $html = '';
        $parent_id = $parent->getId();
        $display = 'none';
        $html .= '<div id="scrollview_products_'.$parent_id.'" rel="'.$parent_id.'" class="list scrollview_products" style="display: '.$display.';">';
        $html .= '<ul id="products_list" style="position:realtive">';

        foreach($products as $product) {
            $html .= '<li class="product" style="width:316px" onclick="toggleProductDetails(this);" rel="'.$parent_id.'" category_id="'.$product->getCategoryId().'">';
            $this->getLayout()->addPartial('product_'.$product->getId(), 'core_view_default', 'catalog/category/l1/view/product.phtml')
                ->setProduct($product)
                ->setCategory($parent)
            ;
            $html .= $this->getLayout()->getPartialHtml('product_'.$product->getId());
            $html .= '</li>';
            $html .= '<li class="separator" rel="'.$parent_id.'" category_id="'.$product->getCategoryId().'"></li>';
        }

        $html .= '</ul>';

        $html .= '</div>';

        return $html;
    }

}
