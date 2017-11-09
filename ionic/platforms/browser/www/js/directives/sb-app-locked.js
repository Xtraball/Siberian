angular.module("starter").directive('sbAppLocked', function () {
    return {
        restrict: 'E',
        replace: true,
        scope: {},
        templateUrl: "templates/html/l1/app_locked.html",
        controller: function($ionicHistory) {

            $ionicHistory.clearHistory();

        }
    };
});