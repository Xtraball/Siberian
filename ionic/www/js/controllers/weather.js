/*global
 App, angular, moment, BASE_PATH, BASE_URL
 */

angular.module("starter").controller("WeatherController", function (Modal, $scope, $stateParams, $window, $q, Country,
                                                                    RainEffect, Loader, Dialog, LinkService, Location, Weather) {

    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        error: false,
        woeid: null,
        show_weather_details: null,
        show_change_location: null,
        new_location: {
            country: "",
            city: "",
        },
        card_design: false
    });

    Weather.setValueId($stateParams.value_id);

    $scope.loadContent = function () {
        $scope.is_loading = true;

        Country
            .findAll()
            .then(function (data) {
                $scope.country_list = data;
            });

        /** This one too hop hop hop embed ! */
        Weather
            .find()
            .then(function (data) {
                $scope.page_title = data.page_title;
                $scope.unit = data.collection.unit;
                $scope.icon_url = data.icon_url;
                $scope.icon_error_url = BASE_URL + "/template/block/colorize/color/" + $window.colors.list_item.color.replace("#", "") + "/path/" + btoa($scope.icon_url + "weather_3200.png");
                $scope.icon_wind = BASE_URL + "/template/block/colorize/color/" + $window.colors.list_item.color.replace("#", "") + "/path/" + btoa($scope.icon_url + "wind.png");
                $scope.icon_atmosphere = BASE_URL + "/template/block/colorize/color/" + $window.colors.list_item.color.replace("#", "") + "/path/" + btoa($scope.icon_url + "atmosphere.png");
                $scope.icon_astronomy = BASE_URL + "/template/block/colorize/color/" + $window.colors.list_item.color.replace("#", "") + "/path/" + btoa($scope.icon_url + "astronomy.png");

                if (data.collection.woeid) {
                    $scope.woeid = data.collection.woeid;
                    $scope.getWeather();
                }
            });
    };

    $scope.getWeather = function () {
        $scope.is_loading = true;
        Weather
        .getWeather($scope.woeid, $scope.unit)
        .then(function (data) {
            $scope.weather = data.query.results.channel;
            $scope.weather_date = moment(data.query.created).calendar();
            $scope.current_icon_url = BASE_URL + "/template/block/colorize/color/" + $window.colors.list_item.color.replace("#", "") + "/path/" + btoa($scope.icon_url + "weather_" + $scope.weather.item.condition.code + ".png");
            $scope.forecast_1_icon_url = BASE_URL + "/template/block/colorize/color/" + $window.colors.list_item.color.replace("#", "") + "/path/" + btoa($scope.icon_url + "weather_" + $scope.weather.item.forecast[1].code + ".png");
            $scope.forecast_2_icon_url = BASE_URL + "/template/block/colorize/color/" + $window.colors.list_item.color.replace("#", "") + "/path/" + btoa($scope.icon_url + "weather_" + $scope.weather.item.forecast[2].code + ".png");
            $scope.forecast_3_icon_url = BASE_URL + "/template/block/colorize/color/" + $window.colors.list_item.color.replace("#", "") + "/path/" + btoa($scope.icon_url + "weather_" + $scope.weather.item.forecast[3].code + ".png");
            $scope.forecast_4_icon_url = BASE_URL + "/template/block/colorize/color/" + $window.colors.list_item.color.replace("#", "") + "/path/" + btoa($scope.icon_url + "weather_" + $scope.weather.item.forecast[4].code + ".png");
            $scope.is_loading = false;

            //$scope.runRain();
        }, function (message) {
            $scope.error = true;
            $scope.error_message = message;
            $scope.is_loading = false;
        });
    };

    /** Wheather details ok */
    $scope.openDetails = function () {

        Modal
        .fromTemplateUrl("weather-details.html", {
            scope: $scope
        }).then(function (modal) {
            $scope.details_modal = modal;
            $scope.details_modal.show();
        });
    };

    $scope.closeDetails = function () {
        $scope.details_modal.remove();
    };

    $scope.openChangeLocationForm = function () {
        Modal
        .fromTemplateUrl("weather-change-location-form.html", {
            scope: $scope
        }).then(function (modal) {
            $scope.location_form_modal = modal;
            $scope.location_form_modal.show();
        });
    };

    $scope.closeChangeLocationForm = function () {
        $scope.location_form_modal.remove();
    };

    $scope.changeLocation = function (useGps) {
        Loader.show();

        var deferred = $q.defer();
        var _location = null;
        if (useGps) {
            Location
                .getLocation()
                .then(function (position) {
                    _location = "(" + position.coords.latitude + "," + position.coords.longitude + ")";
                    Loader.hide();
                    deferred.resolve();
                }, function (error) {
                    Loader.hide();
                    Dialog.alert("Error", "We were unable to fetch your current location", "Dismiss");
                    deferred.reject();
                });
        } else {
            if ($scope.new_location.city.trim() !== "") {
                _location = $scope.new_location.city.trim();

                if ($scope.new_location.country.trim() !== "") {
                    _location += "," + $scope.new_location.country.trim();
                }
                Loader.hide();
                deferred.resolve();
            } else {
                Loader.hide();
                Dialog.alert("Error", "We were unable to find this location", "Dismiss");
                deferred.reject();
            }
        }

        deferred.promise
            .then(function () {
                Weather
                .getWoeid(_location)
                .then(function (data) {
                    var woeid = null;
                    if (data["query"]["count"] > 0) {
                        if (data["query"]["results"]["place"].length > 1) {
                            woeid = data["query"]["results"]["place"][0]["woeid"];
                        } else {
                            woeid = data["query"]["results"]["place"]["woeid"];
                        }
                    }

                    if (woeid) {
                        $scope.woeid = woeid;
                        $scope.getWeather();
                        $scope.closeChangeLocationForm();
                    } else {
                        Dialog.alert("Error", "We were unable to find this location", "Dismiss");
                    }
                });
            });
    };

    $scope.openYahooWebsite = function () {
        LinkService.openLink("https://www.yahoo.com/?ilc=401");
    };

    $scope.runRain = function () {
        RainEffect.run();
    };

    $scope.currentEffect = 'rain';

    var yahooCodes = {
        0: '', //tornado
        1: '', //tropical storm
        2: '', //hurricane
        3: '', //severe thunderstorms
        4: '', //thunderstorms
        5: 'rain', //mixed rain and snow
        6: 'rain', //mixed rain and sleet
        7: '', //mixed snow and sleet
        8: '', //freezing drizzle
        9: 'drizzle', //drizzle
        10: 'rain', //freezing rain
        11: '', //showers
        12: '', //showers
        13: '', //snow flurries
        14: '', //light snow showers
        15: '', //blowing snow
        16: '', //snow
        17: '', //hail
        18: '', //sleet
        19: '', //dust
        20: '', //foggy
        21: '', //haze
        22: '', //smoky
        23: '', //blustery
        24: '', //windy
        25: '', //cold
        26: 'drizzle', //cloudy
        27: 'drizzle', //mostly cloudy (night)
        28: 'drizzle', //mostly cloudy (day)
        29: 'drizzle', //partly cloudy (night)
        30: 'drizzle', //partly cloudy (day)
        31: '', //clear (night)
        32: 'sunny', //sunny
        33: '', //fair (night)
        34: '', //fair (day)
        35: '', //mixed rain and hail
        36: '', //hot
        37: '', //isolated thunderstorms
        38: '', //scattered thunderstorms
        39: '', //scattered thunderstorms
        40: '', //scattered showers
        41: '', //heavy snow
        42: '', //scattered snow showers
        43: '', //heavy snow
        44: '', //partly cloudy
        45: '', //thundershowers
        46: '', //snow showers
        47: '', //isolated thundershowers
        3200: '', //not available
    };

    var currentIndex = 0;
    var effects = [
        'rain',
        'storm',
        //'fallout',
        'drizzle',
        'sunny'
    ];

    setInterval(function () {
        var eff = effects[currentIndex];
        $scope.currentEffect = eff;
        currentIndex = currentIndex + 1;
        if (currentIndex > 4) {
            currentIndex = 0;
        }

        var event = new CustomEvent('weatherChange', {
            detail: {
                type: eff
            }
        });
        window.dispatchEvent(event);
    }, 3000);

    $scope.loadContent();

});