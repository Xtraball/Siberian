App.directive('lockedApp', function (Customer) {
    return {
        restrict: 'E',
        template: '<div ng-include src="\'locked_app.html\'"></div>',
        replace: true,
        controller: function($scope, Customer) {
            $scope.logout = function() {
                Customer.logout();
            }
        }
    };
});