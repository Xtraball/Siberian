App.config(function($stateProvider) {

    $stateProvider.state('padlock-view', {
        url: BASE_PATH+"/padlock/mobile_view/index/value_id/:value_id",
        params: {
            value_id: 0
        },
        controller: 'PadlockController',
        templateUrl: "templates/padlock/l1/view.html"
    });

}).controller('PadlockController', function($scope, $stateParams, Padlock) {

    Padlock.value_id = $stateParams.value_id;

    Padlock.find().success(function(data) {
        $scope.page_title = data.page_title;
    });

    Padlock.findUnlockTypes().success(function(data) {
        $scope.unlock_by_account_type = data.unlock_by_account;
        $scope.unlock_by_qrcode_type = data.unlock_by_qrcode;
    });

});