<?php

class Front_Mobile_HomeController extends Application_Controller_Mobile_Default {

    public function iframeAction() {
        $this->getRequest()->setParam('overview', 1);
        $this->getSession()->isOverview = true;
        $this->forward('index', 'index', 'Front', $this->getRequest()->getParams());
    }

    public function colorsAction() {
        $this->loadPartials("front_index_index", true);
        $this->getLayout()->addPartial("style", "core_view_mobile_default", "application/customization/css.phtml");
    }

    public function indexAction() {
        $this->getSession()->isOverview = false;
        parent::indexAction();
    }

    public function viewAction() {
        $this->loadPartials('home_mobile_view', false);
    }

    public function menuAction() {
        $this->loadPartials('home_mobile_view_l'.$this->getApplication()->getLayoutId(), false);
    }

    public function sliderAction() {
        $this->loadPartials('home_mobile_slider_view_l'.$this->getApplication()->getLayoutId(), false);
    }
    
    public function listAction() {
        $html = $this->getLayout()->addPartial("homepage_scrollbar", "core_view_mobile_default", "home/l1/list.phtml")->toHtml();
        $this->getLayout()->setHtml($html);
    }

    public function backgroundimageAction() {

        $urls = array(
            "standard" => $this->getApplication()->getHomepageBackgroundImageUrl(),
            "hd" => $this->getApplication()->getHomepageBackgroundImageUrl("hd"),
            "tablet" => $this->getApplication()->getHomepageBackgroundImageUrl("tablet"),
        );

        $this->_sendHtml($urls);
//        $this->_sendHtml(array('url' => $url));

    }

    public function findallAction() {

        $option_values = $this->getApplication()->getPages(10);
        $data = array('pages' => array());
        $color = $this->getApplication()->getBlock('tabbar')->getImageColor();
        $background_color = $this->getApplication()->getBlock('tabbar')->getBackgroundColor();

        foreach($option_values as $option_value) {
            $data['pages'][] = array(
                'value_id' => $option_value->getId(),
                'id' => intval($option_value->getId()),
                'layout_id' => $option_value->getLayoutId(),
                'code' => $option_value->getCode(),
                'name' => $option_value->getTabbarName(),
                'is_active' => $option_value->isActive(),
                'url' => $option_value->getUrl(null, array('value_id' => $option_value->getId()), false),
                'path' => $option_value->getPath(null, array('value_id' => $option_value->getId()), false),
                'icon_url' => $this->getRequest()->getBaseUrl().$this->_getColorizedImage($option_value->getIconId(), $color),
                'icon_is_colorable' => $option_value->getImage()->getCanBeColorized(),
                'is_locked' => $option_value->isLocked(),
                'is_link' => !$option_value->getIsAjax(),
                'position' => $option_value->getPosition()
            );
        }

        $option = new Application_Model_Option();
        $option->findTabbarMore();
        $data['more_items'] = array(
            'code' => $option->getCode(),
            'name' => $option->getTabbarName(),
            'is_active' => $option->isActive(),
            'url' => "",
            'icon_url' => $this->getRequest()->getBaseUrl().$this->_getColorizedImage($option->getIconUrl(), $color),
            'icon_is_colorable' => 1,
        );

        $option = new Application_Model_Option();
        $option->findTabbarAccount();
        $data['customer_account'] = array(
            'code' => $option->getCode(),
            'name' => $option->getTabbarName(),
            'is_active' => $option->isActive(),
            'url' => $this->getUrl("customer/mobile_account_login"),
            'path' => $this->getPath("customer/mobile_account_login"),
            'login_url' => $this->getUrl("customer/mobile_account_login"),
            'login_path' => $this->getPath("customer/mobile_account_login"),
            'edit_url' => $this->getUrl("customer/mobile_account_edit"),
            'edit_path' => $this->getPath("customer/mobile_account_edit"),
            'icon_url' => $this->getRequest()->getBaseUrl().$this->_getColorizedImage($option->getIconUrl(), $color),
            'icon_is_colorable' => 1,
            'is_visible' => $this->getApplication()->usesUserAccount()
        );

        $layout = new Application_Model_Layout_Homepage();
        $layout->find($this->getApplication()->getLayoutId());

        $data['layout'] = array(
            "layout_id" => "l{$this->getApplication()->getLayoutId()}",
            "visibility" => $this->getApplication()->getLayoutVisibility(),
            "use_horizontal_scroll" => (int) $layout->getUseHorizontalScroll(),
            "position" => $layout->getPosition()
        );

        $data['limit_to'] = $this->getApplication()->getLayout()->getNumberOfDisplayedIcons();
        $data['layout_id'] = 'l'.$this->getApplication()->getLayoutId();
        $data['tabbar_is_transparent'] = $background_color == "transparent";

        $data['homepage_slider_is_visible'] = (bool) $this->getApplication()->getHomepageSliderIsVisible();
        $data['homepage_slider_duration'] = $this->getApplication()->getHomepageSliderDuration();
        $data['homepage_slider_loop_at_beginning'] = (bool) $this->getApplication()->getHomepageSliderLoopAtBeginning();

        $push_number = 0;
        if($device_uid = $this->getRequest()->getParam("device_uid")) {
            $message = new Push_Model_Message();
            $push_number = $message->countByDeviceId($device_uid);
        }
        $data['push_badge'] = $push_number;

        $homepage_slider_images = array();
        $slider_images = $this->getApplication()->getSliderImages();
        foreach($slider_images as $slider_image) {
            $homepage_slider_images[] = $slider_image->getLink();
        }
        $data['homepage_slider_images'] = $homepage_slider_images;

        $this->_sendHtml($data);

    }

}