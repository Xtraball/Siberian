App.config(function($stateProvider) {

    $stateProvider.state('locked', {
        url: BASE_PATH+"/locked/mobile_view/index",
        controller: 'LockedController',
        templateUrl: 'templates/locked/l1/view.html'
    });
}).controller('LockedController', function($window, $scope) {
    $scope.is_loading = false;
});