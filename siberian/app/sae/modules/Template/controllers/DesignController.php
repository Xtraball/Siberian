<?php

/**
 * Class Template_DesignController
 */
class Template_DesignController extends Application_Controller_Default
{

    /**
     * @var array
     */
    public $cache_triggers = [
        "save" => [
            "tags" => [
                "app_#APP_ID#",
                "css_app_#APP_ID#"
            ],
        ],
    ];

    /**
     *
     */
    public function listAction()
    {

        $layout = $this->getLayout();
        $layout->setBaseRender('modal', 'html/modal.phtml', 'core_view_default')->setTitle(__('TEMPLATES'))->setSubtitle(__('Choose a template to customize'));
        $layout->addPartial('modal_content', 'template_view_application_design_list', 'template/application/design/list.phtml')->setTitle('Test title');
        $html = ['modal_html' => $layout->render()];

        $layout->setHtml(Zend_Json::encode($html));

    }

    /**
     *
     */
    public function saveAction()
    {
        try {
            $request = $this->getRequest();
            $datas = $request->getParams();

            if (empty($datas)) {
                throw new \Siberian\Exception(__('#156: Missing params!'));
            }

            if (empty($datas['design_id'])) {
                throw new \Siberian\Exception(__('#118: An error occurred while saving'));
            }

            $application = $this->getApplication();
            $category = new Template_Model_Category();
            $design = (new Template_Model_Design())
                ->find($datas['design_id']);

            if (!$design->getId()) {
                throw new \Siberian\Exception(__('#119: An error occurred while saving'));
            } else if ($design->getCode() != "blank" && empty($datas['category_id'])) {
                throw new \Siberian\Exception(__('#120: An error occurred while saving'));
            }

            if (!empty($datas['category_id'])) {
                $category->find($datas['category_id']);
                if (!$category->getCode()) {
                    throw new \Siberian\Exception(__('#121: An error occurred while saving'));
                }
            }

            $layout = (new Application_Model_Layout_Homepage())
                ->find($design->getLayoutId());

            $this
                ->getApplication()
                ->setLayoutVisibility($layout->getVisibility())
                ->setDesign($design, $category)
                ->save();

            Template_Model_Design::generateCss(
                $this->getApplication(),
                false,
                false,
                true);

            $payload = [
                'success' => true,
                'overview_src' => $design->getOverview(),
                'homepage_unified' => $application->getHomepageBackgroundImageUrl('unified'),
                'app_icon' => $application->getIcon(),
                'layout_id' => $design->getLayoutId(),
                'display_layout_options' =>
                    ($application->getLayout()->getVisibility() == Application_Model_Layout_Homepage::VISIBILITY_ALWAYS),
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

}
