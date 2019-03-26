/*global
 App, angular, isOverview, BASE_PATH, IS_NATIVE_APP, Camera
 */

angular.module("starter").controller("FormViewController", function (Location, $timeout, $scope, $stateParams, $translate, Dialog,
                                                                     Form, GoogleMaps, Picture) {

    angular.extend($scope, {
        is_loading: true,
        locationIsLoading: false,
        value_id: $stateParams.value_id,
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
                GoogleMaps
                .reverseGeocode(position.coords)
                .then(function (results) {
                    if (results[0]) {
                        $scope.formData[field.id] = results[0].formatted_address + "<br />" + position.coords.latitude + ", " + position.coords.longitude + "";
                    } else {
                        $scope.formData[field.id] = position.coords.latitude + ", " + position.coords.longitude;
                    }
                    $scope.fieldChanged(field);
                }, function (data) {
                    $scope.formData[field.id] = position.coords.latitude + ", " + position.coords.longitude;
                    $scope.fieldChanged(field);
                });
            }, function (e) {
                $scope.formData[field.id] = null;
                $scope.geolocation[field.id] = false;
                $scope.fieldChanged(field);
            }).then(function () {
                $scope.locationIsLoading = false;
            });
        } else {
            $scope.formData[field.id] = null;
            $scope.locationIsLoading = false;
            $scope.fieldChanged(field);
        }
    };

    $scope.fieldChanged = function (field) {
        field.isFilled = false;
        if (!_.isEmpty($scope.formData[field.id])) {
            field.isFilled = true;
        }
    };

    $scope.requiredFieldIsEmpty = function () {
        console.log($scope.sections);
        var emptyRequired = false;
        $scope.sections.forEach(function (section) {
            section.fields.forEach(function (field) {
                if (field.isRequired && !field.isFilled) {
                    emptyRequired = true;
                }
            });
        });

        return emptyRequired;
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

            $scope.fieldChanged(field);
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
