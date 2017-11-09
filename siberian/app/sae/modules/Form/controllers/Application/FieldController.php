<?php

class Form_Application_FieldController extends Application_Controller_Default {

    /**
     * @var array
     */
    public $cache_triggers = array(
        "editpost" => array(
            "tags" => array(
                "homepage_app_#APP_ID#",
            ),
        ),
        "sortfields" => array(
            "tags" => array(
                "homepage_app_#APP_ID#",
            ),
        ),
    );

    /**
     * Affichage de la boxe
     */
    public function editAction() {
        $field_id = $this->getRequest()->getParam('field_id');

        if ($field_id) {
            $field = new Form_Model_Field();
            $myField = $field->find($field_id);
        }

        $html = $this->getLayout()
            ->addPartial('row', 'admin_view_default', 'form/application/edit/field.phtml')
            ->setOptionValue($this->getRequest()->getParam('option_value_id'))
            ->setSectionId($this->getRequest()->getParam('section_id'))
            ->setField((!$field_id ? false : $myField))
            ->toHtml()
        ;

        $this->getLayout()->setHtml($html);

    }

    /**
     * Sauvegarde des infos d'un champ
     */
    public function editpostAction() {

        if ($datas = $this->getRequest()->getPost()) {
            try {

                $html = array();

                // Recherche d'un champ déjà configuré
                $field = new Form_Model_Field();
                if (!empty($datas['field_id'])) {
                    $field->find($datas['field_id']);
                }

                // Si c'est un nouveau champ
                $isNew = (bool) !$field->getId();

                // Suppression ?
                $isDeleted = !empty($datas['is_deleted']);

                // Si pas dans le cas de la suppression
                if (!$isDeleted) {
                    $datas['value_id'] = $this->getCurrentOptionValue()->getId();

                    // Si pas besoin d'option, on supprime
                    if ($datas['type'] != 'checkbox' && $datas['type'] != 'radio' && $datas['type'] != 'select') {
                        $datas['option'] = array();
                    }
                    // Gestion des options
                    if (is_array($datas['option'])) {
                        foreach ($datas['option'] as $key => $option) {
                            if (empty($option)) {
                                unset($datas['option'][$key]);
                            }
                        }
                    }
                    $datas['option'] = implode(';', $datas['option']);
                }

                if(!isset($datas['required'])) {
                    $datas['required'] = 0;
                }

                // Enregistrement
                $field->addData($datas);
                $field->save();

                $this->getCurrentOptionValue()
                    ->touch()
                    ->expires(-1);

                // Success
                $html = array('success' => 1);

                if (!$isDeleted) {

                    $field_id = $field->getId();
                    $field = new Form_Model_Field();
                    $field->find($field_id);

                    $html = array(
                        'success' => 1,
                        'field_id' => $field->getId(),
                        'section_id' => $datas['section_id']
                    );

                    $html['field_html'] = $this->getLayout()
                            ->addPartial('row', 'admin_view_default', 'form/application/edit/section/field.phtml')
                            ->setField($field)
                            ->setOptionValue($this->getCurrentOptionValue())
                            ->toHtml();



                }
            } catch (Exception $e) {
                $html['message'] = $e->getMessage();
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    /**
     * Organise les champs
     */
    public function sortfieldsAction() {

        if ($rows = $this->getRequest()->getParam('field')) {

            $html = array();
            try {

                if(!$this->getCurrentOptionValue()) {
                    throw new Exception('Une erreur est survenue lors de la sauvegarde. Veuillez réessayer ultérieurement.');
                }

                $field = new Form_Model_Field();

                $fields = $field->findBySectionId($this->getRequest()->getParam('section_id'));
                $fields_id = array();

                foreach ($fields as $currField) {
                    $fields_id[] = $currField->getId();
                }

                foreach ($rows as $key => $row) {
                    if (!in_array($row, $fields_id)) {
                        throw new Exception($this->_('An error occurred while saving. A field can\'t be identified.'));
                    }
                }
                $field->updatePosition($rows);

                $html = array(
                    'success' => 1
                );
            } catch (Exception $e) {
                $html = array('message' => $e->getMessage());
            }


            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

}
