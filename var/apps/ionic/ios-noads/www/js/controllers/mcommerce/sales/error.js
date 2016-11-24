App.config(function ($stateProvider) {

    $stateProvider.state('mcommerce-sales-error', {
        url: BASE_PATH+"/mcommerce/mobile_sales_error/index/value_id/:value_id",
        controller: 'MCommerceSalesErrorViewController',
        templateUrl: "templates/mcommerce/l1/sales/error.html",
        cache:false
    });

}).controller('MCommerceSalesErrorViewController', function ($scope, $state, $stateParams, $timeout) {

    $scope.value_id = $stateParams.value_id;

    $timeout(function() {
        $state.go("mcommerce-redirect", {value_id: $scope.value_id});
    }, 4000);

});