/**
 * Picture
 *
 * Lets the user take a picture or import one from the library, and gives it back as a data URI.
 *
 *     Picture.takePicture(width, height, quality).then(function (result) { result.image; });
 *
 * - width, height: maximum size in pixels, default 1200.
 * - quality: JPEG quality from 0 to 100, default 90.
 * - result.image: a `data:image/jpeg;base64,...` string.
 * - A rejection with `cancelled: true` means the user backed out, nothing is shown. Any other rejection was
 *   already reported to the user with a dialog.
 *
 * About the native plugin (cordova-plugin-camera): up to v7 DATA_URL returned the bare base64 string, v8 returns a
 * full data URI on Android and iOS. Both are accepted here (see `service.toDataUrl`), so the app does not depend on
 * the plugin version that is in the shell.
 *
 * @author Xtraball SAS
 */
angular
    .module('starter')
    .service('Picture', function ($cordovaCamera, $ionicActionSheet, $q, $rootScope, $translate, Dialog, SB) {
    var service = {
        isOpen: false,
        sheetResolver: null,
        stack: []
    };

    var DEFAULT_SIZE = 1200;
    var DEFAULT_QUALITY = 90;

    /**
     * What the plugin says when the user backs out of the camera or of the library, it is not an error.
     * Android: "No Image Selected", "Selection cancelled.", "Camera cancelled."; iOS: "No Image Selected",
     * "no image selected", "cancelled".
     *
     * @param error
     * @returns {boolean}
     */
    service.isCancellation = function (error) {
        var message = (error && error.message) ? error.message : error;
        if (typeof message !== 'string') {
            return false;
        }

        return /(no image selected|cancell?ed|canceled|cancel)/i.test(message);
    };

    /**
     * @param value
     * @param fallback
     * @param min
     * @param max
     * @returns {number}
     */
    service.clamp = function (value, fallback, min, max) {
        var number = parseInt(value, 10);
        if (isNaN(number)) {
            return fallback;
        }

        return Math.min(max, Math.max(min, number));
    };

    /**
     * cordova-plugin-camera <= 7 returns the bare base64, >= 8 returns a data URI.
     *
     * @param result
     * @returns {string|null}
     */
    service.toDataUrl = function (result) {
        if (typeof result !== 'string' || result.length <= 0) {
            return null;
        }

        if (result.indexOf('data:') === 0) {
            return result;
        }

        return 'data:image/jpeg;base64,' + result;
    };

    /**
     * @param width
     * @param height
     * @param quality
     */
    service.takePicture = function (width, height, quality) {
        if (service.isOpen) {
            return $q.reject();
        }

        var isBrowser = (DEVICE_TYPE === SB.DEVICE.TYPE_BROWSER);

        if (!isBrowser && (typeof Camera === 'undefined' || typeof navigator.camera === 'undefined')) {
            Dialog.alert('Error', 'Camera is not available.', 'OK', -1);
            return $q.reject();
        }

        service.isOpen = true;

        var deferred = $q.defer();

        var localWidth = service.clamp(width, DEFAULT_SIZE, 1, 10000);
        var localHeight = service.clamp(height, DEFAULT_SIZE, 1, 10000);
        var localQuality = service.clamp(quality, DEFAULT_QUALITY, 0, 100);

        var done = function () {
            if (service.sheetResolver) {
                service.sheetResolver();
                service.sheetResolver = null;
            }
            service.isOpen = false;
        };

        var cancelled = function () {
            done();
            deferred.reject({
                cancelled: true,
                message: $translate.instant('Cancelled')
            });
        };

        var failed = function (message, error) {
            done();
            Dialog.alert('Error', message, 'OK', -1);
            deferred.reject({
                message: error
            });
        };

        // Each button knows its own source, no index to keep in sync with the list.
        var _buttons = [];
        if (!isBrowser) {
            _buttons.push({
                text: $translate.instant('Take a picture'),
                source: Camera.PictureSourceType.CAMERA
            });
        }
        _buttons.push({
            text: $translate.instant('Import from Library'),
            source: isBrowser ? null : Camera.PictureSourceType.PHOTOLIBRARY
        });

        service.sheetResolver = $ionicActionSheet.show({
            buttons: _buttons,
            cancelText: $translate.instant('Cancel'),
            cancel: cancelled,
            buttonClicked: function (index) {
                var button = _buttons[index];

                if (isBrowser) {
                    service.pickFromBrowser(localQuality, deferred, done, failed, cancelled);
                    return true;
                }

                var options = {
                    quality: localQuality,
                    destinationType: Camera.DestinationType.DATA_URL,
                    sourceType: button.source,
                    encodingType: Camera.EncodingType.JPEG,
                    mediaType: Camera.MediaType.PICTURE,
                    targetWidth: localWidth,
                    targetHeight: localHeight,
                    correctOrientation: true,
                    saveToPhotoAlbum: false
                };

                // iPad only, the popover the library opens from.
                if (typeof CameraPopoverOptions !== 'undefined') {
                    options.popoverOptions = CameraPopoverOptions;
                }

                $cordovaCamera
                    .getPicture(options)
                    .then(function (imageData) {
                        var image = service.toDataUrl(imageData);
                        if (image === null) {
                            // Nothing came back, same as backing out.
                            cancelled();
                            return;
                        }

                        done();
                        deferred.resolve({
                            image: image
                        });
                    }, function (error) {
                        if (service.isCancellation(error)) {
                            cancelled();
                            return;
                        }

                        failed('An error occurred while taking a picture.', error);
                    })
                    .catch(function (error) {
                        failed('An error occurred while taking a picture.', error);
                    });

                return true;
            }
        });

        return deferred.promise;
    };

    /**
     * Browser only (preview): a file input.
     *
     * @param quality
     * @param deferred
     * @param done
     * @param failed
     * @param cancelled
     */
    service.pickFromBrowser = function (quality, deferred, done, failed, cancelled) {
        var input = angular.element('<input type="file" accept="image/*">');

        var selectedFile = function (selectEvent) {
            input.off('change', selectedFile);

            var file = selectEvent.currentTarget.files[0];
            if (!file) {
                cancelled();
                return;
            }

            var reader = new FileReader();
            reader.onload = function (onloadEvent) {
                var image = service.toDataUrl(onloadEvent.target.result);
                if (image === null) {
                    cancelled();
                    return;
                }

                done();
                deferred.resolve({
                    image: image
                });
            };

            reader.onerror = function () {
                failed('An error occurred while loading the picture.', 'FileReader error');
            };

            reader.readAsDataURL(file);
        };

        // The picker closing without a choice gives focus back to the window, without a change event.
        var focusBack = function () {
            window.removeEventListener('focus', focusBack);
            setTimeout(function () {
                if (service.isOpen && !input[0].files.length) {
                    cancelled();
                }
            }, 500);
        };

        input.on('change', selectedFile);
        window.addEventListener('focus', focusBack);
        input[0].click();
    };

    return service;
});
