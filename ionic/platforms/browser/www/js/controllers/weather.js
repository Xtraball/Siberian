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
        forecastBuild: null,
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
                    var q = $scope.newLocation.city + "," + $scope.newLocation.country;
                    $scope.getWeather({q: q});
                }
            });
    };

    $scope.buildForecast = function () {
        var list = $scope.forecastData.list;
        var days = {
            0: {
                min: null,
                max: null
            },
            1: {
                min: null,
                max: null
            },
            2: {
                min: null,
                max: null
            },
            3: {
                min: null,
                max: null
            },
            4: {
                min: null,
                max: null
            },
            5: {
                min: null,
                max: null
            }
        };

        var previous = moment($scope.weatherData.dt * 1000);
        var currentDay = 0;
        list.forEach(function (segment) {
            var dateSegment = segment.dt * 1000;
            if (!previous.isSame(dateSegment, "day")) {
                currentDay = currentDay + 1;
                days[currentDay].day = $scope.dayForDate(dateSegment);
                days[currentDay].weather = segment.weather[0];
                days[currentDay].min = segment.main.temp_min.toFixed(0);
                days[currentDay].max = segment.main.temp_max.toFixed(0);

                previous = moment(dateSegment);
            } else {
                if (segment.main.temp_min < days[currentDay].min) {
                    days[currentDay].min = segment.main.temp_min.toFixed(0);
                }
                if (segment.main.temp_max > days[currentDay].max) {
                    days[currentDay].max = segment.main.temp_max.toFixed(0);
                }
            }
        });

        $scope.forecastBuild = days;
    };

    $scope.getIcon = function (id) {
        var icon = null;
        try {
            icon = $scope.iconMap[id].icon;
        } catch (e) {}

        // If we are not in the ranges mentioned above, add a day/night prefix.
        if (!(id > 699 && id < 800) && !(id > 899 && id < 1000)) {
            icon = "day-" + icon;
        }

        return "icon ion-wi-" + icon;
    };

    $scope.speedKmh = function (speedMs) {
        return Math.round(speedMs * 3.6);
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

    $scope.iconMap = {
        "200": {
            "label": "thunderstorm with light rain",
            "icon": "storm-showers"
        },
        "201": {
            "label": "thunderstorm with rain",
            "icon": "storm-showers"
        },
        "202": {
            "label": "thunderstorm with heavy rain",
            "icon": "storm-showers"
        },
        "210": {
            "label": "light thunderstorm",
            "icon": "storm-showers"
        },
        "211": {
            "label": "thunderstorm",
            "icon": "thunderstorm"
        },
        "212": {
            "label": "heavy thunderstorm",
            "icon": "thunderstorm"
        },
        "221": {
            "label": "ragged thunderstorm",
            "icon": "thunderstorm"
        },
        "230": {
            "label": "thunderstorm with light drizzle",
            "icon": "storm-showers"
        },
        "231": {
            "label": "thunderstorm with drizzle",
            "icon": "storm-showers"
        },
        "232": {
            "label": "thunderstorm with heavy drizzle",
            "icon": "storm-showers"
        },
        "300": {
            "label": "light intensity drizzle",
            "icon": "sprinkle"
        },
        "301": {
            "label": "drizzle",
            "icon": "sprinkle"
        },
        "302": {
            "label": "heavy intensity drizzle",
            "icon": "sprinkle"
        },
        "310": {
            "label": "light intensity drizzle rain",
            "icon": "sprinkle"
        },
        "311": {
            "label": "drizzle rain",
            "icon": "sprinkle"
        },
        "312": {
            "label": "heavy intensity drizzle rain",
            "icon": "sprinkle"
        },
        "313": {
            "label": "shower rain and drizzle",
            "icon": "sprinkle"
        },
        "314": {
            "label": "heavy shower rain and drizzle",
            "icon": "sprinkle"
        },
        "321": {
            "label": "shower drizzle",
            "icon": "sprinkle"
        },
        "500": {
            "label": "light rain",
            "icon": "rain"
        },
        "501": {
            "label": "moderate rain",
            "icon": "rain"
        },
        "502": {
            "label": "heavy intensity rain",
            "icon": "rain"
        },
        "503": {
            "label": "very heavy rain",
            "icon": "rain"
        },
        "504": {
            "label": "extreme rain",
            "icon": "rain"
        },
        "511": {
            "label": "freezing rain",
            "icon": "rain-mix"
        },
        "520": {
            "label": "light intensity shower rain",
            "icon": "showers"
        },
        "521": {
            "label": "shower rain",
            "icon": "showers"
        },
        "522": {
            "label": "heavy intensity shower rain",
            "icon": "showers"
        },
        "531": {
            "label": "ragged shower rain",
            "icon": "showers"
        },
        "600": {
            "label": "light snow",
            "icon": "snow"
        },
        "601": {
            "label": "snow",
            "icon": "snow"
        },
        "602": {
            "label": "heavy snow",
            "icon": "snow"
        },
        "611": {
            "label": "sleet",
            "icon": "sleet"
        },
        "612": {
            "label": "shower sleet",
            "icon": "sleet"
        },
        "615": {
            "label": "light rain and snow",
            "icon": "rain-mix"
        },
        "616": {
            "label": "rain and snow",
            "icon": "rain-mix"
        },
        "620": {
            "label": "light shower snow",
            "icon": "rain-mix"
        },
        "621": {
            "label": "shower snow",
            "icon": "rain-mix"
        },
        "622": {
            "label": "heavy shower snow",
            "icon": "rain-mix"
        },
        "701": {
            "label": "mist",
            "icon": "sprinkle"
        },
        "711": {
            "label": "smoke",
            "icon": "smoke"
        },
        "721": {
            "label": "haze",
            "icon": "day-haze"
        },
        "731": {
            "label": "sand, dust whirls",
            "icon": "cloudy-gusts"
        },
        "741": {
            "label": "fog",
            "icon": "fog"
        },
        "751": {
            "label": "sand",
            "icon": "cloudy-gusts"
        },
        "761": {
            "label": "dust",
            "icon": "dust"
        },
        "762": {
            "label": "volcanic ash",
            "icon": "smog"
        },
        "771": {
            "label": "squalls",
            "icon": "day-windy"
        },
        "781": {
            "label": "tornado",
            "icon": "tornado"
        },
        "800": {
            "label": "clear sky",
            "icon": "sunny"
        },
        "801": {
            "label": "few clouds",
            "icon": "cloudy"
        },
        "802": {
            "label": "scattered clouds",
            "icon": "cloudy"
        },
        "803": {
            "label": "broken clouds",
            "icon": "cloudy"
        },
        "804": {
            "label": "overcast clouds",
            "icon": "cloudy"
        },
        "900": {
            "label": "tornado",
            "icon": "tornado"
        },
        "901": {
            "label": "tropical storm",
            "icon": "hurricane"
        },
        "902": {
            "label": "hurricane",
            "icon": "hurricane"
        },
        "903": {
            "label": "cold",
            "icon": "snowflake-cold"
        },
        "904": {
            "label": "hot",
            "icon": "hot"
        },
        "905": {
            "label": "windy",
            "icon": "windy"
        },
        "906": {
            "label": "hail",
            "icon": "hail"
        },
        "951": {
            "label": "calm",
            "icon": "sunny"
        },
        "952": {
            "label": "light breeze",
            "icon": "cloudy-gusts"
        },
        "953": {
            "label": "gentle breeze",
            "icon": "cloudy-gusts"
        },
        "954": {
            "label": "moderate breeze",
            "icon": "cloudy-gusts"
        },
        "955": {
            "label": "fresh breeze",
            "icon": "cloudy-gusts"
        },
        "956": {
            "label": "strong breeze",
            "icon": "cloudy-gusts"
        },
        "957": {
            "label": "high wind, near gale",
            "icon": "cloudy-gusts"
        },
        "958": {
            "label": "gale",
            "icon": "cloudy-gusts"
        },
        "959": {
            "label": "severe gale",
            "icon": "cloudy-gusts"
        },
        "960": {
            "label": "storm",
            "icon": "thunderstorm"
        },
        "961": {
            "label": "violent storm",
            "icon": "thunderstorm"
        },
        "962": {
            "label": "hurricane",
            "icon": "cloudy-gusts"
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

                $scope.buildForecast();

                $scope.error = false;
                $scope.errorMessage = "";

                $scope.isLoading = false;

            }, function (message) {
                $scope.error = true;
                try {
                    $scope.errorMessage = JSON.parse(message).message;
                } catch (e) {
                    $scope.errorMessage = null;
                }

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
                /** ZipCode search on OWM is bugged for now ... 25/01/2019 */
                /**var reg = new RegExp("[a-z]{4,}", "i");
                 if (!reg.test($scope.newLocation.city)) {
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
