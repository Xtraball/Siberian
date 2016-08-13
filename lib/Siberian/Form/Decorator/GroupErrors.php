<?php
/**
 * Class Siberian_Form_Decorator_GroupErrors
 */
class Siberian_Form_Decorator_GroupErrors extends Zend_Form_Decorator_Abstract {

    public function render($content) {
     	$elements = $this->getElement()->getElements();
        $view     = $this->getElement()->getView();
        if(null === $view) {
            return $content;
        }

        $errors = array();
        foreach($elements as $element) {
        	$errors = array_merge($errors,array_values($element->getMessages())); //ignore key
        }
        
        if(empty($errors)) {
            return $content;
        }
        
        $errors = $view->formErrors($errors, $this->getOptions());
        $separator = $this->getSeparator();
        $placement = $this->getPlacement();

        switch($placement) {
            case self::APPEND:
                return $content . $separator . $errors;
            case self::PREPEND:
                return $errors . $separator . $content;
        }
    }

}