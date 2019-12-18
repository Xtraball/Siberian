/**
 * Location button to manage permissions globally!
 */
angular
.module('starter')
.directive('locationButton', function () {
    return {
        restrict: 'E',
        template:
            '<button ng-show="locationIsDisabled()"\n' +
            '        ng-click="requestLocation()"\n' +
            '        class="button button-clear header-item places-secondary-button">\n' +
            '    <i class="icon ion-sb-location-off places-location-action icon-warning-custom"></i>\n' +
            '</button>',
        replace: true,
        controller: function ($scope, $rootScope, Dialog, Loader, Location) {
            $scope.locationIsDisabled = function () {
                return !Location.isEnabled;
            };

            $scope.requestLocation = function () {
                Dialog
                    .confirm(
                        'Error',
                        'We were unable to request your location.<br />Please check that the application is allowed to use the GPS and that your device GPS is on.',
                        ['TRY AGAIN', 'DISMISS'],
                        -1,
                        'location')
                    .then(function (success) {
                        if (success) {
                            Location.isEnabled = true;
                            Loader.show();
                            Location
                                .getLocation({timeout: 30000, enableHighAccuracy: false}, true)
                                .then(function (payload) {
                                    // GPS is OK!!
                                    Loader.hide();
                                    Dialog.alert('Success', 'We finally got you location', 'OK', 2350, 'location');
                                }, function () {
                                    Loader.hide();
                                    Dialog
                                        .alert(
                                            'Error',
                                            'We were unable to request your location.<br />Please check that the application is allowed to use the GPS and that your device GPS is on.',
                                            'OK',
                                            3700,
                                            'location'
                                        );
                                });
                        }
                    });
            };
        }
    };
});
