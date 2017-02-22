App.config(function($stateProvider) {

    $stateProvider.state('form-view', {
        url: BASE_PATH+"/form/mobile_view/index/value_id/:value_id",
        controller: 'FormViewController',
        templateUrl: "templates/form/l1/view.html",
    });

}).controller('FormViewController', function($cordovaCamera, $cordovaGeolocation, $sbhttp, $ionicActionSheet, $ionicPopup, $location, $rootScope, $scope, $stateParams, $timeout, $translate, Application, Dialog, Form, GoogleMaps) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.can_take_pictures = !Application.is_webview;
    $scope.value_id = Form.value_id = $stateParams.value_id;
    $scope.formData = {};
    $scope.preview_src = {};
    $scope.geolocation = {};

    $scope.loadContent = function() {

        Form.findAll().success(function(data) {
            $scope.sections = data.sections;
            $scope.page_title = data.page_title;
        }).error(function() {

        }).finally(function() {
            $scope.is_loading = false;
        });

    };

    $scope.getLocation = function(field) {

        if($scope.geolocation[field.id]) {

            $scope.is_loading = true;

            $cordovaGeolocation.getCurrentPosition({ enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }).then(function(position) {

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

    $scope.takePicture = function(field) {

        if(!$scope.can_take_pictures) {
            $rootScope.showMobileFeatureOnlyError();
            return;
        }

        var source_type = Camera.PictureSourceType.CAMERA;

        // Show the action sheet
        var hideSheet = $ionicActionSheet.show({
            buttons: [
                { text: $translate.instant("Take a picture") },
                { text: $translate.instant("Import from Library") }
            ],
            cancelText: $translate.instant("Cancel"),
            cancel: function() {
                hideSheet();
            },
            buttonClicked: function(index) {
                if(index == 0) {
                    source_type = Camera.PictureSourceType.CAMERA;
                }
                if(index == 1) {
                    source_type = Camera.PictureSourceType.PHOTOLIBRARY;
                }

                var options = {
                    quality : 90,
                    destinationType : Camera.DestinationType.DATA_URL,
                    sourceType : source_type,
                    encodingType: Camera.EncodingType.JPEG,
                    targetWidth: 1200,
                    targetHeight: 1200,
                    correctOrientation: true,
                    popoverOptions: CameraPopoverOptions,
                    saveToPhotoAlbum: false
                };

                $cordovaCamera.getPicture(options).then(function(imageData) {
                    $scope.preview_src[field.id] = "data:image/jpeg;base64," + imageData;
                    $scope.formData[field.id] = "data:image/jpeg;base64," + imageData;
                }, function(err) {
                    // An error occured. Show a message to the user
                });

                return true;
            }
        });

    };

    $scope.post = function() {

        $scope.is_loading = true;

        Form.post($scope.formData).success(function(data) {
            $scope.formData = {};
            $scope.preview_src = {};
            if(data.success) {
                Dialog.alert("", data.message, $translate.instant("OK"));
            }
        }).error(function(data) {
            if(data && angular.isDefined(data.message)) {
                $ionicPopup.show({
                    template: "<div style='text-align:center;font-weight:bold;margin-bottom: 10px;'>" + $translate.instant("Error") + "</div>" + data.message,
                    scope: $scope,
                    buttons: [{
                        text: $translate.instant('OK'),
                        type: 'button-custom',
                        onTap: function(e) {
                            return true;
                        }
                    }]
                }).then(function(result) {

                });
            }
        }).finally(function() {
            $scope.is_loading = false;
        });
    };

    $scope.loadContent();

});
