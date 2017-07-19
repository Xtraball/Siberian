<?php

class Application_Customization_Design_ColorsController extends Application_Controller_Default {

    /**
     * @var array
     */
    public $cache_triggers = array(
        "save" => array(
            "tags" => array(
                "css_app_#APP_ID#",
                "app_#APP_ID#"
            ),
        ),
    );

    public function editAction() {
        $this->loadPartials();

        if($this->getRequest()->isXmlHttpRequest()) {
            $html = array('html' => $this->getLayout()->getPartial('content_editor')->toHtml());
            $this->getLayout()->setHtml(Zend_Json::encode($html));
        } else if($this->getApplication()->getDesignCode() == Application_Model_Application::DESIGN_CODE_ANGULAR) {
            $this->getLayout()->getPartial("content_editor")->setTemplate("application/customization/design/colors/angular/edit.phtml");
            $this->getLayout()->getPartial("overview")->setTemplate("application/customization/index/overview/colors/angular.phtml");
        }
    }

    public function saveAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                $html = array();

                // S'il y a embrouille
                if(empty($datas['block_id']) && !isset($datas["custom_scss"])) {
                    throw new Exception(__("#354-01: An error occurred while saving your colors."));
                }

                // Récupère l'application en cours
                $application = $this->getApplication();

                // Récupère le block
                $block = new Template_Model_Block();
                $block->find($datas['block_id']);
                // S'il y a re-embrouille
                if(!$block->getId() && !isset($datas["custom_scss"])) {
                    throw new Exception(__("#354-02: An error occurred while saving your colors."));
                }
                else {
                    $block->unsData();
                }

                if(!empty($datas['color'])) {
                    $block->setData('color', $datas['color']);
                }
                if(!empty($datas['background_color'])) {
                    $block->setData('background_color', $datas['background_color']);
                }
                if(!empty($datas['border_color'])) {
                    $block->setData('border_color', $datas['border_color']);
                }
                if(!empty($datas['tabbar_color'])) {
                    $block->setData('image_color', $datas['tabbar_color']);
                }
                if(!empty($datas['image_color'])) {
                    $block->setData('image_color', $datas['image_color']);
                }
                if(isset($datas['custom_scss'])) {
                    $html["success_message"] = __("SCSS successfully saved");

                    $application->setData('custom_scss', $datas['custom_scss']);
                    $application->save();
                }


                $block
                    ->setTextOpacity($datas) // Includes verification of the existence of `text_opacity`, `border_opacity` and its validation
                    ->setBorderOpacity($datas) // Includes verification of the existence of `border_opacity` and its validation
                    ->setBackgroundOpacity($datas) // Includes verification of the existence of `background_opacity` and its validation
                    ->setImageOpacity($datas); // Includes verification of the existence of `image_opacity` and its validation

                $block->setBlockId($datas['block_id'])
                    ->setAppId($application->getId())
                    ->save()
                ;

                if($application->useIonicDesign()) {
                    $result = Template_Model_Design::generateCss($application, false, false, true);

                    if(!$result) {
                        throw new Exception(__("#354-03: SCSS Compilation error, you must input valid SCSS."));
                    }
                }

                $html["success"] = 1;
                $html["tabbar_is_transparent"] = ($block->getBackgroundColor() == "transparent");
            }
            catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage()
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }

    }
}
