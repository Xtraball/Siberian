<?php
/**
 * Class Siberian_Form_Decorator_FormHasErrors
 */
class Siberian_Form_Decorator_FormHasErrors extends Zend_Form_Decorator_FormErrors {

    /**
     * @param string $content
     * @return string
     */
 	public function render($content) {
        $form = $this->getElement();
        if(!$form instanceof Zend_Form) {
            return $content;
        }

        $view = $form->getView();
        if(null === $view) {
            return $content;
        }

        $this->initOptions();
        
        $errors = false;
        $els = $form->getElements();
        foreach($els as $el) {
        	$msgs = $el->getMessages();
        	if(count($msgs)>0) {
	        	$errors = true;
	        	break;
        	}
        }

        if(!$errors) {
            return $content;
        }

        $markup = __("Il y a des erreurs plus bas");

        $markup = $this->getMarkupListStart()
                . $view->formErrors(array($markup), $this->getOptions())
                . $this->getMarkupListEnd();

        switch($this->getPlacement()) {
            case self::APPEND:
                return $content . $this->getSeparator() . $markup;
            case self::PREPEND:
                return $markup . $this->getSeparator() . $content;
        }
    }
}