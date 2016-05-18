<?php

class Places_ApplicationController extends Application_Controller_Default {

    public function formAction() {


        $id = $this->getRequest()->getParam("id");

        try {

            $page = new Cms_Model_Application_Page();
            $page->find($id);
            if(!$page->getId()) {
                $page->setId("new");
            }

            $this->getLayout()->setBaseRender('form', 'cms/application/page/edit.phtml', 'admin_view_default')
                ->setCurrentPage($page)
                ->setOptionValue($this->getCurrentOptionValue())
                ->setCurrentFeature("places")
            ;

            $html = array(
                'form' => $this->getLayout()->render(),
                'success' => 1
            );

        } catch (Exception $e) {
            $html = array(
                'message' => $e->getMessage()
            );
        }

        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

}