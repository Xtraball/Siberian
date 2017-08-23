/*global
    angular, DEVICE_TYPE, Camera, CameraPopoverOptions, FileReader
*/

/**
 * Picture
 *
 * @author Xtraball SAS
 */
angular.module('starter').service('Picture', function ($cordovaCamera, $ionicActionSheet, $q, $rootScope,
                                                      $translate, Dialog, SB) {
    var service = {
        isOpen: false,
        sheetResolver: null,
        stack: []
    };

    /**
     * @param width
     * @param height
     * @param quality
     */
    service.takePicture = function (width, height, quality) {
        if (service.isOpen || $rootScope.isNotAvailableInOverview()) {
            return $q.reject();
        }

        if (Camera === undefined) {
            Dialog.alert('Error', 'Camera is not available.', 'OK', -1)
                .then(function () {
                    return $q.reject();
                });
            return $q.reject();
        }

        service.isOpen = true;

        var deferred = $q.defer();

        var localWidth = (width === undefined) ? 1200 : width;
        var localHeight = (height === undefined) ? 1200 : height;
        var localQuality = (quality === undefined) ? 90 : quality;

        var sourceType = Camera.PictureSourceType.CAMERA;

        var _buttons = [
            {
                text: $translate.instant('Import from Library')
            }
        ];

        if (DEVICE_TYPE !== SB.DEVICE.TYPE_BROWSER) {
            _buttons.unshift({
                text: $translate.instant('Take a picture')
            });
        }

        service.sheetResolver = $ionicActionSheet.show({
            buttons: _buttons,
            cancelText: $translate.instant('Cancel'),
            cancel: function () {
                service.sheetResolver();

                deferred.reject({
                    message: $translate.instant('Cancelled')
                });

                service.isOpen = false;
            },
            buttonClicked: function (index) {
                if (index === 0) {
                    sourceType = Camera.PictureSourceType.CAMERA;
                }

                if (index === 1) {
                    sourceType = Camera.PictureSourceType.PHOTOLIBRARY;
                }

                var options = {
                    quality: localQuality,
                    destinationType: Camera.DestinationType.DATA_URL,
                    sourceType: sourceType,
                    encodingType: Camera.EncodingType.JPEG,
                    targetWidth: localWidth,
                    targetHeight: localHeight,
                    correctOrientation: true,
                    popoverOptions: CameraPopoverOptions,
                    saveToPhotoAlbum: false
                };

                if (DEVICE_TYPE === SB.DEVICE.TYPE_BROWSER) {
                    var input = angular.element('<input type="file" accept="image/*">');
                    var selectedFile = function (selectEvent) {
                        var file = selectEvent.currentTarget.files[0];
                        var reader = new FileReader();
                        reader.onload = function (onloadEvent) {
                            input.off('change', selectedFile);

                            if (onloadEvent.target.result.length > 0) {
                                service.sheetResolver();

                                deferred.resolve({
                                    image: onloadEvent.target.result
                                });

                                service.isOpen = false;
                            } else {
                                service.sheetResolver();
                                service.isOpen = false;
                            }
                        };
                        reader.onerror = function () {
                            service.sheetResolver();

                            Dialog.alert('Error', 'An error occurred while loading the picture.', 'OK', -1)
                                .then(function () {
                                    service.isOpen = false;
                                });
                        };
                        reader.readAsDataURL(file);
                    };
                    input.on('change', selectedFile);
                    input[0].click();
                } else {
                    $cordovaCamera.getPicture(options)
                        .then(function (imageData) {
                            service.sheetResolver();

                            deferred.resolve({
                                image: 'data:image/jpeg;base64,' + imageData
                            });

                            service.isOpen = false;
                        }, function (error) {
                            service.sheetResolver();

                            Dialog.alert('Error', 'An error occurred while taking a picture.', 'OK', -1)
                                .then(function () {
                                    service.isOpen = false;
                                });

                            deferred.reject({
                                message: error
                            });
                        }).catch(function (error) {
                            service.sheetResolver();

                            Dialog.alert('Error', 'An error occurred while taking a picture.', 'OK', -1)
                                .then(function () {
                                    service.isOpen = false;
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
