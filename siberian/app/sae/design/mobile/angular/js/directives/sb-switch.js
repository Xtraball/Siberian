App.directive("sbSwitch", function() {
    return {
        restrict: 'E',
        scope: {
            toggleSwitch: "&",
            isChecked: "=?",
            params: "=?"
        },
        template: '<label class="sb-toggle">' +
                    '<input type="checkbox" class="sb-toggle-input" ng-model="isChecked" ng-change="callBack()">' +
                    '<span class="sb-toggle-label" ng-class="{button: isChecked}" data-on="On" data-off="Off"></span>' +
                    '<span class="sb-toggle-handle" ng-class="{checked: isChecked}"></span>' +
                    '</label>',
        replace: true,
        link: function (scope, element, attrs) {
            scope.params = angular.isDefined(scope.params)?scope.params:{};
            scope.isChecked = angular.isDefined(scope.isChecked)?scope.isChecked:false;

            scope.callBack = function() {
                if(scope.toggleSwitch) {
                    scope.toggleSwitch({is_checked:scope.isChecked,params:scope.params});
                }
            }
        }
    };
});