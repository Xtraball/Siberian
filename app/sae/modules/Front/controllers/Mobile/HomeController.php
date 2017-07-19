<?php

class Front_Mobile_HomeController extends Application_Controller_Mobile_Default {

    /**
     * @deprecated
     */
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

    }

    /**
     * @deprecated
     *
     * replaced by front/mobile/loadv2
     */
    public function findallAction() {

        $application = $this->getApplication();
        $app_id = $application->getId();
        $request = $this->getRequest();
        $current_language = Core_Model_Language::getCurrentLanguage();

        $application = $this->getApplication();

        $cache_id = "pre4812_front_mobile_home_findall_app_{$application->getId()}";

        if(!$result = $this->cache->load($cache_id)) {

            $option_values = $application->getPages(10);
            $data = array('pages' => array());
            $color = $this->getApplication()->getBlock('tabbar')->getImageColor();
            $background_color = $this->getApplication()->getBlock('tabbar')->getBackgroundColor();

            foreach ($option_values as $option_value) {
                $data['pages'][] = array(
                    'value_id'          => $option_value->getId(),
                    'id'                => intval($option_value->getId()),
                    'layout_id'         => $option_value->getLayoutId(),
                    'code'              => $option_value->getCode(),
                    'name'              => $option_value->getTabbarName(),
                    'subtitle'          => $option_value->getTabbarSubtitle(),
                    'is_active'         => !!$option_value->isActive(),
                    'url'               => $option_value->getUrl(null, array('value_id' => $option_value->getId()), false),
                    'path'              => $option_value->getPath(null, array('value_id' => $option_value->getId()), false),
                    'icon_url'          => $this->getRequest()->getBaseUrl() . $this->_getColorizedImage($option_value->getIconId(), $color),
                    'icon_is_colorable' => !!$option_value->getImage()->getCanBeColorized(),
                    'is_locked'         => !!$option_value->isLocked(),
                    'is_link'           => !$option_value->getIsAjax(),
                    'use_my_account'    => !!$option_value->getUseMyAccount(),
                    'use_nickname'      => !!$option_value->getUseNickname(),
                    'use_ranking'       => !!$option_value->getUseRanking(),
                    'offline_mode'      => !!$option_value->getObject()->isCacheable(),
                    'custom_fields'     => $option_value->getCustomFields(),
                    'position'          => $option_value->getPosition()
                );
            }

            $option = new Application_Model_Option();
            $option->findTabbarMore();

            $more_colorizable = true;
            if ($this->getApplication()->getMoreIconId()) {
                $library = new Media_Model_Library_Image();
                $icon = $library->find($this->getApplication()->getMoreIconId());
                if (!$icon->getCanBeColorized()) {
                    $more_color = null;
                } else {
                    $more_color = $color;
                }

                $more_colorizable = $icon->getCanBeColorized();
            } else {
                $more_color = $color;
            }

            $data['more_items'] = array(
                'code'                  => $option->getCode(),
                'name'                  => $option->getTabbarName(),
                'subtitle'              => $this->getApplication()->getMoreSubtitle(),
                'is_active'             => !!$option->isActive(),
                'url'                   => "",
                'icon_url'              => $this->getRequest()->getBaseUrl() . $this->_getColorizedImage($option->getIconUrl(), $more_color),
                'icon_is_colorable'     => !!$more_colorizable,
            );

            $option = new Application_Model_Option();
            $option->findTabbarAccount();

            $account_colorizable = true;
            if ($this->getApplication()->getAccountIconId()) {
                $library = new Media_Model_Library_Image();
                $icon = $library->find($this->getApplication()->getAccountIconId());
                if (!$icon->getCanBeColorized()) {
                    $account_color = null;
                } else {
                    $account_color = $color;
                }

                $account_colorizable = $icon->getCanBeColorized();
            } else {
                $account_color = $color;
            }

            $data['customer_account'] = array(
                'code'                  => $option->getCode(),
                'name'                  => $option->getTabbarName(),
                'subtitle'              => $this->getApplication()->getAccountSubtitle(),
                'is_active'             => !!$option->isActive(),
                'url'                   => $this->getUrl("customer/mobile_account_login"),
                'path'                  => $this->getPath("customer/mobile_account_login"),
                'login_url'             => $this->getUrl("customer/mobile_account_login"),
                'login_path'            => $this->getPath("customer/mobile_account_login"),
                'edit_url'              => $this->getUrl("customer/mobile_account_edit"),
                'edit_path'             => $this->getPath("customer/mobile_account_edit"),
                'icon_url'              => $this->getRequest()->getBaseUrl() . $this->_getColorizedImage($option->getIconUrl(), $account_color),
                'icon_is_colorable'     => !!$account_colorizable,
                'is_visible'            => !!$this->getApplication()->usesUserAccount()
            );

            $layout = new Application_Model_Layout_Homepage();
            $layout->find($this->getApplication()->getLayoutId());

            $layout_options = $this->getApplication()->getLayoutOptions();
            if (!empty($layout_options) && $opts = Siberian_Json::decode($layout_options)) {
                $layout_options = $opts;
            } else {
                $layout_options = false;
            }

            $data['layout'] = array(
                "layout_id"                 => "l{$this->getApplication()->getLayoutId()}",
                "layout_code"               => $this->getApplication()->getLayout()->getCode(),
                "layout_options"            => $layout_options,
                "visibility"                => $this->getApplication()->getLayoutVisibility(),
                "use_horizontal_scroll"     => (int)$layout->getUseHorizontalScroll(),
                "position"                  => $layout->getPosition()
            );

            $data['limit_to']               = $this->getApplication()->getLayout()->getNumberOfDisplayedIcons();
            $data['layout_id']              = 'l' . $this->getApplication()->getLayoutId();
            $data['layout_code']            = $this->getApplication()->getLayout()->getCode();
            $data['tabbar_is_transparent']  = !!($background_color == "transparent");

            $data['homepage_slider_is_visible']             = !!$this->getApplication()->getHomepageSliderIsVisible();
            $data['homepage_slider_duration']               = $this->getApplication()->getHomepageSliderDuration();
            $data['homepage_slider_loop_at_beginning']      = !!$this->getApplication()->getHomepageSliderLoopAtBeginning();
            $data['homepage_slider_size']                   = $this->getApplication()->getHomepageSliderSize();
            $data['homepage_slider_is_new']                 = !!($data['homepage_slider_size'] != null);

            $homepage_slider_images = array();
            $slider_images = $this->getApplication()->getSliderImages();
            foreach ($slider_images as $slider_image) {
                $homepage_slider_images[] = $slider_image->getLink();
            }
            $data['homepage_slider_images'] = $homepage_slider_images;

            foreach($application->getOptions() as $opt) {
              $data['layouts'][$opt->getValueId()] = $opt->getLayoutId();
            }

            $this->cache->save($data, $cache_id, array(
                "front_mobile_home_findall",
                "app_".$application->getId(),
                "homepage_app_".$application->getId(),
                "css_app_".$app_id,
                "mobile_translation",
                "mobile_translation_locale_{$current_language}"
            ));

            $data["x-cache"] = "MISS";
        } else {

            $data = $result;

            $data["x-cache"] = "HIT";
        }

        $this->_sendJson($data);

    }

}
