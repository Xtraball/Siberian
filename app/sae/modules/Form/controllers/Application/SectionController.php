<?php

class Form_Application_SectionController extends Application_Controller_Default {

    /**
     * @var array
     */
    public $cache_triggers = array(
        "editpost" => array(
            "tags" => array(
                "homepage_app_#APP_ID#",
            ),
        ),
    );

    public function editpostAction() {

        if ($datas = $this->getRequest()->getPost()) {

            try {
                // Flag utilisé pour le retour HTML
                $isSection = false;

                // Récupère le formulaire
                $form = $this->getCurrentOptionValue()->getObject();

                // Init HTML
                $html = array();

                // On récupère le value_id
                $datas['value_id'] = $this->getCurrentOptionValue()->getId();

                // Cas de la sauvegarde du mail
                if(isset($datas['email'])) {

                    $error = false;
                    $emails = explode(",", $datas['email']);
                    foreach($emails as $email) {
                        if (empty($email) OR !Zend_Validate::is($email, 'EmailAddress')) {
                            $html = array('error' => 1, 'message' => __('<strong>%s</strong> must be a valid email address<br />', __('Recipient email')));
                            $error = true;
                        } else {


                        }
                    }

                    if(!$error) {
                        if(!$form->getId()) {
                            $form->setValueId($datas['value_id']);
                        }
                        $form->setEmail($datas['email'])->save();
                        unset($datas['email']);
                    }
                    
                }
                else {

                    // Init du model Section
                    $section = new Form_Model_Section();
                    if(!empty($datas['section_id'])) {
                        $section->find($datas['section_id']);
                        if($section->getId() AND $section->getValueId() != $this->getCurrentOptionValue()->getId()) {
                            throw new Exception(__('An error occurred while saving. Please try again later.'));
                        }
                    }

                    // On sauvegarde
                    $section->addData($datas)->save();
                    $isSection = true;
                }
                if(!$error) {
                    $html = array(
                        'success' => 1,
                        'success_message' => __('Info successfully saved'),
                        'message_timeout' => 2,
                        'message_button' => 0,
                        'message_loader' => 0
                    );

                    if ($isSection) {
                        // Construit le html
                        $html['section_id'] = $section->getId();
                        $html['is_deleted'] = $section->getIsDeleted() ? 1 : 0;
                        $html['section_html'] = $this->getLayout()
                            ->addPartial('row', 'admin_view_default', 'form/application/edit/section.phtml')
                            ->setSection($section)
                            ->setOptionValue($this->getCurrentOptionValue())
                            ->toHtml();
                    }

                    $this->getCurrentOptionValue()
                        ->touch()
                        ->expires(-1);
                }
            } catch (Exception $e) {
                // Erreur
                if(!isset($datas['is_deleted'])) {
                    $html['message'] = $e->getMessage();
                }
            }

            // Envoi la réponse
            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
        
        return $this;
    }

}