App.directive("datetime", function(Application) {
    return {
        restrict: 'A',
        scope: {
            date: "="
        },
        link: function (scope, element) {
            if(Application.is_android) {
                element.bind('blur', function () {
                    scope.date = this.value;
                    scope.$parent.$apply();
                });
            }
        }
    };
});