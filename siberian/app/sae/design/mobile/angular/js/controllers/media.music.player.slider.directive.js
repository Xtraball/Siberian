"use strict";

App.directive('slider', function () {
    return {
        restrict: 'A',
        template:   '<div class="siberian-slider" ng-click="updatePosition($event)">\
                        <div class="bar progress" ng-style="{ width: value + \'%\'}"></div>\
                        <div class="bar total"></div>\
                    </div>',
        scope: {
            value: '@',
            valueUpdated: '&'
        },
        link: function ($scope, element, attrs) {

            $scope.updatePosition = function ($event) {
                var rect = $event.currentTarget.getBoundingClientRect();
                var clickOffset = $event.clientX - rect.left;
                var total = rect.width;
                var newValue = 100 * clickOffset / total;

                var oldValue = $scope.value;
                if (oldValue !== newValue) {
                    console.debug('Update slider value from %d to %d.', oldValue, newValue);
                   // $scope.value = newValue;
                    if (typeof ($scope.valueUpdated) === 'function') {
                        $scope.valueUpdated({
                            newValue: newValue,
                            oldValue: oldValue
                        });
                    }
                    $scope.value = newValue;
                }
            };

        }
    };
});