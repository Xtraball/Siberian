/*global
    angular
*/

angular.module('starter').directive('sbInputNumber', function ($timeout) {
    return {
        restrict: 'E',
        scope: {
            changeQty: '&',
            step: '=?',
            min: '=?',
            max: '=?',
            value: '=?',
            params: '=?'
        },
        template:
        '<div class="item item-input item-custom input-number-sb">' +
        '   <div class="input-label"><i class="ion-plus"></i> {{ label }}</div>' +
        '   <div class="input-container text-right">' +
        '       <button class="button button-small button-custom button-left" ng-click="down()">-</button>' +
        '       <div class="item-input-wrapper">' +
        '           <input type="text" value="{{ dirValue }}" class="text-center input" readonly />' +
        '       </div>' +
        '       <button class="button button-small button-custom button-right" ng-click="up()">+</button>' +
        '   </div>' +
        '</div>',
        replace: true,
        link: function (scope, element, attrs) {
            scope.step = scope.step ? scope.step : 1;
            scope.min = scope.min ? scope.min : 0;
            scope.max = scope.max ? scope.max : 999;
            scope.value = scope.value ? angular.copy(scope.value) : 0;
            scope.dirValue = scope.value;
            scope.params = scope.params ? scope.params : {};
            scope.label = attrs.label ? attrs.label + ':' : '';

            scope.up = function () {
                if (scope.dirValue < scope.max) {
                    scope.dirValue = scope.dirValue + 1;
                    scope.callBack(scope.dirValue);
                }
            };

            scope.down = function () {
                if (scope.dirValue > scope.min) {
                    scope.dirValue = scope.dirValue - 1;
                    scope.callBack(scope.dirValue);
                }
            };

            scope.callBack = function (value) {
                if (typeof scope.changeQty === 'function') {
                    $timeout(function () {
                        scope.changeQty({
                            qty: value,
                            params: scope.params
                        });
                    }, 500);
                }
            };
        }
    };
});
