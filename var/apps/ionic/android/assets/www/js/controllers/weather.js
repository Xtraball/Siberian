App.config(function($stateProvider) {

    $stateProvider.state('weather-view', {
        url: BASE_PATH+"/weather/mobile_view/index/value_id/:value_id",
        controller: 'WeatherController',
        templateUrl: "templates/weather/l1/view.html"
    });

}).controller('WeatherController', function($ionicModal, $q, $rootScope, $scope, $stateParams, $window, Country, Weather/*, Connection*/) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.value_id = Weather.value_id = $stateParams.value_id;
    $scope.is_loading = true;
    $scope.error = false;
    $scope.woeid = null;
    $scope.show_weather_details = false;
    $scope.show_change_location = false;
    $scope.new_location = {};

    $scope.loadContent = function() {

        Country.findAll().success(function(data) {
            $scope.country_list = data;
        });

        Weather.find().success(function(data) {
            $scope.page_title = data.page_title;
            $scope.unit = data.collection.unit;
            $scope.icon_url = data.icon_url;
            $scope.icon_error_url = $scope.current_icon_url = BASE_URL+"/template/block/colorize/color/" + $window.colors.list_item.color.replace("#","") + "/path/" + btoa($scope.icon_url + "weather_3200.png");
            $scope.icon_wind = $scope.current_icon_url = BASE_URL+"/template/block/colorize/color/" + $window.colors.list_item.color.replace("#","") + "/path/" + btoa($scope.icon_url + "wind.png");
            $scope.icon_atmosphere = $scope.current_icon_url = BASE_URL+"/template/block/colorize/color/" + $window.colors.list_item.color.replace("#","") + "/path/" + btoa($scope.icon_url + "atmosphere.png");
            $scope.icon_astronomy = $scope.current_icon_url = BASE_URL+"/template/block/colorize/color/" + $window.colors.list_item.color.replace("#","") + "/path/" + btoa($scope.icon_url + "astronomy.png");

            if(data.collection.woeid) {
                $scope.woeid = data.collection.woeid;
                $scope.getWeather();
            }

            //if(!Connection.isOnline) {
            //    $scope.error = true;
                $scope.is_loading = false;
            //} else {
            //    $scope.getWeather();
            //}

        });
    };

    $scope.getWeather = function() {
        Weather.getWeather($scope.woeid, $scope.unit).then(function(data) {
            $scope.weather = data.query.results.channel;
            $scope.current_icon_url = BASE_URL+"/template/block/colorize/color/" + $window.colors.list_item.color.replace("#","") + "/path/" + btoa($scope.icon_url + "weather_" + $scope.weather.item.condition.code + ".png");
            $scope.forecast_1_icon_url = BASE_URL+"/template/block/colorize/color/" + $window.colors.list_item.color.replace("#","") + "/path/" + btoa($scope.icon_url + "weather_" + $scope.weather.item.forecast[1].code + ".png");
            $scope.forecast_2_icon_url = BASE_URL+"/template/block/colorize/color/" + $window.colors.list_item.color.replace("#","") + "/path/" + btoa($scope.icon_url + "weather_" + $scope.weather.item.forecast[2].code + ".png");
            $scope.forecast_3_icon_url = BASE_URL+"/template/block/colorize/color/" + $window.colors.list_item.color.replace("#","") + "/path/" + btoa($scope.icon_url + "weather_" + $scope.weather.item.forecast[3].code + ".png");
            $scope.forecast_4_icon_url = BASE_URL+"/template/block/colorize/color/" + $window.colors.list_item.color.replace("#","") + "/path/" + btoa($scope.icon_url + "weather_" + $scope.weather.item.forecast[4].code + ".png");
            $scope.is_loading = false;
        }, function(message) {
            $scope.error = true;
            $scope.error_message = message;
            $scope.is_loading = false;
        });
    };

    $scope.openDetails = function() {

        $ionicModal.fromTemplateUrl("weather-details.html", {
            scope: $scope,
            animation: "slide-in-up"
        }).then(function(modal) {
            $scope.details_modal = modal;
            $scope.details_modal.show();
        });
    };
    $scope.closeDetails = function() {
        $scope.details_modal.remove();
    };

    $scope.openChangeLocationForm = function() {
        $ionicModal.fromTemplateUrl("weather-change-location-form.html", {
            scope: $scope,
            animation: "slide-in-up"
        }).then(function(modal) {
            $scope.location_form_modal = modal;
            $scope.location_form_modal.show();
        });
    };
    $scope.closeChangeLocationForm = function() {
        $scope.location_form_modal.remove();
    };

    $scope.changeLocation = function() {

        $scope.is_loading = true;

        if($scope.new_location.country && !$scope.new_location.use_user_location) {
            var param = "";
            if($scope.new_location.city) {
                param = $scope.new_location.city + "," + $scope.new_location.country;
            } else {
                param = $scope.new_location.country;
            }

            Weather.getWoeid(param).success(function(data) {
                var woeid = null;
                if(data["query"]["count"]> 0) {
                    if(data["query"]["results"]["place"].length > 1) {
                        woeid = data["query"]["results"]["place"][0]["woeid"];
                    } else {
                        woeid = data["query"]["results"]["place"]["woeid"];
                    }
                }

                if(woeid) {
                    $scope.woeid = woeid;
                    $scope.getWeather();
                } else {
                    $scope.error = true;
                    $scope.error_message = "Unable to get woeid.";
                }
            });
        } else {
            $scope.woeid = null;
            $scope.getWeather();
        }

        $scope.closeChangeLocationForm();

    };

    $scope.openYahooWebsite = function() {
        $window.open("https://www.yahoo.com/?ilc=401", $rootScope.getTargetForLink(), "location=no");
    };

    $scope.loadContent();

});