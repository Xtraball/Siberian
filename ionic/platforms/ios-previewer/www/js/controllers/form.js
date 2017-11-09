/*global
 App, angular, isOverview, BASE_PATH, IS_NATIVE_APP, Camera
 */

angular.module("starter").controller("FormViewController", function(Location, $scope, $stateParams, $translate, Dialog, Form,
                                             GoogleMaps, Picture) {

    angular.extend($scope, {
        is_loading          : true,
        value_id            : $stateParams.value_id,
        formData            : {},
        preview_src         : {},
        geolocation         : {},
        can_take_pictures   : IS_NATIVE_APP,
        card_design         : false
    });

    Form.setValueId($stateParams.value_id);

    $scope.loadContent = function() {

        Form.findAll()
            .then(function(data) {
                $scope.sections = data.sections;
                $scope.page_title = data.page_title;

            }).then(function() {

                $scope.is_loading = false;

            });

    };

    $scope.getLocation = function(field) {

        if($scope.geolocation[field.id]) {

            $scope.is_loading = true;

            Location.getLocation()
                .then(function(position) {

                    GoogleMaps.reverseGeocode(position.coords).then(function(results) {
                        if (results[0]) {
                            $scope.formData[field.id] = results[0].formatted_address;
                        } else {
                            $scope.formData[field.id] = position.coords.latitude + ", " + position.coords.longitude;
                        }
                        $scope.is_loading = false;
                    }, function(data) {
                        $scope.formData[field.id] = null;
                        $scope.geolocation[field.id] = false;
                        $scope.is_loading = false;
                    });

                }, function(e) {
                    $scope.is_loading = false;

                    $scope.formData[field.id] = null;
                    $scope.geolocation[field.id] = false;

                });

        } else {
            $scope.formData[field.id] = null;
        }
    };

    /**
     * @param field
     */
    $scope.takePicture = function(field) {

        Picture.takePicture()
            .then(function(success) {
                $scope.preview_src[field.id]    = success.image;
                $scope.formData[field.id]       = success.image;
            });

    };

    $scope.post = function() {

        $scope.is_loading = true;

        if(_.isEmpty($scope.formData)) {

            Dialog.alert("Error", "You must fill at least one field!", "OK", -1)
                .then(function() {
                    $scope.is_loading = false;
                });

            return;
        }

        Form.post($scope.formData)
            .then(function(data) {

                /** Reset form */
                $scope.formData = {};
                $scope.preview_src = {};

                Dialog.alert("Success", data.message, "OK", -1);

            }, function(data) {

                Dialog.alert("Error", data.message, "OK", -1);

            }).then(function() {

                $scope.is_loading = false;

            });
    };

    $scope.loadContent();

});
