<?php

/** Zend_Layout_Controller_Plugin_Layout */
require_once 'Zend/Layout/Controller/Plugin/Layout.php';

class Siberian_Layout_Controller_Plugin_Layout extends Zend_Layout_Controller_Plugin_Layout
{

    /**
     * postDispatch() plugin hook -- render layout
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $layout = $this->getLayout();
        $helper = $this->getLayoutActionHelper();

        // Return early if forward detected
        if (!$request->isDispatched()
            || $this->getResponse()->isRedirect()
            || ($layout->getMvcSuccessfulActionOnly()
                && (!empty($helper) && !$helper->isActionControllerSuccessful())))
        {
            return;
        }

        // Return early if layout has been disabled
        if (!$layout->isEnabled() OR !$this->getLayout()->isLoaded()) {
            return;
        }

        $response   = $this->getResponse();
        $content    = $response->getBody(true);
        $contentKey = $layout->getContentKey();

        if (isset($content['default'])) {
            $content[$contentKey] = $content['default'];
        }
        if ('default' != $contentKey) {
            unset($content['default']);
        }

        $layout->assign($content);

        $fullContent = null;
        $obStartLevel = ob_get_level();
        try {
            $fullContent = $layout->render();
            $response->setBody($fullContent);
        } catch (Exception $e) {
            while (ob_get_level() > $obStartLevel) {
                $fullContent .= ob_get_clean();
            }

            $request->setParam('layoutFullContent', $fullContent);
            $request->setParam('layoutContent', $layout->content);
            $response->setBody(null);
            if (Zend_Controller_Front::getInstance()->throwExceptions()) {
                throw $e;
            } else {
                $this->getResponse()->setException($e);
            }

        }

        return $this;
    }
}
