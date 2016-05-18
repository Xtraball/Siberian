<?php

class Application_PreviewerController extends Application_Controller_Default {

    public function modalAction() {

        $layout = $this->getLayout();
        $layout->setBaseRender('modal', 'html/modal.phtml', 'core_view_default')->setTitle($this->_('Preview'))->setSubtitle($this->_('Preview and test your native apps directly on your mobile.'));
        $layout->addPartial('modal_content', 'admin_view_default', 'application/previewer/modal.phtml');
        $html = array('modal_html' => $layout->render());

        $this->getResponse()->setBody(Zend_Json::encode($html))->sendResponse();
        die;

    }

}
