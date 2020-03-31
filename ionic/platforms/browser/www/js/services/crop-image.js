/**
 * CustomTab
 *
 * @author Xtraball SAS
 * @version 4.18.14
 */
angular
    .module('starter')
    .service('CropImage', function ($rootScope, $q, $ionicPopup, $translate) {
        var service = {};

        service.openPopup = function (url) {
            var scope = $rootScope.$new(true);

            scope.cropModal = {
                original: url,
                result: null
            };

            // DO NOT REMOVE popupShowing !!!
            // img-crop directive doesn't work if it has been loaded off screen
            // We show the popup, then switch popupShowing to true, to add
            // img-crop in the view.
            scope.popupShowing = false;

            var deferred = $q.defer();

            $ionicPopup
                .show({
                    template: '' +
                        '<div style="position: absolute" ' +
                        '     class="cropper">' +
                        '    <img-crop ng-if="popupShowing" ' +
                        '              image="cropModal.original" ' +
                        '              result-image="cropModal.result" ' +
                        '              area-type="square" ' +
                        '              result-image-size="256" ' +
                        '              result-image-format="image/jpeg" ' +
                        '              result-image-quality="0.9"></img-crop>' +
                        '</div>',
                    cssClass: 'avatar-crop',
                    scope: scope,
                    buttons: [{
                        text: $translate.instant('Cancel'),
                        type: 'button-default',
                        onTap: function (e) {
                            return false;
                        }
                    }, {
                        text: $translate.instant('OK'),
                        type: 'button-positive',
                        onTap: function (e) {
                            return true;
                        }
                    }]
                }).then(function (result) {
                    if (result) {
                        deferred.resolve(scope.cropModal.result);
                    } else {
                        deferred.reject();
                    }
                });
            scope.popupShowing = true;

            return deferred.promise;
        };


        return service;
    });

