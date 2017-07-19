/*global
 angular, IS_NATIVE_APP, DEVICE_TYPE, Camera, CameraPopoverOptions, FileReader
 */

/**
 * Picture
 *
 * @author Xtraball SAS
 */
angular.module("starter").service("Picture", function($cordovaCamera, $ionicActionSheet, $q, $rootScope,
                                                      $translate, Dialog, SB) {

    var service = {
        is_open : false,
        sheet_resolver : null,
        stack   : []
    };

    /**
     *
     * @param width
     * @param height
     * @param quality
     */
    service.takePicture = function(width, height, quality) {

        if(service.is_open || $rootScope.isNotAvailableInOverview()) {
            return $q.reject();
        }

        if(Camera === undefined) {
            Dialog.alert("Error", "Camera is not available.", "OK", -1)
                .then(function() {
                    return $q.reject();
                });
            return $q.reject();
        }

        service.is_open = true;

        var deferred = $q.defer();

        width   = (width === undefined) ? 1200 : width;
        height  = (height === undefined) ? 1200 : height;
        quality = (quality === undefined) ? 90 : quality;

        var source_type = Camera.PictureSourceType.CAMERA;

        var _buttons = [
            {
                text: $translate.instant("Import from Library")
            }
        ];

        if(DEVICE_TYPE !== SB.DEVICE.TYPE_BROWSER) {
            _buttons.unshift({
                text: $translate.instant("Take a picture")
            });
        }

        service.sheet_resolver = $ionicActionSheet.show({
            buttons: _buttons,
            cancelText: $translate.instant("Cancel"),
            cancel: function() {
                service.sheet_resolver();

                deferred.reject({
                    message: $translate.instant("Cancelled")
                });

                service.is_open = false;
            },
            buttonClicked: function(index) {
                if(index === 0) {
                    source_type = Camera.PictureSourceType.CAMERA;
                }

                if(index === 1) {
                    source_type = Camera.PictureSourceType.PHOTOLIBRARY;
                }

                var options = {
                    quality                 : quality,
                    destinationType         : Camera.DestinationType.DATA_URL,
                    sourceType              : source_type,
                    encodingType            : Camera.EncodingType.JPEG,
                    targetWidth             : width,
                    targetHeight            : height,
                    correctOrientation      : true,
                    popoverOptions          : CameraPopoverOptions,
                    saveToPhotoAlbum        : false
                };

                if(DEVICE_TYPE === SB.DEVICE.TYPE_BROWSER) {

                    var input = angular.element("<input type='file' accept='image/*'>");
                    var selectedFile = function(evt) {
                        var file = evt.currentTarget.files[0];
                        var reader = new FileReader();
                        reader.onload = function (evt) {
                            input.off("change", selectedFile);

                            if(evt.target.result.length > 0) {
                                service.sheet_resolver();

                                deferred.resolve({
                                    image: evt.target.result
                                });

                                service.is_open = false;
                            } else {
                                service.sheet_resolver();
                                service.is_open = false;
                            }

                        };
                        reader.onerror = function() {
                            service.sheet_resolver();

                            Dialog.alert("Error", "An error occurred while loading the picture.", "OK", -1)
                                .then(function() {
                                    service.is_open = false;
                                });
                        };
                        reader.readAsDataURL(file);
                    };
                    input.on("change", selectedFile);
                    input[0].click();

                } else {

                    $cordovaCamera.getPicture(options)
                        .then(function(imageData) {

                            service.sheet_resolver();

                            deferred.resolve({
                                image: "data:image/jpeg;base64," + imageData
                            });

                            service.is_open = false;

                        }, function(error) {

                            service.sheet_resolver();

                            Dialog.alert("Error", "An error occurred while taking a picture.", "OK", -1)
                                .then(function() {
                                    service.is_open = false;
                                });

                            deferred.reject({
                                message: error
                            });

                        }).catch(function(error) {

                        service.sheet_resolver();

                        Dialog.alert("Error", "An error occurred while taking a picture.", "OK", -1)
                            .then(function() {
                                service.is_open = false;
                            });

                        deferred.reject({
                            message: error
                        });

                    });


                }

                return true;
            }
        });

        return deferred.promise;

    };

    return service;
});