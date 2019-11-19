/*global
    angular
*/

angular.module('starter').directive('sbInputNumber', function ($timeout, $translate) {
    return {
        restrict: 'E',
        scope: {
            changeQty: '&',
            step: '=?',
            min: '=?',
            max: '=?',
            value: '=?',
            label: '=?',
            params: '=?'
        },
        template:
        '<div class="item item-input item-custom input-number-sb">' +
        '   <div class="input-label">{{ getLabel() }}</div>' +
        '   <div class="input-container text-right">' +
        '       <button class="button button-small button-custom button-left" ' +
        '               ng-click="oneLess()">' +
        '           <i class="icon ion-minus"></i>' +
        '       </button>' +
        '       <div class="item-input-wrapper">' +
        '           <input type="text" ' +
        '                  value="{{ getValue() }}" ' +
        '                  class="text-center input" ' +
        '                  readonly />' +
        '       </div>' +
        '       <button class="button button-small button-custom button-right" ' +
        '               ng-click="oneMore()">' +
        '           <i class="icon ion-plus"></i>' +
        '       </button>' +
        '   </div>' +
        '</div>',
        replace: true,
        link: function (scope, element, attrs) {
            scope.step = scope.step ? scope.step : 1;
            scope.min = scope.min ? scope.min : 0;
            scope.max = scope.max ? scope.max : 999;
            scope.params = scope.params ? scope.params : {};
            scope.label = attrs.label ? attrs.label + ':' : '';
        },
        controller: function ($scope) {
            $scope.oneMore = function () {
                if ($scope.value < $scope.max) {
                    $scope.value = $scope.value + 1;
                    $scope.callBack($scope.value);
                }
            };

            $scope.oneLess = function () {
                if ($scope.value > $scope.min) {
                    $scope.value = $scope.value - 1;
                    $scope.callBack($scope.value);
                }
            };

            $scope.getLabel = function () {
                return $translate.instant($scope.label);
            };

            $scope.getValue = function () {
                return $scope.value;
            };

            $scope.callBack = function (value) {
                if (typeof $scope.changeQty === 'function') {
                    // Cancel if not nulled yet!
                    if ($scope.debouncePromise) {
                        $timeout.cancel($scope.debouncePromise);
                    }
                    $scope.debouncePromise = $timeout(function () {
                        $scope.changeQty({
                            qty: value,
                            params: $scope.params
                        });

                        $scope.debouncePromise = null;
                    }, 800);
                }
            };
        }
    };
});
