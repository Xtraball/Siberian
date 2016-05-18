<?php

class Form_MobileController extends Application_Controller_Mobile_Default {

    public function viewAction() {
        $this->loadPartials($this->getFullActionName('_') . '_l' . $this->_layout_id, false);
        $option = $this->getCurrentOptionValue();

        $section = new Form_Model_Section();
        $sections = $section->findByValueId($option->getId());

        foreach ($sections as $section) {
            $section->findFields($section->getId());
        }

        $this->getLayout()->getPartial('content')->setSections($sections);

        $html = array('html' => $this->getLayout()->render());
        if ($url = $option->getBackgroundImageUrl()) {
            $html['background_image_url'] = $url;
        }
        $html['use_homepage_background_image'] = (int) $option->getUseHomepageBackgroundImage() && !$option->getHasBackgroundImage();
        $html['title'] = $option->getTabbarName();

        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    /**
     * Sauvegarde
     */
    public function postAction() {

        if ($datas = $this->getRequest()->getPost()) {
            try {
                $errors = '';
                // Recherche des sections
                $section = new Form_Model_Section();
                $sections = $section->findByValueId($this->getCurrentOptionValue()->getId());

                $field = new Form_Model_Field();

                // Validator date
                $validator = new Zend_Validate_Date(array('format' => 'yyyy-mm-dd'));

                foreach ($sections as $k => $section) {
                    // Recherche les fields de la section
                    $section->findFields($section->getId());
                    // Boucle sur les fields
                    foreach($section->getFields() as $key => $field) {
                        if($field->isRequired() == 1 && $datas['field_'.$k.'_'.$key] == '') {
                            $errors .= $this->_('<strong>%s</strong> must be filled<br />', $field->getName());
                        }
                        if($field->getType() == 'email' && !Zend_Validate::is($datas['field_'.$k.'_'.$key], 'EmailAddress')) {
                            $errors .= $this->_('<strong>%s</strong> must be a valid email address<br />', $field->getName());
                        }
                        if($field->getType() == 'nombre' && !Zend_Validate::is($datas['field_'.$k.'_'.$key], 'Digits')) {
                            $errors .= $this->_('<strong>%s</strong> must be a numerical value<br />', $field->getName());
                        }
                        if($field->getType() == 'date' && !$validator->isValid($datas['field_'.$k.'_'.$key])) {
                            $errors .= $this->_('<strong>%s</strong> must be a valid date<br />', $field->getName());
                        }
                        $datasChanged['field_'.$k.'_'.$key] = array('name' => $field->getName(), 'value' => $datas['field_'.$k.'_'.$key]);
                    }
                }

                if(empty($errors)) {

                    $form = $this->getCurrentOptionValue()->getObject();

                    $layout = $this->getLayout()->loadEmail('form', 'send_email');
                    $layout->getPartial('content_email')
                        ->setDatas($datasChanged);
                    $content = $layout->render();

                    $mail = new Zend_Mail('UTF-8');
                    $mail->setBodyHtml($content);
                    $mail->setFrom($form->getEmail(), $this->getApplication()->getName());
                    $mail->addTo($form->getEmail(), $this->_('Your app\'s form'));
                    $mail->setSubject($this->_('Your app\'s form'));
                    $mail->send();

                    $html = array('success' => 1);
                } else {
                    $html = array('error' => 1, 'message' => $errors);
                }


            } catch (Exception $e) {
                $html = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($html);
        }
    }

}