/*global
 App, angular, moment, BASE_PATH, BASE_URL
 */

angular.module("starter").controller("WeatherController", function (Modal, $scope, $stateParams, $window, $q, Country,
                                                                    RainEffect, Loader, Dialog, LinkService, Location, Weather) {

    angular.extend($scope, {
        isLoading: true,
        value_id: $stateParams.value_id,
        error: false,
        errorMessage: "",
        newLocation: {
            unit: "F",
            units: "imperial",
            country: "",
            city: "",
        },
        iconBase: "https://openweathermap.org/img/w/%ICON%.png",
        weatherData: null,
        forecastData: null,
        weatherDate: null,
        card_design: false
    });

    Weather.setValueId($stateParams.value_id);

    $scope.loadContent = function () {
        $scope.isLoading = true;

        Country
            .findAll()
            .then(function (data) {
                $scope.countryList = data;
            });

        Weather
            .find()
            .then(function (data) {
                $scope.page_title = data.page_title;
                $scope.newLocation.unit = data.unit;
                $scope.newLocation.units = data.units;
                $scope.newLocation.country = data.country;
                $scope.newLocation.city = data.city;

                if ($scope.newLocation.country !== "" && $scope.newLocation.city !== "") {
                    var q = $scope.newLocation.city + "," +$scope.newLocation.country;
                    $scope.getWeather({q: q});
                }
            });
    };

    $scope.getIconUrl = function (code) {
        return $scope.iconBase.replace("%ICON%", code);
    };

    $scope.getSuntime = function (timestamp) {
        return moment(timestamp).format("LT");
    };

    $scope.roundDegrees = function (degrees) {
        return Math.round(degrees);
    };

    $scope.dayForDate = function (date) {
        var date = moment(date).format("ddd");
        date[0] = date[0].toUpperCase();
        return date;
    };

    $scope.d2d = function (d) {
        if (typeof d !== 'number' || isNaN(d)) {
            return -1;
        }

        // keep within the range: 0 <= d < 360
        d = d % 360;

        if (11.25 <= d && d < 33.75) {
            return "NNE";
        } else if (33.75 <= d && d < 56.25) {
            return "NE";
        } else if (56.25 <= d && d < 78.75) {
            return "ENE";
        } else if (78.75 <= d && d < 101.25) {
            return "E";
        } else if (101.25 <= d && d < 123.75) {
            return "ESE";
        } else if (123.75 <= d && d < 146.25) {
            return "SE";
        } else if (146.25 <= d && d < 168.75) {
            return "SSE";
        } else if (168.75 <= d && d < 191.25) {
            return "S";
        } else if (191.25 <= d && d < 213.75) {
            return "SSW";
        } else if (213.75 <= d && d < 236.25) {
            return "SW";
        } else if (236.25 <= d && d < 258.75) {
            return "WSW";
        } else if (258.75 <= d && d < 281.25) {
            return "W";
        } else if (281.25 <= d && d < 303.75) {
            return "WNW";
        } else if (303.75 <= d && d < 326.25) {
            return "NW";
        } else if (326.25 <= d && d < 348.75) {
            return "NNW";
        } else {
            return "N";
        }
    };

    $scope.getWeather = function (query, closeModal) {
        $scope.isLoading = true;

        Weather
        .getWeather(angular.extend({
            units: $scope.newLocation.units
        }, query))
        .then(function (data) {

            $scope.weatherData = data.weather;
            $scope.forecastData = data.forecast;
            $scope.weatherDate = moment($scope.weatherData.dt * 1000).calendar();

            $scope.error = false;
            $scope.errorMessage = "";

            $scope.isLoading = false;

        }, function (message) {
            $scope.error = true;
            $scope.errorMessage = message;

            $scope.isLoading = false;
        }).then(function () {
            if (closeModal === true) {
                $scope.closeChangeLocationForm();
            }
        });

    };

    /** Wheather details ok */
    $scope.openDetails = function () {

        Modal
        .fromTemplateUrl("weather-details.html", {
            scope: $scope
        }).then(function (modal) {
            $scope.detailsModal = modal;
            $scope.detailsModal.show();
        });
    };

    $scope.closeDetails = function () {
        $scope.detailsModal.remove();
    };

    $scope.openChangeLocationForm = function () {
        Modal
        .fromTemplateUrl("weather-change-location-form.html", {
            scope: $scope
        }).then(function (modal) {
            $scope.locationFormModal = modal;
            $scope.locationFormModal.show();
        });
    };

    $scope.closeChangeLocationForm = function () {
        $scope.locationFormModal.remove();
    };

    $scope.changeLocation = function (useGps) {
        Loader.show();

        var deferred = $q.defer();
        var q = null;
        if (useGps) {
            Location
                .getLocation()
                .then(function (position) {
                    q = {
                        lat: position.coords.latitude,
                        lon: position.coords.longitude
                    };
                    Loader.hide();
                    deferred.resolve();
                }, function (error) {
                    Loader.hide();
                    Dialog.alert("Error", "We were unable to fetch your current location", "Dismiss");
                    deferred.reject();
                });
        } else {
            if ($scope.newLocation.city.trim() !== "") {
                //var reg = new RegExp("[a-z]{4,}", "i");
                /**if (!reg.test($scope.newLocation.city)) {
                    q = {
                        zip: $scope.newLocation.city.trim()
                    };
                    if ($scope.newLocation.country.trim() !== "") {
                        q.zip += "," + $scope.newLocation.country.trim();
                    }
                } else {*/
                    q = {
                        q: $scope.newLocation.city.trim()
                    };
                    if ($scope.newLocation.country.trim() !== "") {
                        q.q += "," + $scope.newLocation.country.trim();
                    }
                //}

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
                $scope.getWeather(q, true);
            });
    };

    $scope.runRain = function () {
        RainEffect.run();
    };

    $scope.loadContent();

});