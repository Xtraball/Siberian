App.config(function ($stateProvider) {

    $stateProvider.state('mcommerce-sales-success', {
        url: BASE_PATH+"/mcommerce/mobile_sales_success/index/value_id/:value_id",
        controller: 'MCommerceSalesSuccessViewController',
        templateUrl: "templates/mcommerce/l1/sales/success.html",
        cache:false
    });

}).controller('MCommerceSalesSuccessViewController', function ($ionicLoading, $scope, $state, $stateParams, $timeout, Customer) {

    $scope.value_id = $stateParams.value_id;

    if(Customer.guest_mode) {
        Customer.guest_mode = false;
        Customer.logout().finally(function(){});
    };

    $timeout(function() {
        $state.go("mcommerce-redirect", {value_id: $scope.value_id});
    }, 3000);

});