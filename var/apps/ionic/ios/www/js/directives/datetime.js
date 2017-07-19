angular.module("starter").directive("datetime", function($ionicPlatform) {
    return {
        restrict: 'A',
        scope: {
            date: "="
        },
        link: function (scope, element) {
            if($ionicPlatform.is("android")) {
                element.bind('blur', function () {
                    scope.date = this.value;
                    scope.$parent.$apply();
                });
            }
        }
    };
});