/*global
 App, angular, moment, BASE_PATH, BASE_URL
 */

angular.module("starter").controller("WeatherController", function(Modal, $scope, $stateParams, $window, Country,
                                                                   LinkService, Weather) {

    angular.extend($scope, {
        is_loading              : true,
        value_id                : $stateParams.value_id,
        error                   : false,
        woeid                   : null,
        show_weather_details    : null,
        show_change_location    : null,
        new_location            : {},
        card_design             : false
    });

    Weather.setValueId($stateParams.value_id);

    $scope.loadContent = function() {

        $scope.is_loading = true;

        /** Seriously ? one call for this :'( */
        Country.findAll()
            .then(function(data) {
                $scope.country_list = data;
            });

        /** This one too hop hop hop embed ! */
        Weather.find()
            .then(function(data) {
                $scope.page_title           = data.page_title;
                $scope.unit                 = data.collection.unit;
                $scope.icon_url             = data.icon_url;
                $scope.icon_error_url       = BASE_URL + "/template/block/colorize/color/" + $window.colors.list_item.color.replace("#","") + "/path/" + btoa($scope.icon_url + "weather_3200.png");
                $scope.icon_wind            = BASE_URL + "/template/block/colorize/color/" + $window.colors.list_item.color.replace("#","") + "/path/" + btoa($scope.icon_url + "wind.png");
                $scope.icon_atmosphere      = BASE_URL + "/template/block/colorize/color/" + $window.colors.list_item.color.replace("#","") + "/path/" + btoa($scope.icon_url + "atmosphere.png");
                $scope.icon_astronomy       = BASE_URL + "/template/block/colorize/color/" + $window.colors.list_item.color.replace("#","") + "/path/" + btoa($scope.icon_url + "astronomy.png");

                if(data.collection.woeid) {
                    $scope.woeid = data.collection.woeid;
                    $scope.getWeather();
                }

                $scope.is_loading = false;

            });
    };

    $scope.getWeather = function() {
        Weather.getWeather($scope.woeid, $scope.unit)
            .then(function(data) {

                $scope.weather = data.query.results.channel;
                $scope.weather_date = moment(data.query.created).calendar();
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

    /** Wheather details ok */
    $scope.openDetails = function() {

        Modal
            .fromTemplateUrl("weather-details.html", {
                scope: $scope
            }).then(function(modal) {
                $scope.details_modal = modal;
                $scope.details_modal.show();
            });
    };

    $scope.closeDetails = function() {
        $scope.details_modal.remove();
    };

    $scope.openChangeLocationForm = function() {
        Modal
            .fromTemplateUrl("weather-change-location-form.html", {
                scope: $scope
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

            Weather.getWoeid(param)
                .then(function(data) {
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
        LinkService.openLink("https://www.yahoo.com/?ilc=401");
    };

    $scope.loadContent();

});