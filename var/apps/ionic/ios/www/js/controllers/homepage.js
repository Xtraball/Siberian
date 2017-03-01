App.config(function ($stateProvider, $urlRouterProvider) {

    $stateProvider.state('home', {
        url: BASE_PATH,
        templateUrl: 'templates/home/view.html',
        controller: 'HomeController'
    });

    $urlRouterProvider.otherwise(BASE_PATH);

}).controller('HomeController', function($ionicHistory, $injector, $location, $rootScope, $scope, $state, $timeout, $window, Application, Padlock) {

    var HomepageLayout = $injector.get("HomepageLayout");

    $ionicHistory.clearHistory();

    console.log((new Date()).getTime(), "HomeController");

    $scope.loadContent = function() {

        $scope.is_loading = true;

        if($window.localStorage.getItem('sb-uc')) {
            Padlock.unlock_by_qrcode = true;
        }

        console.log((new Date()).getTime(), "HomepageLayout.getFeatures()");

        HomepageLayout.getFeatures().then(function (features) {

            $scope.layout_id = HomepageLayout.properties.layoutId;

            $scope.app_is_bo_locked = $rootScope.app_is_bo_locked;

            /** Homepage Slider */
            var homepage_slider =  {
                is_active_for_layout: (HomepageLayout.properties.menu.visibility == 'homepage'),
                is_visible: features.data.homepage_slider_is_visible,
                duration: features.data.homepage_slider_duration * 1000,
                loop_at_beginning: features.data.homepage_slider_loop_at_beginning,
                new_slider: features.data.homepage_slider_is_new,
                height: features.data.homepage_slider_size,
                images: []
            };

            var tmp_images = features.data.homepage_slider_images;

            /** Prepend IMAGE_URL for images */
            for(var i=0; i < tmp_images.length; i++) {
                homepage_slider.images[i] = IMAGE_URL + tmp_images[i];
            }

            $scope.showSlider = function() {
                return (homepage_slider.is_active_for_layout && homepage_slider.is_visible && homepage_slider.images);
            };

            $scope.homepage_slider = homepage_slider;

            $scope.features = features;

            $scope.tabbar_is_transparent = HomepageLayout.properties.tabbar_is_transparent;

            /** Load first feature is needed */
            if (!REDIRECT_URI && !Application.is_customizing_colors && HomepageLayout.properties.options.autoSelectFirst && features.first_option !== false) {
                $ionicHistory.nextViewOptions({
                    historyRoot: true,
                    disableAnimate: false
                });
                var feat_index = 0;
                for(var fi = 0; fi < features.options.length; fi++) {
                    var feat = features.options[fi];
                    /** Don't load unwanted features on first page. */
                    if((feat.code !== "code_scan") && (feat.code !== "radio") && (feat.code !== "padlock")) {
                        feat_index = fi;
                        break;
                    }
                }

                $location.path(features.options[feat_index].path).replace();
            }

            /** Redirect where needed if required (paypal/stripe/etc...) ! */
            if(REDIRECT_URI) {
                var redirect_path = "/"+APP_KEY+REDIRECT_URI;
                REDIRECT_URI = false;
                redirect_path = redirect_path.replace(/(\/)+/, "/");
                $location.path(redirect_path);
            }

            $scope.menu_is_visible = true;

            $scope.is_loading = false;

            /** When done, call layout hooks */
            $timeout(function() {
                HomepageLayout.callHooks();
            }, 250);


        });

    };

    $scope.loadContent();

});
