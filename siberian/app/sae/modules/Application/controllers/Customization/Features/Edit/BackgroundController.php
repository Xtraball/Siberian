<?php

class Application_Customization_Features_Edit_BackgroundController extends Application_Controller_Default {

    /**
     * @var array
     */
    public $cache_triggers = array(
        "save" => array(
            "tags" => array("app_#APP_ID#"),
        ),
    );

    /**
     * Simple edit post, validator
     */
    public function saveAction() {
        $values = $this->getRequest()->getPost();

        $form = new Application_Form_BackgroundImage();
        if($form->isValid($values)) {
            /** Do whatever you need when form is valid */
            $option_value = $this->getCurrentOptionValue();

            if($values['background_image'] == '_delete_') {
                $option_value->setData('background_image', '"');
            } else if(file_exists(Core_Model_Directory::getBasePathTo("images/application" . $values['background_image']))) {
                # Nothing changed, skip
            } else {
                $background = Siberian_Feature::moveUploadedFile($this->getCurrentOptionValue(),
                    Core_Model_Directory::getTmpDirectory() . '/' . $values['background_image']);
                $option_value->setData('background_image', $background);
            }

            if($values['background_landscape_image'] == '_delete_') {
                $option_value->setData('background_landscape_image', '"');
            } else if(file_exists(Core_Model_Directory::getBasePathTo("images/application" . $values['background_landscape_image']))) {
                # Nothing changed, skip
            } else {
                $background = Siberian_Feature::moveUploadedFile($this->getCurrentOptionValue(),
                    Core_Model_Directory::getTmpDirectory() . '/' . $values['background_landscape_image']);
                $option_value->setData('background_landscape_image', $background);
            }

            $option_value->save();

            // Update touch date, then never expires (until next touch)!
            $option_value
                ->touch()
                ->expires(-1);

            $payload = array(
                'success' => 1,
                'message' => __('Success.'),
            );
        } else {
            /** Do whatever you need when form is not valid */
            $payload = array(
                'error' => 1,
                'message' => $form->getTextErrors(),
                'errors' => $form->getTextErrors(true)
            );
        }

        $this->_sendJson($payload);
    }
}
