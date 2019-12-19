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
                '        ng-class="buttonClass"\n' +
                '        class="button button-clear header-item places-secondary-button">\n' +
                '    <i ng-class="iconClass"' +
                '       class="icon ion-sb-location-off icon-warning-custom"></i>\n' +
                '</button>',
            scope: {
                success: '=?',
                error: '=?',
                buttonClass: '=?',
                iconClass: '=?'
            },
            replace: true,
            controller: function ($scope, $rootScope, Location) {
                $scope.locationIsDisabled = function () {
                    return !Location.isEnabled;
                };

                $scope.requestLocation = function () {
                    Location.requestLocation($scope.success, $scope.error);
                };
            }
        };
    });
