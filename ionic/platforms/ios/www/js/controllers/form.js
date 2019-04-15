/*global
 App, angular, isOverview, BASE_PATH, IS_NATIVE_APP, Camera
 */

angular.module("starter").controller("FormViewController", function (Location, $filter, $timeout, $scope, $stateParams, $translate, Dialog,
                                                                     Form, GoogleMaps, Picture) {

    angular.extend($scope, {
        is_loading: true,
        locationIsLoading: false,
        value_id: $stateParams.value_id,
        dummy: {},
        formData: {},
        preview_src: {},
        geolocation: {},
        sections: [],
        can_take_pictures: IS_NATIVE_APP,
        card_design: false
    });

    Form.setValueId($stateParams.value_id);

    $scope.loadContent = function () {
        Form
        .findAll()
        .then(function (data) {
            $scope.sections = data.sections;
            $scope.page_title = data.page_title;
            $scope.dateFormat = data.dateFormat;
            $scope.design = data.design;
            $scope.card_design = (data.design === "card");
        }).then(function () {
            $scope.is_loading = false;
        });
    };

    $scope.getLocation = function (field) {
        $scope.locationIsLoading = true;
        if ($scope.geolocation[field.id]) {
            Location
            .getLocation()
            .then(function (position) {
                var lat = Number.parseFloat(position.coords.latitude).toFixed(5);
                var lng = Number.parseFloat(position.coords.longitude).toFixed(5);

                $scope.formData[field.id] = {
                    address: null,
                    coords: {
                        lat: lat,
                        lng: lng
                    }
                };

                GoogleMaps
                .reverseGeocode(position.coords)
                .then(function (results) {
                    if (results[0]) {
                        $scope.formData[field.id].address = results[0].formatted_address;
                    }
                });

            }, function (e) {
                $scope.formData[field.id] = null;
                $scope.geolocation[field.id] = false;
            }).then(function () {
                $scope.locationIsLoading = false;
            });
        } else {
            $scope.formData[field.id] = null;
            $scope.locationIsLoading = false;
        }
    };

    $scope.formatLocation = function (field) {
        var html;
        if (field.address) {
            html = field.address + "<br />" + field.coords.lat + ", " + field.coords.lng;
        } else {
            html = field.coords.lat + ", " + field.coords.lng;
        }

        return $filter("trusted_html")(html);
    };

    /**
     * @param field
     */
    $scope.takePicture = function (field) {
        Picture
        .takePicture()
        .then(function (success) {
            $scope.preview_src[field.id] = success.image;
            $scope.formData[field.id] = success.image;
        });
    };

    $scope.post = function () {
        $scope.is_loading = true;
        if (_.isEmpty($scope.formData)) {
            Dialog
            .alert("Error", "You must fill at least one field!", "OK", -1)
            .then(function () {
                $scope.is_loading = false;
            });
            return;
        }

        Form
        .post($scope.formData)
        .then(function (data) {
            $scope.formData = {};
            $scope.preview_src = {};
            $scope.geolocation = {};

            Dialog.alert("Success", data.message, "OK", 3200);
        }, function (data) {
            Dialog.alert("Error", data.message, "OK", -1);
        }).then(function () {
            $scope.is_loading = false;
        });
    };

    $scope.loadContent();
});
