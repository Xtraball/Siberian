<?php

class Template_DesignController extends Application_Controller_Default {

    /**
     * @var array
     */
    public $cache_triggers = array(
        "save" => array(
            "tags" => array("app_#APP_ID#"),
        ),
    );

    public function listAction() {

        $layout = $this->getLayout();
        $layout->setBaseRender('modal', 'html/modal.phtml', 'core_view_default')->setTitle($this->_('TEMPLATES'))->setSubtitle($this->_('Choose a template to customize'));
        $layout->addPartial('modal_content', 'template_view_application_design_list', 'template/application/design/list.phtml')->setTitle('Test title');
        $html = array('modal_html' => $layout->render());

        $layout->setHtml(Zend_Json::encode($html));

    }

    public function saveAction() {

        if($datas = $this->getRequest()->getParams()) {

            try {
                if(empty($datas['design_id'])) throw new Exception($this->_('#118: An error occurred while saving'));

                $application = $this->getApplication();
                $category = new Template_Model_Category();
                $design = new Template_Model_Design();
                $design->find($datas['design_id']);

                if(!$design->getId()) throw new Exception($this->_('#119: An error occurred while saving'));
                else if($design->getCode() != "blank" && empty($datas['category_id'])) throw new Exception($this->_('#120: An error occurred while saving'));

                if(!empty($datas['category_id'])) {

                    $category->find($datas['category_id']);                    
                    if(!$category->getCode()) {
                        throw new Exception($this->_('#121: An error occurred while saving'));
                    }
                    
                }

                $this->getApplication()
                    ->setLayoutVisibility(Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE)
                    ->setDesign($design, $category)
                    ->save()
                ;

                if($this->getApplication()->useIonicDesign()) {
                    Template_Model_Design::generateCss($this->getApplication(), false, false, true);
                }

                $layout_model = new Application_Model_Layout_Homepage();
                $layout = $layout_model->find($design->getLayoutId());

                $html = array(
                    'success' => 1,
                    'overview_src' => $design->getOverview(),
                    'homepage_standard' => $application->getHomepageBackgroundImageUrl(),
                    'homepage_hd' => $application->getHomepageBackgroundImageUrl('hd'),
                    'homepage_tablet' => $application->getHomepageBackgroundImageUrl('tablet'),
                    'app_icon' => $application->getIcon(),
                    'layout_id' => $design->getLayoutId(),
                    "display_layout_options" => $application->getLayout()->getVisibility() == Application_Model_Layout_Homepage::VISIBILITY_ALWAYS
                );
            }
            catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_buttom' => 1,
                    'message_loader' => 1
                );
            }
        }

        $this->getLayout()->setHtml(Zend_Json::encode($html));

    }

}
