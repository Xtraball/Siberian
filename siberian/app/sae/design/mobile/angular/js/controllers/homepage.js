App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL, {
        controller: 'HomeController',
        templateUrl: BASE_URL+"/front/mobile_home/view"
    }).otherwise({
        controller: 'HomeController',
        templateUrl: BASE_URL+"/front/mobile_home/view"
    })
    
}).controller('HomeController', function ($window, $rootScope, $scope, $timeout, $location, Url, Application, Pages, Customer, LayoutService, Padlock) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.show_homepage_slider = false;
    $scope.slider_images = {};

    $scope.close_on_click = Application.is_previewer;
    $scope.menu_is_visible = false;

    $scope.loadContent = function () {

        $window.stored_data = JSON.stringify(["uc"]);
        Application.getStoredData({data: ["uc"]}, function(json) {

            var data = {};
            try {
                data = JSON.parse(json);
            } catch(e) {
                data = {};
            }

            if(data.uc) {
                Padlock.unlock_by_qrcode = true;
            }

        }, function() {});

        LayoutService.getFeatures().then(function (features) {

            $scope.homepage_slider_is_visible = features.data.homepage_slider_is_visible;
            $scope.homepage_slider_duration = features.data.homepage_slider_duration;
            $scope.homepage_slider_loop_at_beginning = features.data.homepage_slider_loop_at_beginning;
            $scope.homepage_slider_images = features.data.homepage_slider_images;

            $scope.features = features;
            $scope.tabbar_is_transparent = LayoutService.properties.tabbar_is_transparent;

            //if (LayoutService.properties.options.autoSelectFirst) {
            //    $scope.redirectToFirstOption();
            //} else {
            $scope.menu_is_visible = true;
            //}

        });

    };

    $scope.redirectToFirstOption = function () {

        console.log("Redirecting to the first option -- Shouldn't pass here ");
        return;

        var options = $scope.features.options;
        var currentOption = LayoutService.properties.options.current;

        if (currentOption === null && LayoutService.properties.options.autoSelectFirst) {
            if (options && options.length !== 0) {

                currentOption = options[0];
                if (currentOption.url.indexOf(APP_URL) === 0) {
                    // internal link
                    $location.path(Url.get(currentOption.url.substr(APP_URL.length + 1))).replace();
                } else {
                    // external link
                    window.location.replace(currentOption.url);
                }
            }
        }
    };

    $scope.reload = function () {
        $scope.tabbar_is_visible = false;
        Pages.is_loaded = false;
    };

    $scope.loadContent();
});