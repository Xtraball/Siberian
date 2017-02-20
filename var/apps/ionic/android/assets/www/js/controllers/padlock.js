App.config(function($stateProvider) {

    $stateProvider.state('padlock-view', {
        url: BASE_PATH+"/padlock/mobile_view/index/value_id/:value_id",
        params: {
            value_id: 0
        },
        controller: 'PadlockController',
        templateUrl: "templates/padlock/l1/view.html"
    });

}).controller('PadlockController', function($scope, $stateParams, $state, Padlock, Pages) {

    Padlock.value_id = $stateParams.value_id;
    // redirect to an existing value_id
    Pages.findAll().then(function (data) {
        data.pages.forEach(function (page) {
            if (page.code == 'padlock') {
                $state.go('padlock-view', {value_id: page.value_id});
            }
        });
    });
    
    Padlock.find().success(function(data) {
        $scope.page_title = data.page_title;
    });

    Padlock.findUnlockTypes().success(function(data) {
        $scope.unlock_by_account_type = data.unlock_by_account;
        $scope.unlock_by_qrcode_type = data.unlock_by_qrcode;
    });

});